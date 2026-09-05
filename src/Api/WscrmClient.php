<?php

namespace SweetPortofolio\Api;

/**
 * Fetches portfolio data from WebSweetStudio CRM REST API.
 *
 * Caching strategy (stale-while-revalidate):
 * - Fresh data is cached in transients (HOUR_IN_SECONDS).
 * - Every successful fetch also persists a durable backup (autoloaded option,
 *   no expiry). When the API is down or unreachable after the transient
 *   expires, the last known good data is served from the backup instead of
 *   showing an error, so the site keeps working.
 */
class WscrmClient
{
    private string $base_url;

    /** Durable backup: last known good list response (no expiry). */
    private const BACKUP_LIST_KEY = 'portofolio_wscrm_backup';

    public function __construct()
    {
        $this->base_url = rtrim(get_option('portofolio_wscrm_api_url', 'https://app.websweetstudio.com'), '/');
    }

    /**
     * Fetch demos from wscrm API. Returns data in format compatible
     * with the existing portfolio template (Alpine.js).
     *
     * Optional query params supported by the API:
     * - category: filter by category slug
     * - package:  filter by package slug
     */
    public function fetchDemos(array $query = []): array
    {
        $params = [];
        foreach (['category', 'package'] as $key) {
            if (!empty($query[$key])) {
                $params[$key] = sanitize_title((string) $query[$key]);
            }
        }

        $url = $this->base_url . '/api/demos';
        if ($params) {
            $url .= '?' . build_query($params);
        }
        $cache_key = 'portofolio_wscrm_data' . ($params ? '_' . md5(build_query($params)) : '');

        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $response = wp_remote_get($url, [
            'timeout' => 15,
            'headers' => ['Referer' => home_url()],
        ]);

        if (is_wp_error($response)) {
            return $this->fallback($cache_key, $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            return $this->fallback($cache_key, "HTTP $code");
        }

        $body = wp_remote_retrieve_body($response);
        $raw = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->fallback($cache_key, 'Invalid JSON');
        }

        // Transform wscrm format to legacy format
        $demos = $raw['demos'] ?? $raw['data'] ?? [];
        $transformed = [];

        foreach ($demos as $demo) {
            $featured_url = $demo['featured_image'] ?? '';

            $transformed[] = [
                'id' => (int) ($demo['id'] ?? 0),
                'title' => $demo['title'] ?? '',
                'slug' => sanitize_title($demo['title'] ?? ''),
                'jenis' => [$demo['category_slug'] ?? 'uncategorized'],
                'jenis_web' => $demo['category_slug'] ?? 'uncategorized',
                'category_name' => $demo['category'] ?? '',
                'featured_image' => $featured_url,
                'preview_url' => $demo['url'] ?? '',
                'excerpt' => $demo['description'] ?? '',
                'packages' => $demo['packages'] ?? [],
                '_embedded' => [
                    'wp:featuredmedia' => [
                        [
                            'source_url' => $featured_url,
                            'media_details' => [
                                'sizes' => [
                                    'thumbnail' => ['source_url' => $featured_url],
                                    'medium' => ['source_url' => $featured_url],
                                    'large' => ['source_url' => $featured_url],
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        }

        // Categories: transform to legacy format [{"slug": "x", "category": "X"}, ...]
        $categories = [];
        $seen = [];
        foreach ($demos as $d) {
            $slug = $d['category_slug'] ?? '';
            if ($slug && !isset($seen[$slug])) {
                $seen[$slug] = true;
                $categories[] = [
                    'slug' => $slug,
                    'category' => $d['category'] ?? $slug,
                ];
            }
        }

        $result = [
            'demos' => $transformed,
            'categories' => $categories,
            'packages' => $raw['packages'] ?? [],
        ];

        set_transient($cache_key, $result, HOUR_IN_SECONDS);
        if (!$params) {
            // Only the unfiltered response is durable enough for backup.
            update_option(self::BACKUP_LIST_KEY, $result, false);
        }

        return $result;
    }

    /**
     * Fetch a single demo from wscrm API by ID (GET /api/demos/{id}).
     */
    public function fetchDemo(int $id): array
    {
        $url = $this->base_url . '/api/demos/' . $id;
        $cache_key = 'portofolio_wscrm_demo_' . $id;

        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $response = wp_remote_get($url, [
            'timeout' => 15,
            'headers' => ['Referer' => home_url()],
        ]);

        if (is_wp_error($response)) {
            return $this->fallback($cache_key, $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            return $this->fallback($cache_key, "HTTP $code");
        }

        $body = wp_remote_retrieve_body($response);
        $demo = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->fallback($cache_key, 'Invalid JSON');
        }

        $result = [
            'id' => (int) ($demo['id'] ?? $id),
            'title' => $demo['title'] ?? '',
            'url' => $demo['url'] ?? '',
            'category' => $demo['category'] ?? '',
            'category_slug' => $demo['category_slug'] ?? '',
            'packages' => $demo['packages'] ?? [],
            'featured_image' => $demo['featured_image'] ?? '',
            'description' => $demo['description'] ?? '',
        ];

        set_transient($cache_key, $result, HOUR_IN_SECONDS);
        update_option('portofolio_wscrm_demo_backup_' . $id, $result, false);

        return $result;
    }

    /**
     * On API failure, serve the last known good data from the durable
     * backup instead of an error. Returns ['error' => ...] only when no
     * backup exists (e.g. the very first fetch after activation).
     */
    private function fallback(string $cache_key, string $error_message): array
    {
        $backup = $cache_key === self::BACKUP_LIST_KEY
            ? null
            : null;

        // List cache (unfiltered): backup lives in its own option.
        if ($cache_key === 'portofolio_wscrm_data') {
            $stored = get_option(self::BACKUP_LIST_KEY, false);
            if (is_array($stored) && isset($stored['demos'])) {
                // Keep serving stale data and retry next request without hammering a dead API.
                set_transient($cache_key, $stored, 5 * MINUTE_IN_SECONDS);
                return $stored;
            }

            return ['error' => $error_message];
        }

        // Single demo / filtered list caches: backups are per-key options.
        $backup_key = str_replace('portofolio_wscrm_data', 'portofolio_wscrm_backup', $cache_key);
        if (str_starts_with($cache_key, 'portofolio_wscrm_demo_')) {
            $backup_key = str_replace('portofolio_wscrm_demo_', 'portofolio_wscrm_demo_backup_', $cache_key);
        }

        $stored = get_option($backup_key, false);
        if (is_array($stored)) {
            set_transient($cache_key, $stored, 5 * MINUTE_IN_SECONDS);
            return $stored;
        }

        return ['error' => $error_message];
    }

    public function getBaseUrl(): string
    {
        return $this->base_url;
    }

    public function flushCache(): void
    {
        delete_transient('portofolio_wscrm_data');
        delete_option(self::BACKUP_LIST_KEY);

        global $wpdb;
        // Remove every wscrm transient (list, filtered list, per-ID demo)
        // and their timeouts, plus the per-ID demo backups.
        $wpdb->query(
            "DELETE FROM {$wpdb->options}
             WHERE option_name LIKE '\_transient\_portofolio\_wscrm\_%'
                OR option_name LIKE '\_transient\_timeout\_portofolio\_wscrm\_%'
                OR option_name LIKE 'portofolio\_wscrm\_demo\_backup\_%'"
        );
    }
}

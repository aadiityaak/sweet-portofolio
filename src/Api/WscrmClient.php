<?php

namespace SweetPortofolio\Api;

/**
 * Fetches portfolio data from WebSweetStudio CRM REST API.
 */
class WscrmClient
{
    private string $base_url;

    public function __construct()
    {
        $this->base_url = rtrim(get_option('portofolio_wscrm_api_url', 'https://app.websweetstudio.com'), '/');
    }

    /**
     * Fetch demos from wscrm API. Returns data in format compatible
     * with the existing portfolio template (Alpine.js).
     */
    public function fetchDemos(): array
    {
        $url = $this->base_url . '/api/demos';
        $cache_key = 'portofolio_wscrm_data';

        $cached = get_transient($cache_key);
        if ($cached !== false) {
            return $cached;
        }

        $response = wp_remote_get($url, [
            'timeout' => 15,
            'headers' => ['Referer' => home_url()],
        ]);

        if (is_wp_error($response)) {
            return ['error' => $response->get_error_message()];
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            return ['error' => "HTTP $code"];
        }

        $body = wp_remote_retrieve_body($response);
        $raw = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Invalid JSON'];
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

        return $result;
    }

    public function getBaseUrl(): string
    {
        return $this->base_url;
    }

    public function flushCache(): void
    {
        delete_transient('portofolio_wscrm_data');
    }
}

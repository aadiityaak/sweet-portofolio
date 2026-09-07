<?php

/**
 * Template Name: Portfolio List
 *
 * @package       SWEETPORTOFOLIO
 * @author        Aditya K
 * @license       gplv2
 * @version       1.0.0
 *
 **/

// Don't render in admin area
if (is_admin()) {
    return;
}

$image_size = get_option('portofolio_image_size');
$access_key = get_option('portofolio_access_key');
$preview_page = get_option('portofolio_preview_page');
$style_thumbnail = get_option('portofolio_style_thumbnail');
$portofolio_selection = get_option('portofolio_selection');
$whatsapp_number = get_option('portofolio_whatsapp_number');
$portofolio_credit = get_option('portofolio_credit');
$portofolio_page = get_option('portofolio_page');
$shortcode_ids = isset($shortcode_ids) && is_array($shortcode_ids) ? $shortcode_ids : array();

// Ensure portofolio_selection is an array
if (!is_array($portofolio_selection)) {
    $portofolio_selection = array();
}

// Clean WhatsApp number
$whatsapp_number = preg_replace('/[^0-9]/', '', $whatsapp_number);
$whatsapp_number = preg_replace('/^0/', '62', $whatsapp_number);

// Get portfolio data
$api_source = get_option('portofolio_api_source', 'wscrm');
$transient_key = 'web_data_transient';

if ($api_source === 'wscrm' && class_exists('\SweetPortofolio\Api\WscrmClient')) {
    // Fetch from wscrm API
    $wscrm = new \SweetPortofolio\Api\WscrmClient();
    $data = $wscrm->fetchDemos();

    if (isset($data['error'])) {
        $error_message = 'WSCRM API Error: ' . esc_html($data['error']);
        $data = [];
    } else {
        // Data is already transformed to legacy format
        $categories_data = isset($data['categories']) ? $data['categories'] : [];
        $data = isset($data['demos']) ? $data['demos'] : [];
    }
} else {
    // Legacy API flow
    $data = get_transient($transient_key);

    if (empty($access_key)) {
        $error_message = 'Access Key is not set. Please configure the access key in <a href="' . admin_url('admin.php?page=portofolio-settings') . '">Portofolio Settings</a>.</div>';
    }

    if ($data !== false && isset($data['code']) && $data['code'] === 'rest_forbidden') {
        delete_transient($transient_key);
        $data = false;
    }

    if (false === $data) {
        $api_url = 'https://my.websweetstudio.com/wp-json/wp/v2/portofolio?access_key=' . $access_key;

        if (!empty($image_size)) {
            $api_url .= '&image_size=' . $image_size;
        }

        $response = wp_remote_get($api_url);

        if (is_wp_error($response)) {
            $error_message = 'Error fetching data from API: ' . esc_html($response->get_error_message());
        } else {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            error_log('API Response: ' . $body);

            if (isset($data['code']) && $data['code'] === 'rest_forbidden') {
                error_log('API Error: Access forbidden - ' . ($data['message'] ?? 'Unknown error'));
                $error_message = 'API Access Forbidden: Invalid access key. Please check your access key in <a href="' . admin_url('admin.php?page=portofolio-settings') . '">Portofolio Settings</a>.</div>';
                $data = [];
            } elseif (isset($data['code'])) {
                error_log('API Error: ' . $data['code'] . ' - ' . ($data['message'] ?? 'Unknown error'));
                $error_message = 'API Error: ' . esc_html($data['message'] ?? 'Unknown error');
                $data = [];
            }

            if (!isset($data['code'])) {
                set_transient($transient_key, $data, 12 * 3600);
            }
        }
    }

    if (isset($data['code']) && $data['code'] === 'rest_forbidden') {
        $error_message = 'API Access Forbidden: Invalid access key. Please check your access key in <a href="' . admin_url('admin.php?page=portofolio-settings') . '">Portofolio Settings</a>.</div>';
        $data = [];
    }

    // Fetch categories for legacy
    $categories_data = get_transient('jenis_web_data');
    if (!$categories_data) {
        if (!empty($access_key)) {
            $categories_api_url = 'https://my.websweetstudio.com/wp-json/wp/v2/jenis-web?access_key=' . $access_key;
            $response = wp_remote_get($categories_api_url);

            if (!is_wp_error($response)) {
                $body = wp_remote_retrieve_body($response);
                $categories_data = json_decode($body, true);

                if (is_array($categories_data) && !isset($categories_data['code'])) {
                    set_transient('jenis_web_data', $categories_data, 12 * 3600);
                }
            }
        }

        if (!is_array($categories_data)) {
            $categories_data = array();
        }
    }
}

// Get current page from URL
$current_page = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$jenis_web = isset($_GET['jenis_web']) ? sanitize_text_field($_GET['jenis_web']) : '';
if (isset($shortcode_category) && !empty($shortcode_category)) {
    $jenis_web = $shortcode_category;
}
?>
<?php if (!defined('SWEETPORTOFOLIO_SHORTCODE')) {
    get_header();
} ?>

<!-- Styles are now enqueued from assets/css/frontend.css -->

<div id="primary" class="content-area container">

    <main id="main" class="site-main" role="main">
        <div class="portfolio-container">
            <?php if (isset($error_message)) : ?>
                <div class="alert alert-warning">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Portfolio data consumed by vanilla JS (assets/js/script.js) -->
            <script type="text/plain" id="portfolios-data"><?php echo is_array($data) ? json_encode(array_values($data), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) : '[]'; ?></script>

            <?php
            $description_attr = (isset($atts) && is_array($atts) && isset($atts['description'])) ? $atts['description'] : 'yes';
            $grid_config = array(
                'initialPage' => max(1, (int) $current_page),
                'initialCategory' => $jenis_web,
                'showTitle' => isset($shortcode_title) ? $shortcode_title : 'yes',
                'styleThumbnail' => $style_thumbnail,
                'previewPage' => !empty($preview_page) ? get_the_permalink($preview_page) : '',
                'whatsappNumber' => $whatsapp_number,
                'portofolioCredit' => $portofolio_credit,
                'portofolioSelection' => is_array($portofolio_selection) ? array_values($portofolio_selection) : array(),
                'selectedIds' => array_values(array_map('intval', $shortcode_ids)),
                'showDescription' => ($description_attr !== 'no'),
            );
            ?>
            <!-- Portfolio grid (rendered by vanilla JS) -->
            <div class="portfolio-shell" data-config="<?php echo htmlspecialchars(json_encode($grid_config), ENT_QUOTES, 'UTF-8'); ?>">
                <!-- Filter Form -->
                <?php $filter_attr = (isset($atts) && is_array($atts) && isset($atts['filter'])) ? $atts['filter'] : 'yes';
                if ($filter_attr !== 'no') : ?>
                    <div class="filter-section">
                        <div class="filter-row">
                            <div class="filter-group">
                                <label for="category-filter" class="portfolio-filter-label">Filter kategori</label>
                                <select id="category-filter" class="filter-select" data-current="<?php echo esc_attr($jenis_web); ?>">
                                    <option value="">All Categories</option>
                                    <?php
                                    if (is_array($categories_data) && !empty($portofolio_selection)) {
                                        foreach ($categories_data as $category) {
                                            if (isset($category['slug']) && in_array($category['slug'], $portofolio_selection)) {
                                                echo '<option value="' . esc_attr($category['slug']) . '"' . selected($jenis_web, $category['slug'], false) . '>' . esc_html($category['category']) . '</option>';
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="portfolio-results-bar">
                    <div>
                        <span class="portfolio-results-kicker"></span>
                        <h2 class="portfolio-results-title">Koleksi portofolio</h2>
                    </div>
                    <div class="portfolio-results-summary"></div>
                </div>

                <div class="portfolio-empty-state" style="display:none">
                    <span class="portfolio-badge portfolio-badge-muted">Belum ada hasil</span>
                    <h3 class="portfolio-empty-title">Portofolio yang Anda cari belum tersedia.</h3>
                    <p class="portfolio-empty-text">Coba pilih kategori lain atau hubungi kami untuk meminta contoh desain yang paling sesuai dengan kebutuhan bisnis Anda.</p>
                </div>

                <div class="frame-portofolio" id="portfolio-grid" style="display:none"></div>

                <!-- Pagination -->
                <div class="pagination" style="display:none">
                    <span
                        data-pagination="prev"
                        class="pagination-btn"
                        role="button"
                        tabindex="0"
                        aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </span>

                    <div class="pagination-pages"></div>

                    <span
                        data-pagination="next"
                        class="pagination-btn"
                        role="button"
                        tabindex="0"
                        aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </span>
                </div>

                <div class="pagination-info" style="display:none">
                    <span></span>
                </div>
            </div>
        </div>
    </main><!-- #main -->
</div><!-- #primary -->

<?php if (!defined('SWEETPORTOFOLIO_SHORTCODE')) {
    get_footer();
} ?>
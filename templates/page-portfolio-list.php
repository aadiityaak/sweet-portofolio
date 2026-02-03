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
$transient_key = 'web_data_transient';
$data = get_transient($transient_key);

// Check if access_key is set
if (empty($access_key)) {
    $error_message = 'Access Key is not set. Please configure the access key in <a href="' . admin_url('admin.php?page=portofolio-settings') . '">Portofolio Settings</a>.</div>';
}

// Check if transient contains error data
if ($data !== false && isset($data['code']) && $data['code'] === 'rest_forbidden') {
    // Clear the transient with error data
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

        // Debug: Log API response
        error_log('API Response: ' . $body);

        // Check if response contains an error
        if (isset($data['code']) && $data['code'] === 'rest_forbidden') {
            error_log('API Error: Access forbidden - ' . ($data['message'] ?? 'Unknown error'));
            $error_message = 'API Access Forbidden: Invalid access key. Please check your access key in <a href="' . admin_url('admin.php?page=portofolio-settings') . '">Portofolio Settings</a>.</div>';
            // Don't save error data to transient
            $data = [];
        } elseif (isset($data['code'])) {
            error_log('API Error: ' . $data['code'] . ' - ' . ($data['message'] ?? 'Unknown error'));
            $error_message = 'API Error: ' . esc_html($data['message'] ?? 'Unknown error');
            $data = [];
        }

        // Only save valid data to transient
        if (!isset($data['code'])) {
            set_transient($transient_key, $data, 12 * 3600);
        }
    }
}

// Check if data contains error before filtering
if (isset($data['code']) && $data['code'] === 'rest_forbidden') {
    // Reset data to empty array if it contains an error
    $error_message = 'API Access Forbidden: Invalid access key. Please check your access key in <a href="' . admin_url('admin.php?page=portofolio-settings') . '">Portofolio Settings</a>.</div>';
    $data = [];
}

// Get current page from URL
$current_page = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$jenis_web = isset($_GET['jenis_web']) ? sanitize_text_field($_GET['jenis_web']) : '';
if (isset($shortcode_category) && !empty($shortcode_category)) {
    $jenis_web = $shortcode_category;
}

// Get categories for filter dropdown
$categories_data = get_transient('jenis_web_data');

// If categories data is not available, fetch it
if (!$categories_data) {
    if (!empty($access_key)) {
        $categories_api_url = 'https://my.websweetstudio.com/wp-json/wp/v2/jenis-web?access_key=' . $access_key;
        $response = wp_remote_get($categories_api_url);

        if (!is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            $categories_data = json_decode($body, true);

            // Save categories to transient for 12 hours
            if (is_array($categories_data) && !isset($categories_data['code'])) {
                set_transient('jenis_web_data', $categories_data, 12 * 3600);
            }
        }
    }

    // If still no data, set as empty array
    if (!is_array($categories_data)) {
        $categories_data = array();
    }
}
?>
<?php if (!defined('SWEETPORTOFOLIO_SHORTCODE')) {
    get_header();
} ?>

<style>
    /* Hide WhatsApp widget from main plugin on portfolio list page */
    #sweetaddons-whatsapp-widget {
        display: none !important;
    }

    .site-main {
        max-width: 100% !important;
        width: 100%;
    }

    .content-area {
        padding-top: 0;
        margin-top: 0;
    }

    .site-main {
        width: 100%;
        max-width: 100%;
    }



    /* Portfolio Container Styles */
    .portfolio-container {
        padding: 20px;
    }

    /* Filter Section Styles */
    .filter-section {
        margin-bottom: 20px;
    }

    .filter-row {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .filter-group {
        flex: 1;
        min-width: 200px;
    }

    .filter-select {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        background-color: #fff;
    }

    /* Portfolio Grid Styles */
    .frame-portofolio {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .col-portofolio {
        width: 100%;
        position: relative;
    }

    .card-portofolio {
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        width: 100%;
        height: 100%;
        position: relative;
    }

    .card-portofolio:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    }

    .card-image {
        position: relative;
        overflow: hidden;
        aspect-ratio: 1/1;
        width: 100%;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
        position: absolute;
        top: 0;
        left: 0;
    }

    .card-portofolio:hover .card-image img {
        transform: scale(1.05);
    }

    .card-credit {
        position: absolute;
        top: 10px;
        left: 10px;
        background: rgba(0, 0, 0, 0.7);
        color: #fff;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
    }

    .card-actions {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        background: rgba(0, 0, 0, 0.7);
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 2;
        pointer-events: none;
        box-sizing: border-box;
        padding: 20px;
    }

    .card-image:hover .card-actions {
        opacity: 1;
        pointer-events: auto;
    }

    .btn-preview,
    .btn-whatsapp {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 8px 15px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
        z-index: 3;
        position: relative;
        cursor: pointer;
        pointer-events: auto;
        max-width: 120px;
    }

    .btn-preview {
        background-color: #007bff;
        color: #fff;
    }

    .btn-preview:hover {
        background-color: #0069d9;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
    }

    .btn-whatsapp {
        background-color: #25d366;
        color: #fff;
    }

    .btn-whatsapp:hover {
        background-color: #128c7e;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(37, 211, 102, 0.3);
    }

    .card-content {
        padding: 15px;
    }

    .card-title {
        font-size: 18px;
        font-weight: 600;
        margin: 0 0 10px 0;
    }

    .card-title-link {
        color: #212529;
        text-decoration: none;
        transition: color 0.2s ease;
        display: block;
    }

    .card-title-link:hover {
        color: #007bff;
        text-decoration: underline;
    }

    .card-excerpt {
        font-size: 14px;
        color: #6c757d;
        margin: 0;
        line-height: 1.5;
    }

    /* Pagination Styles */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        margin-top: 30px;
        flex-wrap: wrap;
    }

    .pagination-btn {
        padding: 8px 12px;
        border: 1px solid #ddd;
        background: #fff;
        color: #212529;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .pagination-btn:hover:not(.disabled):not(.active) {
        background: #f8f9fa;
        border-color: #adb5bd;
    }

    .pagination-btn.active {
        background: #007bff;
        color: #fff;
        border-color: #007bff;
    }

    .pagination-btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .pagination-info {
        font-size: 14px;
        color: #6c757d;
        margin: 0 10px;
    }

    /* Error Message Styles */
    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid transparent;
        border-radius: 4px;
    }

    .alert-warning {
        color: #856404;
        background-color: #fff3cd;
        border-color: #ffeaa7;
    }

    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }

    /* Responsive Styles */
    @media screen and (max-width: 767px) {
        .portfolio-container {
            padding: 15px;
        }



        .filter-row {
            flex-direction: column;
            gap: 10px;
        }

        .pagination {
            gap: 5px;
        }

        .pagination-btn {
            padding: 6px 10px;
            font-size: 12px;
        }

        .pagination-info {
            font-size: 12px;
            width: 100%;
            text-align: center;
            margin: 10px 0;
        }
    }
</style>

<div id="primary" class="content-area container">

    <main id="main" class="site-main" role="main">
        <div class="portfolio-container">
            <?php if (isset($error_message)) : ?>
                <div class="alert alert-warning">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Add data for Alpine.js -->
            <script type="text/plain" id="portfolios-data"><?php echo is_array($data) ? json_encode(array_values($data)) : '[]'; ?></script>

            <!-- Alpine.js portfolio component -->
            <div x-data="portfolioGrid(
                    <?php echo $current_page; ?>,
                    '<?php echo $jenis_web; ?>',
                    '<?php echo isset($shortcode_title) ? $shortcode_title : 'yes'; ?>',
                    '<?php echo $style_thumbnail; ?>',
                    '<?php echo !empty($preview_page) ? get_the_permalink($preview_page) : ''; ?>',
                    '<?php echo $whatsapp_number; ?>',
                    '<?php echo $portofolio_credit; ?>',
                    <?php
                    // Ensure portofolio_selection is a valid array
                    if (!is_array($portofolio_selection)) {
                        $portofolio_selection = array();
                    }
                    // Output the array as a JavaScript array literal
                    echo '[' . implode(',', array_map(function ($item) {
                        return "'" . esc_js($item) . "'";
                    }, $portofolio_selection)) . ']';
                    ?>
                ,
                <?php
                echo '[' . implode(',', array_map('intval', $shortcode_ids)) . ']';
                ?>
                )">
                <!-- Filter Form with Alpine.js -->
                <?php $filter_attr = (isset($atts) && is_array($atts) && isset($atts['filter'])) ? $atts['filter'] : 'yes'; if ($filter_attr !== 'no') : ?>
                <div class="filter-section">
                    <div class="filter-row">
                        <div class="filter-group">
                            <select id="category-filter" x-model="selectedCategory" @change="filterPortfolios()" class="filter-select">
                                <option value="">All Categories</option>
                                <?php
                                if (is_array($categories_data) && !empty($portofolio_selection)) {
                                    foreach ($categories_data as $category) {
                                        if (isset($category['slug']) && in_array($category['slug'], $portofolio_selection)) {
                                            echo '<option value="' . esc_attr($category['slug']) . '">' . esc_html($category['category']) . '</option>';
                                        }
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="frame-portofolio">
                    <template x-for="(item, index) in paginatedPortfolios" :key="'portfolio-' + (item.id || index)">
                        <div class="col-portofolio">
                            <div class="card-portofolio">
                                <div class="card-image">
                                    <img :src="getImageUrl(item)" :alt="item.title" @error="$event.target.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkltYWdlIE5vdCBBdmFpbGFibGU8L3RleHQ+PC9zdmc+'">
                                    <span x-show="portofolioCredit" class="card-credit" x-text="portofolioCredit"></span>
                                    <div class="card-actions">
                                        <a :href="getPreviewUrl(item)" class="btn-preview" target="_blank">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0" />
                                                <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7" />
                                            </svg>
                                            Preview
                                        </a>
                                        <a x-show="whatsappNumber" :href="getWhatsAppUrl(item)" class="btn-whatsapp" target="_blank">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232" />
                                            </svg> Order
                                        </a>
                                    </div>
                                </div>
                                <div class="card-content">
                                    <h3 x-show="showTitle !== 'no'" class="card-title">
                                        <a :href="getPreviewUrl(item)" x-text="item.title" class="card-title-link"></a>
                                    </h3>
                                    <p x-show="item.excerpt" class="card-excerpt" x-text="item.excerpt"></p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Pagination with Alpine.js -->
                <div x-show="totalPages > 1" class="pagination">
                    <button @click="goToPage(currentPage - 1)" class="pagination-btn" :class="{'disabled': currentPage === 1}" :disabled="currentPage === 1" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </button>

                    <template x-for="page in getVisiblePages()" :key="'page-' + page">
                        <button @click="goToPage(page)" class="pagination-btn" :class="{'active': page === currentPage}" x-text="page"></button>
                    </template>

                    <button @click="goToPage(currentPage + 1)" class="pagination-btn" :class="{'disabled': currentPage === totalPages}" :disabled="currentPage === totalPages" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </button>
                </div>

                <div class="pagination-info" x-show="totalPages > 1">
                    <span x-text="`${(currentPage - 1) * itemsPerPage + 1}-${Math.min(currentPage * itemsPerPage, filteredPortfolios.length)} dari ${filteredPortfolios.length} items`"></span>
                </div>
            </div>
        </div>
    </main><!-- #main -->
</div><!-- #primary -->

<?php if (!defined('SWEETPORTOFOLIO_SHORTCODE')) {
    get_footer();
} ?>

<?php

/**
 * sweet-portofolio
 *
 * @package       SWEETPORTOFOLIO
 * @author        Aditya K
 * @license       gplv2
 * @version       1.0.0
 *
 * 
 **/

function sweet_portofolio_jenis_web_shortcode()
{
    // Don't render in admin area
    if (is_admin()) {
        return '';
    }

    $access_key = get_option('portofolio_access_key'); // Ganti dengan kunci akses yang Anda gunakan
    $portofolio_selection = get_option('portofolio_selection');

    // Ensure portofolio_selection is an array
    if (!is_array($portofolio_selection)) {
        $portofolio_selection = array();
    }

    // Cek apakah data sudah ada dalam transient
    $data = get_transient('jenis_web_data');

    if (!$data) {
        $api_url = 'https://my.websweetstudio.com/wp-json/wp/v2/jenis-web?access_key=' . $access_key;
        $response = wp_remote_get($api_url);

        if (is_wp_error($response)) {
            return 'Error fetching data.';
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Simpan data dalam transient selama 1 jam (3600 detik)
        $transient_key = 'jenis_web_data';
        $transient_set = set_transient($transient_key, $data, 12 * 3600);
    }

    if (!empty($data) && is_array($data)) {
        ob_start();

        // Add data for Alpine.js
        echo '<script type="text/plain" id="categories-data">' . json_encode($data) . '</script>';

        // Alpine.js modal component
?>
        <div x-data="categoryModal">
            <button @click.prevent="modalOpen = true" class="btn-modal-portofolio">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-diagram-3" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M6 3.5A1.5 1.5 0 0 1 7.5 2h1A1.5 1.5 0 0 1 10 3.5v1A1.5 1.5 0 0 1 8.5 6v1H14a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0v-1A.5.5 0 0 1 2 7h5.5V6A1.5 1.5 0 0 1 6 4.5v-1zM8.5 5a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1zM0 11.5A1.5 1.5 0 0 1 1.5 10h1A1.5 1.5 0 0 1 4 11.5v1A1.5 1.5 0 0 1 2.5 14h-1A1.5 1.5 0 0 1 0 12.5v-1zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1zm4.5.5A1.5 1.5 0 0 1 7.5 10h1a1.5 1.5 0 0 1 1.5 1.5v1A1.5 1.5 0 0 1 8.5 14h-1A1.5 1.5 0 0 1 6 12.5v-1zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1zm4.5.5a1.5 1.5 0 0 1 1.5-1.5h1a1.5 1.5 0 0 1 1.5 1.5v1a1.5 1.5 0 0 1-1.5 1.5h-1a1.5 1.5 0 0 1-1.5-1.5v-1zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1z" />
                </svg>
                Pilih Kategori
            </button>

            <!-- Modal with Alpine.js -->
            <div x-show="modalOpen"
                x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                @click.self="modalOpen = false"
                class="frame-modal-portofolio">
                <div class="content-portofolio">
                    <div class="modal-header">
                        <b>Pilih Kategori</b>
                        <button @click.stop="modalOpen = false" class="close-modal-portofolio">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="red" class="bi bi-x-lg" viewBox="0 0 16 16">
                                <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z" />
                            </svg>
                        </button>
                    </div>
                    <div class="modal-body-portofolio">
                        <ul class="list-group">
                            <template x-for="(category, index) in categories" :key="'category-' + (category.slug || index)">
                                <a @click.stop="selectCategory(category.slug || category.category)" class="list-portofolio">
                                    <div class="ms-2 me-auto portofolio-text-start">
                                        <div class="fw-bold portofolio-text-start"><b x-text="category.category"></b></div>
                                        <span x-text="'Demo website ' + category.category"></span>
                                    </div>
                                    <span class="badge-portofolio" x-text="category.count"></span>
                                </a>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php
        return ob_get_clean();
    }

    return 'No data available.';
}

add_shortcode('sweet-portofolio-jenis-web', 'sweet_portofolio_jenis_web_shortcode');

function portofolio_custom_masonry_shortcode($atts)
{
    // Don't render in admin area
    if (is_admin()) {
        return '';
    }

    $a = shortcode_atts(array(
        'default' => '',
        'include' => '',
        'title' => 'yes',
    ), $atts);

    ob_start();
    $jenis_web = isset($_GET['jenis_web']) ? sanitize_text_field($_GET['jenis_web']) : $a['default'];
    $image_size = get_option('portofolio_image_size');
    $access_key = get_option('portofolio_access_key');
    $preview_page = get_option('portofolio_preview_page');
    $style_thumbnail = get_option('portofolio_style_thumbnail');
    $portofolio_selection = get_option('portofolio_selection');
    $whatsapp_number = get_option('portofolio_whatsapp_number');
    $portofolio_credit = get_option('portofolio_credit');

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

    if (false === $data) {
        $api_url = 'https://my.websweetstudio.com/wp-json/wp/v2/portofolio?access_key=' . $access_key;

        if (!empty($image_size)) {
            $api_url .= '&image_size=' . $image_size;
        }

        $response = wp_remote_get($api_url);

        if (is_wp_error($response)) {
            return 'Error fetching data from API.';
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Simpan data dalam transient selama 12 jam
        set_transient($transient_key, $data, 12 * 3600);
    }

    // Filter data based on include parameter
    if (isset($a['include']) && $a['include'] != '' && is_array($data)) {
        $includes = explode(',', $a['include']);
        $data = array_filter($data, function ($item) use ($includes) {
            if (is_array($item)) {
                return isset($item['id']) && in_array($item['id'], $includes);
            }
            return false;
        });
    }

    // Add data for Alpine.js
    if (is_array($data)) {
        echo '<script type="text/plain" id="portfolios-data">' . json_encode(array_values($data)) . '</script>';
    } else {
        echo '<script type="text/plain" id="portfolios-data">[]</script>';
    }

    // Get current page from URL
    $current_page = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;

    // Alpine.js portfolio component
    ?>
    <div x-data="portfolioGrid(
        <?php echo $current_page; ?>,
        '<?php echo $jenis_web; ?>',
        '<?php echo $a['title']; ?>',
        '<?php echo $style_thumbnail; ?>',
        '<?php echo !empty($preview_page) ? get_the_permalink($preview_page) : ''; ?>',
        '<?php echo $whatsapp_number; ?>',
        '<?php echo $portofolio_credit; ?>',
        <?php echo json_encode($portofolio_selection); ?>
    )">
        <!-- Filter Form with Alpine.js -->
        <div class="filter-section mb-3">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="category-filter" class="form-label">Filter by Category:</label>
                        <select id="category-filter" x-model="selectedCategory" @change="filterPortfolios()" class="form-select">
                            <option value="">All Categories</option>
                            <?php
                            // Get categories for filter dropdown
                            $categories_data = get_transient('jenis_web_data');
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
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Items per page:</label>
                        <select x-model="itemsPerPage" @change="filterPortfolios()" class="form-select">
                            <option value="6">6</option>
                            <option value="12">12</option>
                            <option value="24">24</option>
                            <option value="48">48</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="frame-portofolio">
            <template x-for="(item, index) in paginatedPortfolios" :key="'portfolio-' + (item.id || index)">
                <div class="col-portofolio">
                    <div class="card-portofolio">
                        <div class="position-relative">
                            <img :src="getImageUrl(item)" :alt="item.title" class="card-img-top">
                            <span x-show="portofolioCredit" class="portofolio-credit" x-text="portofolioCredit"></span>
                        </div>
                        <div class="card-body">
                            <h5 x-show="showTitle !== 'no'" class="portofolio-title" x-text="item.title"></h5>
                            <div class="group-btn-portfolio" role="group" aria-label="Basic example">
                                <a :href="getPreviewUrl(item)" class="btn-preview-portfolio" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16">
                                        <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0" />
                                        <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7" />
                                    </svg>
                                    Preview
                                </a>
                                <a x-show="whatsappNumber" :href="getWhatsAppUrl(item)" class="btn-whatsapp-portfolio" target="_blank">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                                        <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232" />
                                    </svg> Order
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Pagination with Alpine.js -->
        <nav x-show="totalPages > 1" aria-label="Page navigation" class="pt-3">
            <ul class="pagination justify-content-center">
                <template x-for="page in totalPages" :key="'page-' + page">
                    <li :class="{'page-item': true, 'active': page === currentPage}">
                        <a @click="goToPage(page)" class="page-link" href="javascript:void(0)" x-text="page"></a>
                    </li>
                </template>
            </ul>
        </nav>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('sweet-portofolio-list', 'portofolio_custom_masonry_shortcode');

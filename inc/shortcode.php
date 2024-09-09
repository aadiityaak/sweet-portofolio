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

 function sweet_portofolio_jenis_web_shortcode() {
    $access_key = get_option('portofolio_access_key'); // Ganti dengan kunci akses yang Anda gunakan
    $portofolio_selection = get_option('portofolio_selection');
    
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

    if (!empty($data)) {
        ob_start();
        $buttons_markup = '';
        ?>
        <a class="btn-modal-portofolio">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-diagram-3" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M6 3.5A1.5 1.5 0 0 1 7.5 2h1A1.5 1.5 0 0 1 10 3.5v1A1.5 1.5 0 0 1 8.5 6v1H14a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0v-1A.5.5 0 0 1 2 7h5.5V6A1.5 1.5 0 0 1 6 4.5v-1zM8.5 5a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1zM0 11.5A1.5 1.5 0 0 1 1.5 10h1A1.5 1.5 0 0 1 4 11.5v1A1.5 1.5 0 0 1 2.5 14h-1A1.5 1.5 0 0 1 0 12.5v-1zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1zm4.5.5A1.5 1.5 0 0 1 7.5 10h1a1.5 1.5 0 0 1 1.5 1.5v1A1.5 1.5 0 0 1 8.5 14h-1A1.5 1.5 0 0 1 6 12.5v-1zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1zm4.5.5a1.5 1.5 0 0 1 1.5-1.5h1a1.5 1.5 0 0 1 1.5 1.5v1a1.5 1.5 0 0 1-1.5 1.5h-1a1.5 1.5 0 0 1-1.5-1.5v-1zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1z"/>
            </svg>
            Pilih Kategori
        </a>
        <!-- Modal -->
        <div class="frame-modal-portofolio">
            <div class="content-portofolio">
                <div class="modal-header">
                    <b>Pilih Kategori</b>
                    <button class="close-modal-portofolio">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="red" class="bi bi-x-lg" viewBox="0 0 16 16">
                            <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                        </svg>
                    </button>
                </div>
                <div class="modal-body-portofolio">
                    <ul class="list-group">
                        <?php
                        foreach ($data as $category) {
                            if (in_array($category['slug'], $portofolio_selection)) {
                                ?>
                                <a href="?jenis_web=<?php echo $category['slug']; ?>" class="list-portofolio">
                                    <div class="ms-2 me-auto portofolio-text-start">
                                        <div class="fw-bold portofolio-text-start"><b><?php echo $category['category']; ?></b></div>
                                        Demo website <?php echo $category['category']; ?>
                                    </div>
                                    <span class="badge-portofolio">
                                        <?php echo $category['count']; ?>
                                    </span>
                                </a>
                                <?php
                            }
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    return 'No data available.';
}

add_shortcode('sweet-portofolio-jenis-web', 'sweet_portofolio_jenis_web_shortcode');

function portofolio_custom_masonry_shortcode($atts) {
    $a = shortcode_atts(array(
        'default' => '',
        'include' => '',
        'title' => 'yes',
    ), $atts);
    
    ob_start();
    $jenis_web = isset($_GET['jenis_web']) ? sanitize_text_field($_GET['jenis_web']) : $a['default'];
    $image_size = get_option('portofolio_image_size'); // Ganti dengan kunci akses yang Anda gunakan
    $access_key = get_option('portofolio_access_key'); // Ganti dengan kunci akses yang Anda gunakan
    $preview_page = get_option('portofolio_preview_page'); // Ganti dengan kunci akses yang Anda gunakan
    $style_thumbnail = get_option('portofolio_style_thumbnail');
    $portofolio_selection = get_option('portofolio_selection');

    // Menggunakan set_transient untuk menyimpan data
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

    if (isset($jenis_web) && $jenis_web != '') {
        $data = array_filter($data, function($item) use ($jenis_web) {
            if (is_array($item)) {
                return isset($item['jenis']) && strpos($item['jenis'], $jenis_web) !== false;
            }
        });
    } elseif ($portofolio_selection) {
        $data = array_filter($data, function($item) use ($portofolio_selection) {
            if (is_array($item)) {
                return isset($item['jenis']) && in_array($item['jenis'], $portofolio_selection);
            }
        });
    }

    if (isset($a['include']) && $a['include'] != '') {
        $includes = explode(',', $a['include']);
        $data = array_filter($data, function($item) use ($includes) {
            if (is_array($item)) {
                return isset($item['id']) && in_array($item['id'], $includes);
            }
        });
    }

    $current_page = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
    $items_per_page = 12;
    $total_pages = ceil(count($data) / $items_per_page);
    $start_index = ($current_page - 1) * $items_per_page;
    $end_index = $start_index + $items_per_page;
    $current_page_data = array_slice($data, $start_index, $items_per_page);
    ?>
    <div class="frame-portofolio">
        <?php foreach ($current_page_data as $item) {
            $images = ($style_thumbnail == 'thumbnail') ? $item['thumbnail_url'] : $item['screenshot'];
            ?>
            <div class="col-portofolio">
                <div class="card-portofolio">
                    <div class="position-relative">
                        <img src="<?php echo esc_url($images); ?>" class="card-img-top" alt="<?php echo esc_attr($item['title']); ?>">
                        <?php
                        $portofolio_credit = get_option('portofolio_credit');
                        if ($portofolio_credit) {
                            echo '<span class="portofolio-credit">' . $portofolio_credit . '</span>';
                        }
                        ?>
                    </div>
                    <div class="card-body">
                        <?php if ($a['title'] != 'no') { ?>
                            <h5 class="portofolio-title"><?php echo esc_html($item['title']); ?></h5>
                        <?php } ?>
                        <div class="group-btn-portfolio" role="group" aria-label="Basic example">
                            <a class="btn-preview-portfolio" target="_blank" href="<?php echo get_the_permalink($preview_page); ?>?id=<?php echo esc_html($item['id']); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye-fill" viewBox="0 0 16 16">
                                <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0"/>
                                <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8m8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7"/>
                            </svg>
                                Preview
                            </a>
                            <?php
                            $whatsapp_number = get_option('portofolio_whatsapp_number'); // Prefix added to option name
                            $whatsapp_number = preg_replace('/[^0-9]/', '', $whatsapp_number);
                            $whatsapp_number = preg_replace('/^0/', '62', $whatsapp_number);

                            if (!empty($whatsapp_number)) {
                                $whatsapp_message = "Saya tertarik dengan " . urlencode($item['title']);
                                $whatsapp_url = "https://wa.me/$whatsapp_number?text=" . urlencode($whatsapp_message);
                                ?>
                                <a target="_blank" href="<?php echo esc_url($whatsapp_url); ?>" class="btn-whatsapp-portfolio">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                                    <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
                                </svg> Order
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
    <?php
    $jenis_web = isset($_GET['jenis_web']) ? '&jenis_web=' . $_GET['jenis_web'] : '';
    if ($total_pages > 1) {
        echo "<nav aria-label='Page navigation' class='pt-3'>";
        echo "<ul class='pagination justify-content-center'>";
        for ($i = 1; $i <= $total_pages; $i++) {
            $active_class = ($i == $current_page) ? "active" : "";
            echo "<li class='page-item $active_class'>";
            echo "<a class='page-link' href='?halaman={$i}{$jenis_web}'>$i</a>";
            echo "</li>";
        }
        echo "</ul>";
        echo "</nav>";
    }
    return ob_get_clean();
}
add_shortcode('sweet-portofolio-list', 'portofolio_custom_masonry_shortcode');

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
    
    // Cek apakah data sudah ada dalam sesi
    if (isset($_SESSION['jenis_web_data'])) {
        $data = $_SESSION['jenis_web_data'];
    } else {
        $api_url = 'https://my.websweetstudio.com/wp-json/wp/v2/jenis-web?access_key=' . $access_key;

        $response = wp_remote_get($api_url);

        if (is_wp_error($response)) {
            return 'Error fetching data.';
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Simpan data dalam sesi
        $_SESSION['jenis_web_data'] = $data;
    }

    if (!empty($data)) {
        ob_start();
        $buttons_markup = '';
        ?>
        <a type="button" class="btn btn-primary text-white" data-bs-toggle="modal" data-bs-target="#portofolioModal">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-diagram-3" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M6 3.5A1.5 1.5 0 0 1 7.5 2h1A1.5 1.5 0 0 1 10 3.5v1A1.5 1.5 0 0 1 8.5 6v1H14a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0V8h-5v.5a.5.5 0 0 1-1 0v-1A.5.5 0 0 1 2 7h5.5V6A1.5 1.5 0 0 1 6 4.5v-1zM8.5 5a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1zM0 11.5A1.5 1.5 0 0 1 1.5 10h1A1.5 1.5 0 0 1 4 11.5v1A1.5 1.5 0 0 1 2.5 14h-1A1.5 1.5 0 0 1 0 12.5v-1zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1zm4.5.5A1.5 1.5 0 0 1 7.5 10h1a1.5 1.5 0 0 1 1.5 1.5v1A1.5 1.5 0 0 1 8.5 14h-1A1.5 1.5 0 0 1 6 12.5v-1zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1zm4.5.5a1.5 1.5 0 0 1 1.5-1.5h1a1.5 1.5 0 0 1 1.5 1.5v1a1.5 1.5 0 0 1-1.5 1.5h-1a1.5 1.5 0 0 1-1.5-1.5v-1zm1.5-.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-1z"/>
            </svg>
            Pilih Kategori
        </a>
        <!-- Modal -->
        <div class="modal fade" id="portofolioModal" tabindex="-1" aria-labelledby="portofolioModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="portofolioModalLabel">Pilih Kategori</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                    <ul class="list-group">
                    <?php
                    foreach ($data as $category) {
                        if(in_array($category['slug'], $portofolio_selection)){
                        ?>
                            <a href="?jenis_web=<?php echo $category['slug']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                <div class="fw-bold text-start"><b><?php echo $category['category']; ?></b></div>
                                    Demo website <?php echo $category['category']; ?>
                                </div>
                                <span class="badge bg-danger rounded-pill">
                                    <?php echo $category['count']; ?>
                                </span>
                            </a>
                        <?php
                        }
                    }
                    echo '</ul>';
                echo '</div>
            </div>
            </div>
        </div>';
        return ob_get_clean();
    }

    return 'No data available.';
}

add_shortcode('sweet-portofolio-jenis-web', 'sweet_portofolio_jenis_web_shortcode');

function portofolio_custom_masonry_shortcode($atts) {
	$a = shortcode_atts( array(
		'default' => '',
        'include' => '',
        'title' => 'yes',
	), $atts );
    ob_start();
    $jenis_web = isset($_GET['jenis_web']) ? sanitize_text_field($_GET['jenis_web']) : $a['default'];
    $image_size = get_option('portofolio_image_size'); // Ganti dengan kunci akses yang Anda gunakan
    $access_key = get_option('portofolio_access_key'); // Ganti dengan kunci akses yang Anda gunakan
    $preview_page = get_option('portofolio_preview_page'); // Ganti dengan kunci akses yang Anda gunakan
    $style_thumbnail = get_option('portofolio_style_thumbnail');
    $portofolio_selection = get_option('portofolio_selection');

    // Cek apakah data sudah ada dalam sesi
    if (isset($_SESSION['web_data'])) {
        $data = $_SESSION['web_data'];
    } else {
        $api_url = 'https://my.websweetstudio.com/wp-json/wp/v2/portofolio?access_key='.$access_key;
    
        // if (!empty($jenis_web)) {
        //     $api_url .= '&jenis_web=' . $jenis_web;
        // }
    
        if (!empty($image_size)) {
            $api_url .= '&image_size=' . $image_size;
        }
    
        $response = wp_remote_get($api_url);
    
        if (is_wp_error($response)) {
            return 'Error fetching data from API.';
        }
    
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
    
        // Simpan data dalam sesi
        $_SESSION['web_data'] = $data;
    }

    // if(isset($_GET['hapus']))
    // {
    // unset($_SESSION[$_GET['hapus']]);
    // }
    // print_r($data);
    // Filter hanya elemen dengan jenis 'landing-page'
    if (isset($jenis_web) && $jenis_web != '') {
        $data = array_filter($data, function($item) use ($jenis_web) {
            if (is_array($item)) {
                return isset($item['jenis']) && strpos($item['jenis'], $jenis_web) !== false;
            }
        });
        
    } elseif ($portofolio_selection) {
        $data = array_filter($data, function($item) use ($portofolio_selection, $jenis_web) {
            if (is_array($item)) {
                return isset($item['jenis']) && in_array($item['jenis'], $portofolio_selection);
            }
        });
    }

    // Tampilkan hanya id tertentu
    if (isset($a['include']) && $a['include'] != '') {
        $includes = $a['include'] ? explode(',', $a['include']) : [];
        $data = array_filter($data, function($item) use ($includes) {
            if (is_array($item)) {
                return isset($item['id']) && in_array($item['id'], $includes);
            }
        });
    }

    // Menentukan halaman yang sedang aktif (dapat berasal dari parameter URL atau variabel lain)
    $current_page = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;

    // Menentukan jumlah item per halaman
    $items_per_page = 12;

    // Menghitung total halaman
    $total_pages = ceil(count($data) / $items_per_page);

    // Menghitung indeks awal dan akhir untuk array yang akan ditampilkan pada halaman saat ini
    $start_index = ($current_page - 1) * $items_per_page;
    $end_index = $start_index + $items_per_page;

    // Memotong array sesuai dengan halaman saat ini
    $current_page_data = array_slice($data, $start_index, $items_per_page);
    ?>
    <div class="row g-3">
        <?php foreach ($current_page_data as $item) {
            $images = ($style_thumbnail == 'thumbnail') ? $item['thumbnail_url'] : $item['screenshot'];
                // if(($jenis_web == $item['jenis']) || empty($jenis_web)){
                ?>
                <div class="col-md-4 col-6">
                    <div class="card">
                        <div class="position-relative">
                            <img src="<?php echo esc_url($images); ?>" class="card-img-top" alt="<?php echo esc_attr($item['title']); ?>">
                            <?php
                            $portofolio_credit = get_option('portofolio_credit');
                            if($portofolio_credit) {
                                echo '<span class="portofolio-credit">'.$portofolio_credit.'</span>';
                            }

                            ?>
                        </div>
                        <div class="card-body">
                            <?php if($a['title'] != 'no'){ ?>
                                <h5 class="card-title h6"><?php echo esc_html($item['title']); ?></h5>
                            <?php } ?>
                            <div class="btn-group w-100" role="group" aria-label="Basic example">
                                <a class="btn btn-primary" target="_blank" href="<?php echo get_the_permalink($preview_page); ?>?id=<?php echo esc_html($item['id']); ?>">Preview</button>
                                <?php
                                    $whatsapp_number = get_option('portofolio_whatsapp_number'); // Prefix added to option name
                                    // Mengganti "08" dengan "628" dan menghapus karakter selain angka
                                    $whatsapp_number = preg_replace('/[^0-9]/', '', $whatsapp_number);
                                    $whatsapp_number = preg_replace('/^0/', '62', $whatsapp_number);

                                    if (!empty($whatsapp_number)) {
                                        $whatsapp_message = "Saya tertarik dengan " . urlencode($item['title']);
                                        $whatsapp_url = "https://wa.me/$whatsapp_number?text=" . urlencode($whatsapp_message);
                                        ?>
                                        <a target="_blank" href="<?php echo esc_url($whatsapp_url); ?>" class="btn btn-success">Order</a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
            // }
        }
        ?>
    </div>
    <!-- Menampilkan Pagination -->
    <?php
    // Menampilkan link pagination hanya jika lebih dari 1 halaman
    $jenis_web = isset($_GET['jenis_web']) ? '&jenis_web='.$_GET['jenis_web']: '';
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
    ?>
    <?php
    return ob_get_clean();
}
add_shortcode('sweet-portofolio-list', 'portofolio_custom_masonry_shortcode');

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
    $api_url = 'https://my.websweetstudio.com/wp-json/wp/v2/jenis-web?access_key=' . $access_key;

    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return 'Error fetching data.';
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!empty($data)) {
        ob_start();
        $buttons_markup = '';
        ?>
        <a type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#portofolioModal">
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
                        ?>
                            <a href="?jenis_web=<?php echo $category['slug']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                <div class="fw-bold">
                                    <b><?php echo $category['category']; ?></b>
                                </div>
                                    Demo website <?php echo $category['category']; ?>
                                </div>
                                <span class="badge bg-danger rounded-pill">
                                    <?php echo $category['count']; ?>
                                </span>
                            </a>
                        <?php
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

function portofolio_custom_masonry_shortcode() {
    ob_start();
    $jenis_web = isset($_GET['jenis_web']) ? sanitize_text_field($_GET['jenis_web']) : '';
    $access_key = get_option('portofolio_access_key'); // Ganti dengan kunci akses yang Anda gunakan

    $api_url = 'https://my.websweetstudio.com/wp-json/wp/v2/portofolio?access_key='.$access_key;

    if (!empty($jenis_web)) {
        $api_url .= '&jenis_web=' . $jenis_web;
    }

    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return 'Error fetching data from API.';
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (empty($data)) {
        return 'No data available.';
    }

    ?>
    <div class="row g-3">
        <?php foreach ($data as $item) : 
            ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="position-relative">
                        <img src="<?php echo esc_url($item['thumbnail_url']); ?>" class="card-img-top" alt="<?php echo esc_attr($item['title']); ?>">
                        <?php
                        $portofolio_credit = get_option('portofolio_credit');
                        if($portofolio_credit) {
                            echo '<span class="portofolio-credit">'.$portofolio_credit.'</span>';
                        }

                        ?>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title h6"><?php echo esc_html($item['title']); ?></h5>
                        <div class="btn-group w-100" role="group" aria-label="Basic example">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#portofolio-modal-<?php echo esc_attr($item['id']); ?>">Preview</button>
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

            <!-- Modal -->
            <div class="modal fade lazy-load-modal" id="portofolio-modal-<?php echo esc_attr($item['id']); ?>" tabindex="-1" aria-labelledby="portofolio-modalLabel-<?php echo esc_attr($item['id']); ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xxl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title text-truncate" id="portofolio-modalLabel-<?php echo esc_attr($item['id']); ?>">Preview <?php echo esc_html($item['title']); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden pt-5">Loading...</span>
                                </div>
                            </div>
                            <div class="embed-responsive embed-responsive-16by9">
                                <iframe class="lazy-iframe embed-responsive-item" data-src="<?php echo esc_url($item['url_live_preview']); ?>" width="100%" height="600" frameborder="0"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('sweet-portofolio-list', 'portofolio_custom_masonry_shortcode');

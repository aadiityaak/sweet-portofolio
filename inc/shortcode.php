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
        echo '
        <div class="dropdown text-md-end">
            <button class="btn btn-primary dropdown-toggle text-white" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            Jenis Web
            </button>
            <ul class="dropdown-menu">';
            foreach ($data as $category) {
                echo '<li><a class="dropdown-item" href="?jenis_web='.$category['slug'].'" >'.$category['category'].' ('.$category['count'].')</a></li>';
            }
            echo '</ul>';
        echo '</div>';
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
                    <img src="<?php echo esc_url($item['thumbnail_url']); ?>" class="card-img-top" alt="<?php echo esc_attr($item['title']); ?>">
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
            <div class="modal fade" id="portofolio-modal-<?php echo esc_attr($item['id']); ?>" tabindex="-1" aria-labelledby="portofolio-modalLabel-<?php echo esc_attr($item['id']); ?>" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="portofolio-modalLabel-<?php echo esc_attr($item['id']); ?>"><?php echo esc_html($item['title']); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- <iframe src="<?php echo esc_url($item['url_live_preview']); ?>" width="100%" height="600" frameborder="0"></iframe> -->
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

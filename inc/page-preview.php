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
global $post;
$access_key = get_option('portofolio_access_key'); // Ganti dengan kunci akses yang Anda gunakan
$image_size = get_option('portofolio_image_size'); // Ganti dengan kunci akses yang Anda gunakan
$id = $_GET['id'] ?? '';
$api_url = 'https://my.websweetstudio.com/wp-json/wp/v2/id?id='.$id.'&access_key='.$access_key;

if (!empty($image_size)) {
    $api_url .= '&image_size=' . $image_size;
}
$response = wp_remote_get($api_url);

if (is_wp_error($response)) {
    return 'Error fetching data from API.';
}

$body = wp_remote_retrieve_body($response);
$data = json_decode($body, true);
$data_title = $data['title'] ?? '';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <header class="d-flex justify-content-between align-items-center p-2 bg-dark shadow-sm">
        <!-- Back to Home Button -->
        <a class="btn btn-light btn-sm align-middle" href="<?php echo esc_url( get_home_url() ); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house" viewBox="0 0 16 16">
                <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5ZM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5 5 5Z"/>
            </svg>
            Home
        </a>
        <span class="text-light d-none d-md-flex"><b><?php echo get_bloginfo( 'name' ); ?> - <?php echo $data_title; ?></b></span>
        <?php
            $whatsapp_option = get_option('portofolio_whatsapp_number'); // Prefix added to option name
            // Mengganti "08" dengan "628" dan menghapus karakter selain angka
            $whatsapp_number = preg_replace('/[^0-9]/', '', $whatsapp_option);
            $whatsapp_number = preg_replace('/^0/', '62', $whatsapp_number);

            if (!empty($whatsapp_number)) {
                $whatsapp_message = "Saya tertarik dengan " . urlencode($data_title);
                $whatsapp_url = "https://wa.me/$whatsapp_number?text=" . urlencode($whatsapp_message);
                ?>
                <a target="_blank" href="<?php echo esc_url($whatsapp_url); ?>" class="align-middle btn-sm btn btn-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                        <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/>
                    </svg>
                    <?php echo $whatsapp_option; ?>
                </a>
        <?php } ?>
    </header>

    <div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">
            <!-- Your iframe code here -->
            <?php
            $demo_url = $data['url_live_preview'] ?? get_home_url();
            
            if (!empty($demo_url)) :
            ?>
                <div id="iframe-container">
                    <iframe src="<?php echo esc_url( $demo_url ); ?>" width="100%" height="600" frameborder="0" allowfullscreen></iframe>
                </div>
            <?php else : ?>
                <p>Demo URL is not set.</p>
            <?php endif; ?>
        </main><!-- #main -->
    </div><!-- #primary -->

    <?php wp_footer(); ?>
</body>
</html>
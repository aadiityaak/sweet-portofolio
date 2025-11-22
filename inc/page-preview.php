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
$portofolio_page = get_option('portofolio_page');
$id = $_GET['id'] ?? '';
$api_url = 'https://my.websweetstudio.com/wp-json/wp/v2/id?id=' . $id . '&access_key=' . $access_key;

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
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <style>
        #wpadminbar {
            display: none !important;
        }

        /* Hide WhatsApp widget from main plugin on preview page */
        #sweetaddons-whatsapp-widget {
            display: none !important;
        }

        html {
            margin: 0 !important;
            padding: 0;
            height: 100%;
        }

        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            height: 100%;
        }

        .site-main {
            max-width: 100% !important;
            flex: 1;
            overflow: hidden;
        }

        .content-area {
            padding-top: 0;
            margin-top: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header Preview Styles */
        .header-preview {
            position: sticky;
            top: 0;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 10px 20px;
            z-index: 999;
            height: 60px;
            flex-shrink: 0;
        }

        .btn-whatsapp-portfolio {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background-color: #6c757d;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-whatsapp-portfolio:hover {
            background-color: #5a6268;
            transform: translateY(-1px);
        }

        .text-judul {
            font-size: 16px;
            font-weight: 600;
            color: #212529;
            text-align: center;
            flex: 1;
            margin: 0 15px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media screen and (max-width: 767px) {
            .header-preview {
                padding: 8px 15px;
                height: 50px;
            }

            .text-judul {
                font-size: 14px;
                margin: 0 10px;
            }

            .btn-whatsapp-portfolio {
                padding: 6px 12px;
                font-size: 12px;
            }

            .btn-whatsapp-portfolio svg {
                width: 14px;
                height: 14px;
            }
        }

        /* Iframe Container Styles */
        #iframe-container {
            width: 100%;
            height: 100%;
            position: relative;
            margin-top: 0;
            padding-top: 0;
            flex: 1;
        }

        #iframe-container iframe {
            width: 100%;
            height: 100%;
            border: none;
            display: block;
            vertical-align: top;
        }

        @media screen and (max-width: 767px) {
            #iframe-container {
                height: 100%;
            }
        }

        /* Demo Not Available Styles */
        .demo-not-available {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 4rem 2rem;
            height: 100%;
            margin-top: 0;
            padding-top: 4rem;
            flex: 1;
        }

        .demo-icon {
            color: #6c757d;
            margin-bottom: 1.5rem;
            opacity: 0.7;
        }

        .demo-not-available h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #212529;
        }

        .demo-not-available p {
            font-size: 1rem;
            color: #6c757d;
            max-width: 500px;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .demo-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn-demo-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .btn-whatsapp-demo {
            background-color: #25d366;
            color: #fff;
        }

        .btn-whatsapp-demo:hover {
            background-color: #128c7e;
            transform: translateY(-2px);
        }

        .btn-back-demo {
            background-color: #6c757d;
            color: #fff;
        }

        .btn-back-demo:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }

        @media (max-width: 767px) {
            .demo-not-available {
                padding: 2rem 1rem;
                height: 100%;
            }

            .demo-actions {
                flex-direction: column;
                width: 100%;
                max-width: 280px;
            }

            .btn-demo-action {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body <?php body_class('wss-preview-page'); ?>>
    <div id="primary" class="content-area">
        <header class="header-preview">
            <!-- Back to Home Button -->
            <a class="btn-whatsapp-portfolio" href="<?php echo get_the_permalink($portofolio_page); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z" />
                </svg>
                Kembali
            </a>
            <span class="text-judul"><b><?php echo get_bloginfo('name'); ?> - <?php echo $data_title; ?></b></span>
            <?php
            $whatsapp_option = get_option('portofolio_whatsapp_number'); // Prefix added to option name
            // Mengganti "08" dengan "628" dan menghapus karakter selain angka
            $whatsapp_number = preg_replace('/[^0-9]/', '', $whatsapp_option);
            $whatsapp_number = preg_replace('/^0/', '62', $whatsapp_number);

            if (!empty($whatsapp_number)) {
                $whatsapp_message = "Saya tertarik dengan " . urlencode($data_title);
                $whatsapp_url = "https://wa.me/$whatsapp_number?text=" . urlencode($whatsapp_message);
            ?>
                <a target="_blank" href="<?php echo esc_url($whatsapp_url); ?>" class="btn-whatsapp-portfolio">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                        <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z" />
                    </svg>
                    Order Langsung
                </a>
            <?php } ?>
        </header>
        <main id="main" class="site-main" role="main">
            <!-- Your iframe code here -->
            <?php
            $demo_url = $data['url_live_preview'] ?? '';

            if (!empty($demo_url)) :
            ?>
                <div id="iframe-container">
                    <iframe src="<?php echo esc_url($demo_url); ?>" allowfullscreen></iframe>
                </div>
            <?php else : ?>
                <div class="demo-not-available">
                    <div class="demo-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M4.406 3.342A5.53 5.53 0 0 1 8 2c2.69 0 4.923 2 5.166 4.579C14.758 6.804 16 8.137 16 9.773 16 11.569 14.502 13 12.687 13H3.781C1.708 13 0 11.366 0 9.318c0-1.763 1.266-3.223 2.942-3.593.143-.863.698-1.723 1.464-2.383zm.653.757c-.757.653-1.153 1.44-1.153 2.056v.448l-.445.049C2.064 6.805 1 7.952 1 9.318 1 10.785 2.23 12 3.781 12h8.906C13.98 12 15 10.988 15 9.773c0-1.216-1.02-2.228-2.313-2.228h-.5v-.5C12.188 4.825 10.328 3 8 3a4.53 4.53 0 0 0-2.941 1.1z" />
                        </svg>
                    </div>
                    <h3>Demo Tidak Tersedia</h3>
                    <p>Maaf, demo untuk portfolio ini belum tersedia. Silakan hubungi kami untuk informasi lebih lanjut.</p>
                    <div class="demo-actions">
                        <?php if (!empty($whatsapp_number)) : ?>
                            <a target="_blank" href="<?php echo esc_url($whatsapp_url); ?>" class="btn-demo-action btn-whatsapp-demo">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232" />
                                </svg>
                                Hubungi Kami
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo get_the_permalink($portofolio_page); ?>" class="btn-demo-action btn-back-demo">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z" />
                            </svg>
                            Kembali ke Portfolio
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </main><!-- #main -->
    </div><!-- #primary -->

    <?php wp_footer(); ?>
</body>

</html>
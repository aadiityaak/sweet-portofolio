<?php

/**
 * Template Name: Preview
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
        :root {
            --sp-primary: #cc785c;
            --sp-primary-active: #a9583e;
            --sp-ink: #141413;
            --sp-body: #3d3d3a;
            --sp-muted: #6c6a64;
            --sp-hairline: #e6dfd8;
            --sp-canvas: #faf9f5;
            --sp-surface-soft: #f5f0e8;
            --sp-surface-card: #efe9de;
            --sp-surface-dark: #181715;
            --sp-surface-dark-elevated: #252320;
            --sp-on-primary: #ffffff;
            --sp-on-dark: #faf9f5;
            --sp-on-dark-soft: #a09d96;
        }

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
            background: var(--sp-canvas);
            color: var(--sp-body);
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        .site-main {
            max-width: 1200px !important;
            width: 100%;
            margin: 0 auto;
            flex: 1;
            overflow: hidden;
        }

        .content-area {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 24px;
            box-sizing: border-box;
        }

        /* Header Preview Styles */
        .header-preview {
            position: relative;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            background: var(--sp-surface-soft);
            border: 1px solid var(--sp-hairline);
            border-radius: 16px;
            padding: 16px 20px;
            margin-bottom: 24px;
            flex-shrink: 0;
            box-sizing: border-box;
        }

        .preview-header-copy {
            min-width: 0;
            flex: 1;
            text-align: center;
        }

        .preview-kicker {
            display: inline-block;
            margin-bottom: 6px;
            color: var(--sp-muted);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        .preview-title {
            margin: 0;
            color: var(--sp-ink);
            font-family: "Cormorant Garamond", "Times New Roman", serif;
            font-size: clamp(1.45rem, 3vw, 2.15rem);
            font-weight: 500;
            line-height: 1.1;
            letter-spacing: -0.03em;
        }

        .preview-subtitle {
            margin: 8px 0 0;
            color: var(--sp-muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .preview-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 44px;
            padding: 12px 18px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
            white-space: nowrap;
            border: 1px solid transparent;
        }

        .preview-btn-back {
            background: var(--sp-canvas);
            border-color: var(--sp-hairline);
            color: var(--sp-ink);
        }

        .preview-btn-back:hover {
            background: var(--sp-surface-card);
            color: var(--sp-ink);
        }

        .preview-btn-primary {
            background: var(--sp-primary);
            color: var(--sp-on-primary);
        }

        .preview-btn-primary:hover {
            background: var(--sp-primary-active);
            color: var(--sp-on-primary);
        }

        @media screen and (max-width: 767px) {
            .content-area {
                padding: 16px;
            }

            .header-preview {
                flex-direction: column;
                align-items: stretch;
                padding: 18px 16px;
            }

            .preview-header-copy {
                text-align: left;
            }

            .preview-btn {
                width: 100%;
            }

            .header-preview-actions {
                display: flex;
                flex-direction: column;
                gap: 12px;
                width: 100%;
            }
        }

        /* Iframe Container Styles */
        #iframe-container {
            width: 100%;
            height: 100%;
            position: relative;
            flex: 1;
            padding: 20px;
            background: var(--sp-surface-dark);
            border-radius: 16px;
            box-sizing: border-box;
            border: 1px solid rgba(250, 249, 245, 0.08);
        }

        #iframe-container iframe {
            width: 100%;
            height: 100%;
            border: 0;
            border-radius: 12px;
            display: block;
            vertical-align: top;
            background: #fff;
        }

        @media screen and (max-width: 767px) {
            #iframe-container {
                padding: 12px;
            }
        }

        /* Demo Not Available Styles */
        .demo-not-available {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 56px 32px;
            flex: 1;
            border-radius: 16px;
            background: var(--sp-surface-soft);
            border: 1px solid var(--sp-hairline);
        }

        .demo-icon {
            color: var(--sp-muted);
            margin-bottom: 1.5rem;
            opacity: 0.7;
        }

        .demo-not-available h3 {
            font-family: "Cormorant Garamond", "Times New Roman", serif;
            font-size: clamp(1.55rem, 3.4vw, 2.2rem);
            font-weight: 500;
            letter-spacing: -0.03em;
            margin-bottom: 1rem;
            color: var(--sp-ink);
        }

        .demo-not-available p {
            font-size: 1rem;
            color: var(--sp-muted);
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
            justify-content: center;
            min-height: 44px;
            padding: 12px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
            white-space: nowrap;
            border: 1px solid transparent;
        }

        .btn-whatsapp-demo {
            background: var(--sp-primary);
            color: var(--sp-on-primary);
        }

        .btn-whatsapp-demo:hover {
            background: var(--sp-primary-active);
            color: var(--sp-on-primary);
        }

        .btn-back-demo {
            background: var(--sp-canvas);
            border-color: var(--sp-hairline);
            color: var(--sp-ink);
        }

        .btn-back-demo:hover {
            background: var(--sp-surface-card);
            color: var(--sp-ink);
        }

        @media (max-width: 767px) {
            .demo-not-available {
                padding: 40px 20px;
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
            <a class="preview-btn preview-btn-back" href="<?php echo get_the_permalink($portofolio_page); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z" />
                </svg>
                Kembali
            </a>
            <div class="preview-header-copy">
                <span class="preview-kicker">Live Preview</span>
                <h1 class="preview-title"><?php echo esc_html($data_title); ?></h1>
                <p class="preview-subtitle"><?php echo esc_html(get_bloginfo('name')); ?> menampilkan preview portfolio dalam tampilan editorial yang lebih tenang dan fokus.</p>
            </div>
            <div class="header-preview-actions">
                <?php
                $whatsapp_option = get_option('portofolio_whatsapp_number'); // Prefix added to option name
                // Mengganti "08" dengan "628" dan menghapus karakter selain angka
                $whatsapp_number = preg_replace('/[^0-9]/', '', $whatsapp_option);
                $whatsapp_number = preg_replace('/^0/', '62', $whatsapp_number);

                if (!empty($whatsapp_number)) {
                    $whatsapp_message = "Saya tertarik dengan " . urlencode($data_title);
                    $whatsapp_url = "https://wa.me/$whatsapp_number?text=" . urlencode($whatsapp_message);
                ?>
                    <a target="_blank" href="<?php echo esc_url($whatsapp_url); ?>" class="preview-btn preview-btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                            <path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z" />
                        </svg>
                        Order Langsung
                    </a>
                <?php } ?>
            </div>
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
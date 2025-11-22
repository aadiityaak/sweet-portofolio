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
function portofolio_whatsapp_settings_page()
{
    add_menu_page(
        'Portofolio Option',
        'Portofolio Option',
        'manage_options',
        'portofolio-settings', // Prefix added to page slug
        'portofolio_settings_page_content',
        'dashicons-admin-settings',
        30
    );
}
add_action('admin_menu', 'portofolio_whatsapp_settings_page');

function portofolio_settings_page_content()
{
    $access_key = get_option('portofolio_access_key');
    $portfolioSelection = (array) get_option('portofolio_selection', []);

    // Add custom CSS for layout
    echo '<style>
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }
        .col-8 {
            flex: 0 0 66.666667%;
            max-width: 66.666667%;
            padding-right: 15px;
            padding-left: 15px;
        }
        .col-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
            padding-right: 15px;
            padding-left: 15px;
        }
        .align-middle {
            align-items: center;
        }
        .portfolio-page-container select,
        .preview-page-container select {
            width: 100%;
        }
    </style>';

    // Check if access key is valid
    $access_key_valid = true;
    $access_key_message = '';

    if (!empty($access_key)) {
        // Clear any existing error data in transients first
        delete_transient('web_data_transient');

        // Test the access key with a simple API call
        $test_url = 'https://my.websweetstudio.com/wp-json/wp/v2/portofolio?access_key=' . $access_key . '&per_page=1';
        $test_response = wp_remote_get($test_url);

        if (is_wp_error($test_response)) {
            $access_key_valid = false;
            $access_key_message = 'Error connecting to API: ' . $test_response->get_error_message();
        } else {
            $test_body = wp_remote_retrieve_body($test_response);
            $test_data = json_decode($test_body, true);

            if (isset($test_data['code']) && $test_data['code'] === 'rest_forbidden') {
                $access_key_valid = false;
                $access_key_message = 'Access key is invalid or expired';
            } elseif (isset($test_data['code'])) {
                $access_key_valid = false;
                $access_key_message = 'API Error: ' . ($test_data['message'] ?? 'Unknown error');
            }
        }
    }

    // Cek apakah data sudah ada dalam sesi
    $data = get_transient('jenis_web_data');

    if (!$data) {
        if (!empty($access_key) && $access_key_valid) {
            $api_url = 'https://my.websweetstudio.com/wp-json/wp/v2/jenis-web?access_key=' . $access_key;
            $response = wp_remote_get($api_url);

            if (is_wp_error($response)) {
                $data = [];
            } else {
                $body = wp_remote_retrieve_body($response);
                $data = json_decode($body, true);
            }

            // Simpan data dalam transient selama 1 jam (3600 detik)
            $transient_key = 'jenis_web_data';
            $transient_set = set_transient($transient_key, $data, 12 * 3600);
        } else {
            $data = [];
        }
    }
?>
    <div class="wrap">
        <h2>Portofolio WhatsApp Settings</h2>

        <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true'): ?>
            <div id="message" class="updated notice is-dismissible">
                <p>Settings saved successfully.</p>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['cache-cleared']) && $_GET['cache-cleared'] == 'true'): ?>
            <div id="message" class="updated notice is-dismissible">
                <p>Cache cleared successfully.</p>
            </div>
        <?php endif; ?>

        <p>
            <a href="<?php echo admin_url('admin.php?page=portofolio-settings&cache-cleared=true'); ?>" class="button">Clear Cache</a>
            <a href="<?php echo admin_url('admin.php?page=portofolio-settings&refresh-data=true'); ?>" class="button button-primary">Refresh Portfolio Data</a>
            <a href="<?php echo admin_url('admin.php?page=portofolio-settings&generate-pages=true'); ?>" class="button button-secondary">Generate Pages</a>
            <?php
            if (isset($_GET['cache-cleared']) && $_GET['cache-cleared'] == 'true') {
                delete_transient('web_data_transient');
                delete_transient('jenis_web_data');
                echo '<script>window.location.href = "' . admin_url('admin.php?page=portofolio-settings&cache-cleared-redirect=true') . '";</script>';
            }

            if (isset($_GET['refresh-data']) && $_GET['refresh-data'] == 'true') {
                delete_transient('web_data_transient');
                delete_transient('jenis_web_data');
                echo '<script>window.location.href = "' . admin_url('admin.php?page=portofolio-settings&data-refreshed=true') . '";</script>';
            }

            if (isset($_GET['generate-pages']) && $_GET['generate-pages'] == 'true') {
                portofolio_generate_pages();
                echo '<script>window.location.href = "' . admin_url('admin.php?page=portofolio-settings&pages-generated=true') . '";</script>';
            }

            if (isset($_GET['cache-cleared-redirect']) && $_GET['cache-cleared-redirect'] == 'true') {
                echo '<div id="message" class="updated notice is-dismissible"><p>Cache cleared successfully.</p></div>';
            }

            if (isset($_GET['data-refreshed']) && $_GET['data-refreshed'] == 'true') {
                echo '<div id="message" class="updated notice is-dismissible"><p>Portfolio data refreshed successfully.</p></div>';
            }

            if (isset($_GET['pages-generated']) && $_GET['pages-generated'] == 'true') {
                $messages = get_transient('portofolio_generate_messages');
                if ($messages && is_array($messages)) {
                    foreach ($messages as $message) {
                        echo '<div id="message" class="updated notice is-dismissible"><p>' . esc_html($message) . '</p></div>';
                    }
                    delete_transient('portofolio_generate_messages');
                } else {
                    echo '<div id="message" class="updated notice is-dismissible"><p>Pages generated successfully. Portofolio and Preview pages have been created with the correct templates.</p></div>';
                }
            }
            ?>
        </p>

        <form method="post" action="options.php">
            <?php settings_fields('portofolio-whatsapp-settings-group'); // Prefix added to settings group 
            ?>
            <?php do_settings_sections('portofolio-whatsapp-settings-group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">WhatsApp Number</th>
                    <td><input type="text" name="portofolio_whatsapp_number" value="<?php echo esc_attr(get_option('portofolio_whatsapp_number')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Access Key</th>
                    <td>
                        <input type="text" name="portofolio_access_key" value="<?php echo esc_attr(get_option('portofolio_access_key')); ?>" />
                        <?php if (!empty($access_key)): ?>
                            <?php if ($access_key_valid): ?>
                                <p class="description" style="color: green;">✓ Access key is valid</p>
                            <?php else: ?>
                                <p class="description" style="color: red;">✗ <?php echo esc_html($access_key_message); ?></p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="description">Enter your access key from my.websweetstudio.com</p>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Credit Text</th>
                    <td><input type="text" name="portofolio_credit" value="<?php echo esc_attr(get_option('portofolio_credit')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Image Size</th>
                    <td>
                        <select name="portofolio_image_size">
                            <option value="thumbnail" <?php selected(get_option('portofolio_image_size'), 'thumbnail'); ?>>Thumbnail 400</option>
                            <option value="medium" <?php selected(get_option('portofolio_image_size'), 'medium'); ?>>Medium 700</option>
                            <option value="large" <?php selected(get_option('portofolio_image_size'), 'large'); ?>>Large 1000</option>
                            <option value="full" <?php selected(get_option('portofolio_image_size'), 'full'); ?>>Full 1080</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Style Thumbnail</th>
                    <td>
                        <select name="portofolio_style_thumbnail">
                            <option value="thumbnail" <?php selected(get_option('portofolio_style_thumbnail'), 'thumbnail'); ?>>Standart</option>
                            <option value="screenshot" <?php selected(get_option('portofolio_style_thumbnail'), 'screenshot'); ?>>Screenshot</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Portofolio Page</th>
                    <td>
                        <div x-data="portfolioPageGenerator()" class="portfolio-page-container">
                            <div class="row align-middle">
                                <div class="col-8">
                                    <?php
                                    $selected_page = esc_attr(get_option('portofolio_page'));
                                    wp_dropdown_pages(array(
                                        'name' => 'portofolio_page',
                                        'id' => 'portofolio_page_select',
                                        'show_option_none' => '-- Select a Page --',
                                        'option_none_value' => '-1',
                                        'selected' => $selected_page,
                                    ));
                                    ?>
                                </div>
                                <div class="col-4">
                                    <button
                                        @click="generatePortfolioPage()"
                                        :disabled="generating"
                                        class="button button-secondary"
                                        x-text="generating ? 'Generating...' : 'Generate Page'">
                                    </button>
                                    <button
                                        @click="generatePortfolioPage(true)"
                                        :disabled="generating"
                                        class="button button-primary"
                                        style="margin-left: 5px;"
                                        x-text="generating ? 'Generating...' : 'Force Generate'">
                                    </button>
                                </div>
                            </div>

                            <div x-show="message" x-transition class="notice" :class="'notice-' + messageType" style="margin-top: 10px;">
                                <p x-text="message"></p>
                            </div>

                            <div style="margin-top: 10px;">
                                <span>
                                    <strong>Generate Page:</strong> Membuat halaman baru jika belum ada.<br>
                                    <strong>Force Generate:</strong> Menimpa halaman yang sudah ada dengan konten baru.<br><br>
                                    Pastikan sudah memasukkan shortcode di bawah ini pada page yang dipilih:<br>
                                    [sweet-portofolio-jenis-web] Digunakan untuk menampilkan tombol filter berdasarkan jenis web.<br>
                                    [sweet-portofolio-list default="profil-perusahaan"] Digunakan untuk menampilkan list thumbnail portofolio.<br>
                                    [sweet-portofolio-list include="1982,1670" title="no"] Digunakan untuk menampilkan portofolio berdasarkan id nya
                                </span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Preview Page</th>
                    <td>
                        <div x-data="previewPageGenerator()" class="preview-page-container">
                            <div class="row align-middle">
                                <div class="col-8">
                                    <?php
                                    $selected_page = esc_attr(get_option('portofolio_preview_page'));
                                    wp_dropdown_pages(array(
                                        'name' => 'portofolio_preview_page',
                                        'id' => 'portofolio_preview_page_select',
                                        'show_option_none' => '-- Select a Page --',
                                        'option_none_value' => '-1',
                                        'selected' => $selected_page,
                                    ));
                                    ?>
                                </div>
                                <div class="col-4">
                                    <button
                                        @click="generatePreviewPage()"
                                        :disabled="generating"
                                        class="button button-secondary"
                                        x-text="generating ? 'Generating...' : 'Generate Page'">
                                    </button>
                                    <button
                                        @click="generatePreviewPage(true)"
                                        :disabled="generating"
                                        class="button button-primary"
                                        style="margin-left: 5px;"
                                        x-text="generating ? 'Generating...' : 'Force Generate'">
                                    </button>
                                </div>
                            </div>

                            <div x-show="message" x-transition class="notice" :class="'notice-' + messageType" style="margin-top: 10px;">
                                <p x-text="message"></p>
                            </div>

                            <div style="margin-top: 10px;">
                                <span>
                                    <strong>Generate Page:</strong> Membuat halaman baru jika belum ada.<br>
                                    <strong>Force Generate:</strong> Menimpa halaman yang sudah ada dengan konten baru.<br><br>
                                    Pastikan sudah merubah page template menjadi 'Preview Portofolio' pada page yang dipilih.
                                </span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Portfolio Selection</th>
                    <td>
                        <?php foreach ($data as $portfolio) : ?>
                            <label>
                                <input type="checkbox" name="portofolio_selection[]" value="<?php echo esc_attr($portfolio['slug']); ?>" <?php checked(is_array($portfolioSelection) && in_array($portfolio['slug'], $portfolioSelection)); ?>>
                                <?php echo esc_html($portfolio['category']); ?>
                            </label><br>
                        <?php endforeach; ?>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>

    <script>
        // Make sure Alpine.js is loaded before initializing components
        document.addEventListener('DOMContentLoaded', () => {
            console.log('DOM loaded');
            console.log('wpApiSettings:', window.wpApiSettings);

            // Check if Alpine.js is loaded
            if (typeof Alpine === 'undefined') {
                console.error('Alpine.js is not loaded');
                return;
            }

            console.log('Alpine.js is loaded');
            // Don't call Alpine.start() here as it's already initialized by the CDN script
            // Alpine.start(); // This line is causing the warning
        });

        document.addEventListener('alpine:init', () => {
            console.log('Alpine.js initializing');

            // Function to generate portfolio page
            Alpine.data('portfolioPageGenerator', () => ({
                generating: false,
                message: '',
                messageType: '',

                async generatePortfolioPage(force = false) {
                    console.log('generatePortfolioPage called with force:', force);
                    console.log('wpApiSettings.nonce:', wpApiSettings.nonce);

                    this.generating = true;
                    this.message = '';

                    try {
                        const apiUrl = '<?php echo rest_url('sweet-portofolio/v1/generate-portfolio-page'); ?>';
                        console.log('Making request to:', apiUrl);

                        const response = await fetch(apiUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-WP-Nonce': wpApiSettings.nonce
                            },
                            body: JSON.stringify({
                                force: force
                            })
                        });

                        console.log('Response status:', response.status);
                        console.log('Response ok:', response.ok);

                        const data = await response.json();
                        console.log('Response data:', data);

                        if (response.ok) {
                            this.message = data.message || 'Portfolio page created successfully!';
                            this.messageType = 'success';
                            console.log('Success message:', this.message);

                            // Update the select dropdown
                            if (data.page_id) {
                                console.log('Updating select dropdown with page_id:', data.page_id);
                                const select = document.getElementById('portofolio_page_select');
                                if (select) {
                                    console.log('Select element found with', select.options.length, 'options');
                                    // Log all current options
                                    for (let i = 0; i < select.options.length; i++) {
                                        console.log('Option', i, ': value=', select.options[i].value, ', text=', select.options[i].text);
                                    }

                                    // Add new option if not exists
                                    let optionExists = false;
                                    for (let i = 0; i < select.options.length; i++) {
                                        if (select.options[i].value == data.page_id) {
                                            optionExists = true;
                                            select.selectedIndex = i;
                                            console.log('Found existing option at index', i);
                                            break;
                                        }
                                    }

                                    if (!optionExists) {
                                        console.log('Adding new option to select');
                                        const newOption = document.createElement('option');
                                        newOption.value = data.page_id;
                                        newOption.text = data.page_title || 'Portofolio';
                                        newOption.selected = true;
                                        select.appendChild(newOption);
                                        console.log('New option added and selected');

                                        // Trigger change event to notify WordPress
                                        const changeEvent = new Event('change', {
                                            bubbles: true
                                        });
                                        select.dispatchEvent(changeEvent);
                                        console.log('Change event dispatched');
                                    }
                                } else {
                                    console.error('Select element not found');
                                }
                            }
                        } else {
                            if (data.code === 'page_exists') {
                                this.message = data.message + ' Use the "Force Generate" button to overwrite the existing page.';
                            } else {
                                this.message = data.message || 'Error creating portfolio page.';
                            }
                            this.messageType = 'error';
                            console.error('Error message:', this.message);
                        }
                    } catch (error) {
                        console.error('Network error:', error);
                        this.message = 'Network error: ' + error.message;
                        this.messageType = 'error';
                    } finally {
                        this.generating = false;
                        console.log('Generation process completed');

                        // Clear message after 5 seconds
                        setTimeout(() => {
                            this.message = '';
                        }, 5000);
                    }
                }
            }));

            // Function to generate preview page
            Alpine.data('previewPageGenerator', () => ({
                generating: false,
                message: '',
                messageType: '',

                async generatePreviewPage(force = false) {
                    console.log('generatePreviewPage called with force:', force);
                    console.log('wpApiSettings.nonce:', wpApiSettings.nonce);

                    this.generating = true;
                    this.message = '';

                    try {
                        const apiUrl = '<?php echo rest_url('sweet-portofolio/v1/generate-preview-page'); ?>';
                        console.log('Making request to:', apiUrl);

                        const response = await fetch(apiUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-WP-Nonce': wpApiSettings.nonce
                            },
                            body: JSON.stringify({
                                force: force
                            })
                        });

                        console.log('Response status:', response.status);
                        console.log('Response ok:', response.ok);

                        const data = await response.json();
                        console.log('Response data:', data);

                        if (response.ok) {
                            this.message = data.message || 'Preview page created successfully!';
                            this.messageType = 'success';

                            // Update the select dropdown
                            if (data.page_id) {
                                console.log('Updating preview select dropdown with page_id:', data.page_id);
                                const select = document.getElementById('portofolio_preview_page_select');
                                if (select) {
                                    console.log('Preview select element found with', select.options.length, 'options');
                                    // Log all current options
                                    for (let i = 0; i < select.options.length; i++) {
                                        console.log('Preview Option', i, ': value=', select.options[i].value, ', text=', select.options[i].text);
                                    }

                                    // Add new option if not exists
                                    let optionExists = false;
                                    for (let i = 0; i < select.options.length; i++) {
                                        if (select.options[i].value == data.page_id) {
                                            optionExists = true;
                                            select.selectedIndex = i;
                                            console.log('Found existing preview option at index', i);
                                            break;
                                        }
                                    }

                                    if (!optionExists) {
                                        console.log('Adding new preview option to select');
                                        const newOption = document.createElement('option');
                                        newOption.value = data.page_id;
                                        newOption.text = data.page_title || 'Preview Portofolio';
                                        newOption.selected = true;
                                        select.appendChild(newOption);
                                        console.log('New preview option added and selected');

                                        // Trigger change event to notify WordPress
                                        const changeEvent = new Event('change', {
                                            bubbles: true
                                        });
                                        select.dispatchEvent(changeEvent);
                                        console.log('Preview change event dispatched');
                                    }
                                } else {
                                    console.error('Preview select element not found');
                                }
                            }
                        } else {
                            if (data.code === 'page_exists') {
                                this.message = data.message + ' Use the "Force Generate" button to overwrite the existing page.';
                            } else {
                                this.message = data.message || 'Error creating preview page.';
                            }
                            this.messageType = 'error';
                        }
                    } catch (error) {
                        this.message = 'Network error: ' + error.message;
                        this.messageType = 'error';
                    } finally {
                        this.generating = false;

                        // Clear message after 5 seconds
                        setTimeout(() => {
                            this.message = '';
                        }, 5000);
                    }
                }
            }));
        });

        // No need to manually initialize components as they're already initialized by Alpine.js
    </script>
<?php
}

function portofolio_register_whatsapp_settings()
{
    register_setting('portofolio-whatsapp-settings-group', 'portofolio_whatsapp_number'); // Prefix added to setting name
    register_setting('portofolio-whatsapp-settings-group', 'portofolio_access_key', 'portofolio_validate_access_key'); // Prefix added to setting name
    register_setting('portofolio-whatsapp-settings-group', 'portofolio_credit');
    register_setting('portofolio-whatsapp-settings-group', 'portofolio_image_size'); // Register the new setting for image size
    register_setting('portofolio-whatsapp-settings-group', 'portofolio_page');
    register_setting('portofolio-whatsapp-settings-group', 'portofolio_preview_page');
    register_setting('portofolio-whatsapp-settings-group', 'portofolio_style_thumbnail');
    register_setting('portofolio-whatsapp-settings-group', 'portofolio_selection');
}

function portofolio_validate_access_key($input)
{
    $old_value = get_option('portofolio_access_key');

    // If access key has changed, clear transients
    if ($old_value !== $input) {
        delete_transient('web_data_transient');
        delete_transient('jenis_web_data');
    }

    return $input;
}

function portofolio_generate_pages()
{
    $messages = array();

    // Check if portfolio page already exists
    $portfolio_page_id = get_option('portofolio_page');
    if (!$portfolio_page_id || $portfolio_page_id == '-1' || !get_post($portfolio_page_id)) {
        // Create portfolio page
        $portfolio_page = array(
            'post_title'    => 'Portofolio',
            'post_content'  => '[sweet-portofolio-jenis-web]' . "\n\n" . '[sweet-portofolio-list default="profil-perusahaan"]',
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type'     => 'page',
            'post_name'     => 'portofolio'
        );

        $portfolio_page_id = wp_insert_post($portfolio_page);

        if ($portfolio_page_id && !is_wp_error($portfolio_page_id)) {
            // Save the page ID to options
            update_option('portofolio_page', $portfolio_page_id);
            $messages[] = "Portfolio page created successfully.";
        } else {
            $messages[] = "Error creating portfolio page.";
        }
    } else {
        $messages[] = "Portfolio page already exists.";
    }

    // Check if preview page already exists
    $preview_page_id = get_option('portofolio_preview_page');
    if (!$preview_page_id || $preview_page_id == '-1' || !get_post($preview_page_id)) {
        // Create preview page
        $preview_page = array(
            'post_title'    => 'Preview Portofolio',
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type'     => 'page',
            'post_name'     => 'preview-portofolio'
        );

        $preview_page_id = wp_insert_post($preview_page);

        if ($preview_page_id && !is_wp_error($preview_page_id)) {
            // Save the page ID to options
            update_option('portofolio_preview_page', $preview_page_id);

            // Set the page template to 'Preview Portofolio'
            update_post_meta($preview_page_id, '_wp_page_template', 'page-preview.php');
            $messages[] = "Preview page created successfully with correct template.";
        } else {
            $messages[] = "Error creating preview page.";
        }
    } else {
        $messages[] = "Preview page already exists.";
    }

    // Store messages in transient to display after redirect
    set_transient('portofolio_generate_messages', $messages, 60);
}



add_action('admin_init', 'portofolio_register_whatsapp_settings');

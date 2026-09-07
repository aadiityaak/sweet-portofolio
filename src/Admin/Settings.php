<?php

namespace SweetPortofolio\Admin;

class Settings
{
    private const SETTINGS_GROUP = 'portofolio-whatsapp-settings-group';

    public function register()
    {
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function register_settings()
    {
        register_setting(self::SETTINGS_GROUP, 'portofolio_whatsapp_number', [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitize_text'],
            'default' => '',
        ]);

        register_setting(self::SETTINGS_GROUP, 'portofolio_access_key', [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitize_text'],
            'default' => '',
        ]);

        register_setting(self::SETTINGS_GROUP, 'portofolio_credit', [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitize_text'],
            'default' => '',
        ]);

        register_setting(self::SETTINGS_GROUP, 'portofolio_image_size', [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitize_text'],
            'default' => 'thumbnail',
        ]);

        register_setting(self::SETTINGS_GROUP, 'portofolio_style_thumbnail', [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitize_text'],
            'default' => 'thumbnail',
        ]);

        register_setting(self::SETTINGS_GROUP, 'portofolio_page', [
            'type' => 'integer',
            'sanitize_callback' => [$this, 'sanitize_page_id'],
            'default' => -1,
        ]);

        register_setting(self::SETTINGS_GROUP, 'portofolio_preview_page', [
            'type' => 'integer',
            'sanitize_callback' => [$this, 'sanitize_page_id'],
            'default' => -1,
        ]);

        register_setting(self::SETTINGS_GROUP, 'portofolio_selection', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_selection'],
            'default' => [],
        ]);

        // WSCRM API settings
        register_setting(self::SETTINGS_GROUP, 'portofolio_api_source', [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitize_text'],
            'default' => 'wscrm',
        ]);

        register_setting(self::SETTINGS_GROUP, 'portofolio_wscrm_api_url', [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitize_url'],
            'default' => 'https://app.websweetstudio.com',
        ]);
    }

    public function sanitize_text($value)
    {
        return sanitize_text_field((string) $value);
    }

    public function sanitize_page_id($value)
    {
        $page_id = absint($value);

        return $page_id > 0 ? $page_id : -1;
    }

    public function sanitize_selection($value)
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter(array_map('sanitize_text_field', $value)));
    }

    public function sanitize_url($value)
    {
        $value = rtrim(esc_url_raw($value), '/');
        // Flush wscrm cache on URL change
        if (class_exists('\SweetPortofolio\Api\WscrmClient')) {
            (new \SweetPortofolio\Api\WscrmClient())->flushCache();
        }
        return $value;
    }

    public function enqueue_admin_assets($hook)
    {
        if ($hook !== 'toplevel_page_portofolio-settings') {
            return;
        }

        wp_enqueue_style(
            'sweet-portofolio-admin',
            SWEETPORTOFOLIO_URL . 'assets/css/admin.css',
            [],
            SWEETPORTOFOLIO_VERSION
        );
    }

    public function add_menu_page()
    {
        add_menu_page(
            'Portofolio Option',
            'Portofolio Option',
            'manage_options',
            'portofolio-settings',
            [$this, 'render_page'],
            'dashicons-admin-settings',
            30
        );
    }

    public function render_page()
    {
        $access_key = get_option('portofolio_access_key');
        $portfolioSelection = (array) get_option('portofolio_selection', []);

        // Logic from portofolio_settings_page_content
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

        // Check if data exists in session (transient)
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

                // Save data in transient for 1 hour (3600 seconds)
                $transient_key = 'jenis_web_data';
                set_transient($transient_key, $data, 12 * 3600);
            } else {
                $data = [];
            }
        }

        // Add modern CSS for layout
        // Styles are now enqueued from assets/css/admin.css
?>
        <div class="wrap sweet-portofolio-settings">
            <div class="sweet-portofolio-header">
                <h1>Sweet Portofolio Settings</h1>
                <p>Kelola portofolio website Anda dengan mudah dan modern</p>
            </div>

            <?php if (isset($_GET['settings-updated']) && $_GET['settings-updated'] == 'true'): ?>
                <div class="sweet-portofolio-notice sweet-portofolio-notice-success">
                    <p>✓ Settings saved successfully.</p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['cache-cleared']) && $_GET['cache-cleared'] == 'true'): ?>
                <div class="sweet-portofolio-notice sweet-portofolio-notice-success">
                    <p>✓ Cache cleared successfully.</p>
                </div>
            <?php endif; ?>

            <div class="sweet-portofolio-actions">
                <a href="<?php echo admin_url('admin.php?page=portofolio-settings&cache-cleared=true'); ?>" class="sweet-portofolio-button sweet-portofolio-button-white">Clear Cache</a>
                <a href="<?php echo admin_url('admin.php?page=portofolio-settings&refresh-data=true'); ?>" class="sweet-portofolio-button sweet-portofolio-button-primary">Refresh Portfolio Data</a>
                <a href="<?php echo admin_url('admin.php?page=portofolio-settings&generate-pages=true'); ?>" class="sweet-portofolio-button sweet-portofolio-button-white">Generate Pages</a>
            </div>

            <div class="sweet-portofolio-notices-area">
                <?php
                if (isset($_GET['cache-cleared']) && $_GET['cache-cleared'] == 'true') {
                    delete_transient('web_data_transient');
                    delete_transient('jenis_web_data');
                    if (class_exists('\SweetPortofolio\Api\WscrmClient')) {
                        (new \SweetPortofolio\Api\WscrmClient())->flushCache();
                    }
                    echo '<script>window.location.href = "' . admin_url('admin.php?page=portofolio-settings&cache-cleared-redirect=true') . '";</script>';
                }

                if (isset($_GET['refresh-data']) && $_GET['refresh-data'] == 'true') {
                    delete_transient('web_data_transient');
                    delete_transient('jenis_web_data');
                    if (class_exists('\SweetPortofolio\Api\WscrmClient')) {
                        (new \SweetPortofolio\Api\WscrmClient())->flushCache();
                    }
                    echo '<script>window.location.href = "' . admin_url('admin.php?page=portofolio-settings&data-refreshed=true') . '";</script>';
                }

                if (isset($_GET['generate-pages']) && $_GET['generate-pages'] == 'true') {
                    $this->portofolio_generate_pages();
                    echo '<script>window.location.href = "' . admin_url('admin.php?page=portofolio-settings&pages-generated=true') . '";</script>';
                }

                if (isset($_GET['cache-cleared-redirect']) && $_GET['cache-cleared-redirect'] == 'true') {
                    echo '<div class="sweet-portofolio-notice sweet-portofolio-notice-success"><p>✓ Cache cleared successfully.</p></div>';
                }

                if (isset($_GET['data-refreshed']) && $_GET['data-refreshed'] == 'true') {
                    echo '<div class="sweet-portofolio-notice sweet-portofolio-notice-success"><p>✓ Portfolio data refreshed successfully.</p></div>';
                }

                if (isset($_GET['pages-generated']) && $_GET['pages-generated'] == 'true') {
                    $messages = get_transient('portofolio_generate_messages');
                    if ($messages && is_array($messages)) {
                        foreach ($messages as $message) {
                            echo '<div class="sweet-portofolio-notice sweet-portofolio-notice-success"><p>✓ ' . esc_html($message) . '</p></div>';
                        }
                        delete_transient('portofolio_generate_messages');
                    } else {
                        echo '<div class="sweet-portofolio-notice sweet-portofolio-notice-success"><p>✓ Pages generated successfully. Portofolio and Preview pages have been created with the correct templates.</p></div>';
                    }
                }
                ?>
            </div>

            <form method="post" action="options.php">
                <?php settings_fields(self::SETTINGS_GROUP); ?>
                <?php do_settings_sections(self::SETTINGS_GROUP); ?>

                <div class="sweet-portofolio-card">
                    <h2 class="sweet-portofolio-card-title">Basic Settings</h2>

                    <div class="sweet-portofolio-form-row">
                        <div class="sweet-portofolio-form-col">
                            <label for="portofolio_whatsapp_number" class="sweet-portofolio-label">WhatsApp Number</label>
                            <input type="text" id="portofolio_whatsapp_number" name="portofolio_whatsapp_number" value="<?php echo esc_attr(get_option('portofolio_whatsapp_number')); ?>" class="sweet-portofolio-input" />
                        </div>
                    </div>

                    <div class="sweet-portofolio-form-row">
                        <div class="sweet-portofolio-form-col">
                            <label for="portofolio_access_key" class="sweet-portofolio-label">Access Key</label>
                            <input type="text" id="portofolio_access_key" name="portofolio_access_key" value="<?php echo esc_attr(get_option('portofolio_access_key')); ?>" class="sweet-portofolio-input" />
                            <?php if (!empty($access_key)): ?>
                                <?php if ($access_key_valid): ?>
                                    <div class="sweet-portofolio-status sweet-portofolio-status-success">✓ Access key is valid</div>
                                <?php else: ?>
                                    <div class="sweet-portofolio-status sweet-portofolio-status-error">✗ <?php echo esc_html($access_key_message); ?></div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="sweet-portofolio-help-text">Enter your access key from my.websweetstudio.com</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="sweet-portofolio-form-row">
                        <div class="sweet-portofolio-form-col">
                            <label for="portofolio_credit" class="sweet-portofolio-label">Credit Text</label>
                            <input type="text" id="portofolio_credit" name="portofolio_credit" value="<?php echo esc_attr(get_option('portofolio_credit')); ?>" class="sweet-portofolio-input" />
                        </div>
                    </div>
                </div>

                <div class="sweet-portofolio-card">
                    <h2 class="sweet-portofolio-card-title">Display Settings</h2>

                    <div class="sweet-portofolio-form-row">
                        <div class="sweet-portofolio-form-col">
                            <label for="portofolio_image_size" class="sweet-portofolio-label">Image Size</label>
                            <select id="portofolio_image_size" name="portofolio_image_size" class="sweet-portofolio-select">
                                <option value="thumbnail" <?php selected(get_option('portofolio_image_size'), 'thumbnail'); ?>>Thumbnail 400</option>
                                <option value="medium" <?php selected(get_option('portofolio_image_size'), 'medium'); ?>>Medium 700</option>
                                <option value="large" <?php selected(get_option('portofolio_image_size'), 'large'); ?>>Large 1000</option>
                                <option value="full" <?php selected(get_option('portofolio_image_size'), 'full'); ?>>Full 1080</option>
                            </select>
                        </div>

                        <div class="sweet-portofolio-form-col">
                            <label for="portofolio_style_thumbnail" class="sweet-portofolio-label">Style Thumbnail</label>
                            <select id="portofolio_style_thumbnail" name="portofolio_style_thumbnail" class="sweet-portofolio-select">
                                <option value="thumbnail" <?php selected(get_option('portofolio_style_thumbnail'), 'thumbnail'); ?>>Standart</option>
                                <option value="screenshot" <?php selected(get_option('portofolio_style_thumbnail'), 'screenshot'); ?>>Screenshot</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="sweet-portofolio-card">
                    <h2 class="sweet-portofolio-card-title">Page Management</h2>

                    <div class="sweet-portofolio-form-section">
                        <h3 class="sweet-portofolio-section-title">Portofolio Page</h3>
                        <div id="portfolio-page-generator" class="portfolio-page-container">
                            <div class="sweet-portofolio-form-row">
                                <div class="sweet-portofolio-form-col">
                                    <label for="portofolio_page_select" class="sweet-portofolio-label">Select Page</label>
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
                            </div>

                            <div class="sweet-portofolio-form-row">
                                <div class="sweet-portofolio-form-col">
                                    <div class="sweet-portofolio-button-group">
                                        <button
                                            id="btn-generate-portfolio-page"
                                            type="button"
                                            class="sweet-portofolio-button sweet-portofolio-button-primary">
                                            Generate Page
                                        </button>
                                        <button
                                            id="btn-view-portfolio-page"
                                            type="button"
                                            class="sweet-portofolio-button sweet-portofolio-button-secondary">
                                            View Page
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div id="portfolio-page-message" class="sweet-portofolio-notice" style="display:none">
                                <p></p>
                            </div>

                            <div class="sweet-portofolio-help-text">
                                <strong>Generate Page:</strong> Membuat halaman portofolio dengan template yang sudah ditentukan.<br><br>
                                Halaman akan menggunakan template khusus tanpa perlu menambahkan shortcode secara manual.
                            </div>
                        </div>
                    </div>

                    <div class="sweet-portofolio-form-section">
                        <h3 class="sweet-portofolio-section-title">Preview Page</h3>
                        <div id="preview-page-generator" class="preview-page-container">
                            <div class="sweet-portofolio-form-row">
                                <div class="sweet-portofolio-form-col">
                                    <label for="portofolio_preview_page_select" class="sweet-portofolio-label">Select Page</label>
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
                            </div>

                            <div class="sweet-portofolio-form-row">
                                <div class="sweet-portofolio-form-col">
                                    <div class="sweet-portofolio-button-group">
                                        <button
                                            id="btn-generate-preview-page"
                                            type="button"
                                            class="sweet-portofolio-button sweet-portofolio-button-primary">
                                            Generate Preview Page
                                        </button>
                                        <button
                                            id="btn-view-preview-page"
                                            type="button"
                                            class="sweet-portofolio-button sweet-portofolio-button-secondary">
                                            View Page
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div id="preview-page-message" class="sweet-portofolio-notice" style="display:none">
                                <p></p>
                            </div>

                            <div class="sweet-portofolio-help-text">
                                <strong>Generate Preview Page:</strong> Membuat halaman preview portofolio dengan template yang sudah ditentukan.<br><br>
                                Halaman akan menggunakan template khusus tanpa perlu menambahkan shortcode secara manual.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sweet-portofolio-card">
                    <h2 class="sweet-portofolio-card-title">Portfolio Selection</h2>

                    <div class="sweet-portofolio-form-row">
                        <div class="sweet-portofolio-form-col">
                            <div class="sweet-portofolio-checkbox-group">
                                <?php foreach ($data as $portfolio) : ?>
                                    <label class="sweet-portofolio-checkbox-label">
                                        <input type="checkbox" name="portofolio_selection[]" value="<?php echo esc_attr($portfolio['slug']); ?>" <?php checked(is_array($portfolioSelection) && in_array($portfolio['slug'], $portfolioSelection)); ?> class="sweet-portofolio-checkbox">
                                        <span class="sweet-portofolio-checkbox-text"><?php echo esc_html($portfolio['category']); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sweet-portofolio-form-actions"></div>

                <div class="sweet-portofolio-card">
                    <h2 class="sweet-portofolio-card-title">API Source</h2>
                    <p class="sweet-portofolio-help-text" style="margin-bottom: 16px">
                        Pilih sumber data portfolio. <strong>WSCRM API</strong> menggunakan REST API dari <code>app.websweetstudio.com</code> (public, no auth).
                        <strong>Legacy API</strong> menggunakan API lama dengan access key.
                    </p>
                    <div class="sweet-portofolio-form-row">
                        <div class="sweet-portofolio-form-col">
                            <label for="portofolio_api_source" class="sweet-portofolio-label">Sumber API</label>
                            <select name="portofolio_api_source" id="portofolio_api_source" class="sweet-portofolio-input">
                                <option value="wscrm" <?php selected(get_option('portofolio_api_source', 'wscrm'), 'wscrm'); ?>>WSCRM API (app.websweetstudio.com)</option>
                                <option value="legacy" <?php selected(get_option('portofolio_api_source', 'wscrm'), 'legacy'); ?>>Legacy API (my.websweetstudio.com)</option>
                            </select>
                        </div>
                    </div>
                    <div class="sweet-portofolio-form-row" id="wscrm-api-url-row" style="<?php echo get_option('portofolio_api_source', 'wscrm') === 'legacy' ? 'display:none' : ''; ?>">
                        <div class="sweet-portofolio-form-col">
                            <label for="portofolio_wscrm_api_url" class="sweet-portofolio-label">WSCRM API URL</label>
                            <input type="url" name="portofolio_wscrm_api_url" id="portofolio_wscrm_api_url"
                                   value="<?php echo esc_attr(get_option('portofolio_wscrm_api_url', 'https://app.websweetstudio.com')); ?>"
                                   class="sweet-portofolio-input" style="max-width: 500px" />
                            <p class="sweet-portofolio-help-text">Default: <code>https://app.websweetstudio.com</code>. Ubah jika self-hosted.</p>
                        </div>
                    </div>
                    <?php
                    // Show connection status for wscrm
                    if (get_option('portofolio_api_source', 'wscrm') === 'wscrm' && class_exists('\SweetPortofolio\Api\WscrmClient')) {
                        $wscrm = new \SweetPortofolio\Api\WscrmClient();
                        $wscrmData = $wscrm->fetchDemos();
                        if (isset($wscrmData['error'])) {
                            echo '<div class="sweet-portofolio-notice sweet-portofolio-notice-error" style="margin-top:12px"><p>WSCRM API Error: ' . esc_html($wscrmData['error']) . '</p></div>';
                        } else {
                            $demoCount = count($wscrmData['demos'] ?? []);
                            $catCount = count($wscrmData['categories'] ?? []);
                            echo '<div class="sweet-portofolio-notice sweet-portofolio-notice-success" style="margin-top:12px"><p>Connected! ' . $demoCount . ' demo, ' . $catCount . ' kategori tersedia.</p></div>';
                        }
                    }
                    ?>
                    <script>
                    document.getElementById('portofolio_api_source').addEventListener('change', function() {
                        document.getElementById('wscrm-api-url-row').style.display = this.value === 'legacy' ? 'none' : '';
                    });
                    </script>
                </div>

                <input type="submit" name="submit" id="submit" class="sweet-portofolio-submit" value="Save Changes">
                </div>
            </form>
        </div>

        <script>
        (function () {
            'use strict';

            function showMessage(boxId, text, type) {
                var box = document.getElementById(boxId);
                if (!box) return;
                var p = box.querySelector('p');
                if (p) p.textContent = text;
                box.classList.remove('sweet-portofolio-notice-success', 'sweet-portofolio-notice-error');
                if (type) box.classList.add('sweet-portofolio-notice-' + type);
                box.style.display = text ? '' : 'none';
                if (text) {
                    setTimeout(function () {
                        box.style.display = 'none';
                    }, 5000);
                }
            }

            function upsertPageOption(selectId, pageId, pageTitle) {
                var select = document.getElementById(selectId);
                if (!select || !pageId) return;
                var optionExists = false;
                for (var i = 0; i < select.options.length; i++) {
                    if (select.options[i].value == pageId) {
                        optionExists = true;
                        select.selectedIndex = i;
                        break;
                    }
                }
                if (!optionExists) {
                    var newOption = document.createElement('option');
                    newOption.value = pageId;
                    newOption.text = pageTitle;
                    newOption.selected = true;
                    select.appendChild(newOption);
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            async function generatePage(apiUrl, btn, messageBoxId, selectId, defaultTitle) {
                btn.disabled = true;
                var originalLabel = btn.textContent;
                btn.textContent = 'Generating...';
                showMessage(messageBoxId, '', '');

                try {
                    var response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': window.wpApiSettings.nonce
                        },
                        body: JSON.stringify({ force: true })
                    });

                    var data = await response.json();

                    if (response.ok) {
                        showMessage(messageBoxId, data.message || 'Page created successfully!', 'success');
                        if (data.page_id) {
                            upsertPageOption(selectId, data.page_id, data.page_title || defaultTitle);
                        }
                    } else if (data.code === 'page_exists') {
                        showMessage(messageBoxId, data.message + ' Use the "Force Generate" button to overwrite the existing page.', 'error');
                    } else {
                        showMessage(messageBoxId, data.message || 'Error creating page.', 'error');
                    }
                } catch (error) {
                    showMessage(messageBoxId, 'Network error: ' + error.message, 'error');
                } finally {
                    btn.disabled = false;
                    btn.textContent = originalLabel;
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                var portfolioBtn = document.getElementById('btn-generate-portfolio-page');
                if (portfolioBtn) {
                    portfolioBtn.addEventListener('click', function () {
                        generatePage(
                            '<?php echo rest_url('sweet-portofolio/v1/generate-portfolio-page'); ?>',
                            portfolioBtn,
                            'portfolio-page-message',
                            'portofolio_page_select',
                            'Portofolio'
                        );
                    });
                }

                var portfolioViewBtn = document.getElementById('btn-view-portfolio-page');
                if (portfolioViewBtn) {
                    portfolioViewBtn.addEventListener('click', function () {
                        window.open('<?php echo home_url('/portofolio'); ?>', '_blank');
                    });
                }

                var previewBtn = document.getElementById('btn-generate-preview-page');
                if (previewBtn) {
                    previewBtn.addEventListener('click', function () {
                        generatePage(
                            '<?php echo rest_url('sweet-portofolio/v1/generate-preview-page'); ?>',
                            previewBtn,
                            'preview-page-message',
                            'portofolio_preview_page_select',
                            'Preview Portofolio'
                        );
                    });
                }

                var previewViewBtn = document.getElementById('btn-view-preview-page');
                if (previewViewBtn) {
                    previewViewBtn.addEventListener('click', function () {
                        window.open('<?php echo home_url('/preview-portofolio'); ?>', '_blank');
                    });
                }
            });
        })();
        </script>
<?php
    }

    private function portofolio_generate_pages()
    {
        $messages = array();
        $portfolio_page_id = get_option('portofolio_page');

        if (!$portfolio_page_id || $portfolio_page_id == '-1' || !get_post($portfolio_page_id)) {
            $portfolio_page = array(
                'post_title'    => 'Portofolio',
                'post_content'  => '',
                'post_status'   => 'publish',
                'post_author'   => 1,
                'post_type'     => 'page',
                'post_name'     => 'portofolio'
            );

            $portfolio_page_id = wp_insert_post($portfolio_page);

            if ($portfolio_page_id && !is_wp_error($portfolio_page_id)) {
                update_option('portofolio_page', $portfolio_page_id);
                update_post_meta($portfolio_page_id, '_wp_page_template', 'page-portfolio-list.php');
                $messages[] = "Portfolio page created successfully.";
            }
        }

        set_transient('portofolio_generate_messages', $messages, 60);
    }
}

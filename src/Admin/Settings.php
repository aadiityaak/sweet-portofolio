<?php

namespace SweetPortofolio\Admin;

class Settings
{
    public function register()
    {
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
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
                    echo '<script>window.location.href = "' . admin_url('admin.php?page=portofolio-settings&cache-cleared-redirect=true') . '";</script>';
                }

                if (isset($_GET['refresh-data']) && $_GET['refresh-data'] == 'true') {
                    delete_transient('web_data_transient');
                    delete_transient('jenis_web_data');
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
                <?php settings_fields('portofolio-whatsapp-settings-group'); ?>
                <?php do_settings_sections('portofolio-whatsapp-settings-group'); ?>

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
                        <div x-data="portfolioPageGenerator()" class="portfolio-page-container">
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
                                            @click="generatePortfolioPage(true)"
                                            :disabled="generating"
                                            type="button"
                                            class="sweet-portofolio-button sweet-portofolio-button-primary"
                                            x-text="generating ? 'Generating...' : 'Generate Page'">
                                        </button>
                                        <button
                                            @click="viewPortfolioPage()"
                                            :disabled="!hasPortfolioPage()"
                                            type="button"
                                            class="sweet-portofolio-button sweet-portofolio-button-secondary">
                                            View Page
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div x-show="message" x-transition class="sweet-portofolio-notice" :class="'sweet-portofolio-notice-' + messageType">
                                <p x-text="message"></p>
                            </div>

                            <div class="sweet-portofolio-help-text">
                                <strong>Generate Page:</strong> Membuat halaman portofolio dengan template yang sudah ditentukan.<br><br>
                                Halaman akan menggunakan template khusus tanpa perlu menambahkan shortcode secara manual.
                            </div>
                        </div>
                    </div>

                    <div class="sweet-portofolio-form-section">
                        <h3 class="sweet-portofolio-section-title">Preview Page</h3>
                        <div x-data="previewPageGenerator()" class="preview-page-container">
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
                                            @click="generatePreviewPage(true)"
                                            :disabled="generating"
                                            type="button"
                                            class="sweet-portofolio-button sweet-portofolio-button-primary"
                                            x-text="generating ? 'Generating...' : 'Generate Preview Page'">
                                        </button>
                                        <button
                                            @click="viewPreviewPage()"
                                            :disabled="!hasPreviewPage()"
                                            type="button"
                                            class="sweet-portofolio-button sweet-portofolio-button-secondary">
                                            View Page
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div x-show="message" x-transition class="sweet-portofolio-notice" :class="'sweet-portofolio-notice-' + messageType">
                                <p x-text="message"></p>
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

                <div class="sweet-portofolio-form-actions">
                    <input type="submit" name="submit" id="submit" class="sweet-portofolio-submit" value="Save Changes">
                </div>
            </form>
        </div>

        <script>
            // Make sure Alpine.js is loaded before initializing components
            document.addEventListener('DOMContentLoaded', () => {

                // Check if Alpine.js is loaded
                if (typeof Alpine === 'undefined') {
                    console.error('Alpine.js is not loaded');
                    return;
                }

            });

            document.addEventListener('alpine:init', () => {

                // Function to generate portfolio page
                Alpine.data('portfolioPageGenerator', () => ({
                    generating: false,
                    message: '',
                    messageType: '',

                    async generatePortfolioPage(force = true) {

                        this.generating = true;
                        this.message = '';

                        try {
                            const apiUrl = '<?php echo rest_url('sweet-portofolio/v1/generate-portfolio-page'); ?>';

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

                            const data = await response.json();

                            if (response.ok) {
                                this.message = data.message || 'Portfolio page created successfully!';
                                this.messageType = 'success';

                                // Update the select dropdown
                                if (data.page_id) {
                                    const select = document.getElementById('portofolio_page_select');
                                    if (select) {
                                        // Add new option if not exists
                                        let optionExists = false;
                                        for (let i = 0; i < select.options.length; i++) {
                                            if (select.options[i].value == data.page_id) {
                                                optionExists = true;
                                                select.selectedIndex = i;
                                                break;
                                            }
                                        }

                                        if (!optionExists) {
                                            const newOption = document.createElement('option');
                                            newOption.value = data.page_id;
                                            newOption.text = data.page_title || 'Portofolio';
                                            newOption.selected = true;
                                            select.appendChild(newOption);

                                            // Trigger change event to notify WordPress
                                            const changeEvent = new Event('change', {
                                                bubbles: true
                                            });
                                            select.dispatchEvent(changeEvent);
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

                            // Clear message after 5 seconds
                            setTimeout(() => {
                                this.message = '';
                            }, 5000);
                        }
                    },

                    viewPortfolioPage() {
                        // Direct to the portfolio page on frontend
                        window.open('<?php echo home_url('/portofolio'); ?>', '_blank');
                    },

                    hasPortfolioPage() {
                        const select = document.getElementById('portofolio_page_select');
                        return select && select.value && select.value !== '-1';
                    }
                }));

                // Function to generate preview page
                Alpine.data('previewPageGenerator', () => ({
                    generating: false,
                    message: '',
                    messageType: '',

                    async generatePreviewPage(force = true) {

                        this.generating = true;
                        this.message = '';

                        try {
                            const apiUrl = '<?php echo rest_url('sweet-portofolio/v1/generate-preview-page'); ?>';

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

                            const data = await response.json();

                            if (response.ok) {
                                this.message = data.message || 'Preview page created successfully!';
                                this.messageType = 'success';

                                // Update the select dropdown
                                if (data.page_id) {
                                    const select = document.getElementById('portofolio_preview_page_select');
                                    if (select) {
                                        // Add new option if not exists
                                        let optionExists = false;
                                        for (let i = 0; i < select.options.length; i++) {
                                            if (select.options[i].value == data.page_id) {
                                                optionExists = true;
                                                select.selectedIndex = i;
                                                break;
                                            }
                                        }

                                        if (!optionExists) {
                                            const newOption = document.createElement('option');
                                            newOption.value = data.page_id;
                                            newOption.text = data.page_title || 'Preview Portofolio';
                                            newOption.selected = true;
                                            select.appendChild(newOption);

                                            // Trigger change event to notify WordPress
                                            const changeEvent = new Event('change', {
                                                bubbles: true
                                            });
                                            select.dispatchEvent(changeEvent);
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
                            console.error('Network error:', error);
                            this.message = 'Network error: ' + error.message;
                            this.messageType = 'error';
                        } finally {
                            this.generating = false;

                            // Clear message after 5 seconds
                            setTimeout(() => {
                                this.message = '';
                            }, 5000);
                        }
                    },

                    viewPreviewPage() {
                        // Direct to the preview page on frontend
                        window.open('<?php echo home_url('/preview-portofolio'); ?>', '_blank');
                    },

                    hasPreviewPage() {
                        const select = document.getElementById('portofolio_preview_page_select');
                        return select && select.value && select.value !== '-1';
                    }
                }));
            });
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

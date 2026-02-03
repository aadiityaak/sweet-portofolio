<?php

namespace SweetPortofolio\Admin;

class Settings
{
    public function register()
    {
        add_action('admin_menu', [$this, 'add_menu_page']);
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
        echo '<style>
        /* Modern Card-based Layout */
        .sweet-portofolio-settings {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .sweet-portofolio-header {
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .sweet-portofolio-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        
        .sweet-portofolio-header p {
            margin: 10px 0 0;
            opacity: 0.9;
            font-size: 16px;
        }
        
        .sweet-portofolio-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .sweet-portofolio-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 25px;
            margin-bottom: 25px;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        
        .sweet-portofolio-card:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }
        
        .sweet-portofolio-card h2 {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 20px;
            color: #1f2937;
            border-bottom: 2px solid #f3f4f6;
            padding-bottom: 10px;
        }
        
        .sweet-portofolio-form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        
        .sweet-portofolio-form-col {
            padding: 0 10px;
            flex: 1;
            min-width: 250px;
        }
        
        .sweet-portofolio-form-group {
            margin-bottom: 20px;
        }
        
        .sweet-portofolio-form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #374151;
        }
        
        .sweet-portofolio-form-group input[type="text"],
        .sweet-portofolio-form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        
        .sweet-portofolio-form-group input[type="text"]:focus,
        .sweet-portofolio-form-group select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .sweet-portofolio-form-group .description {
            font-size: 13px;
            color: #6b7280;
            margin-top: 5px;
        }
        
        .sweet-portofolio-button-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .sweet-portofolio-button {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            font-size: 14px;
        }
        
        .sweet-portofolio-button-primary {
            background: #3b82f6;
            color: white;
        }
        
        .sweet-portofolio-button-primary:hover {
            background: #2563eb;
        }
        
        .sweet-portofolio-button-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        
        .sweet-portofolio-button-secondary:hover {
            background: #e5e7eb;
        }
        
        .sweet-portofolio-checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }
        
        .sweet-portofolio-checkbox-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            background: #f9fafb;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            transition: all 0.2s;
        }
        
        .sweet-portofolio-checkbox-item:hover {
            background: #f3f4f6;
        }
        
        .sweet-portofolio-checkbox-item input[type="checkbox"] {
            margin-right: 10px;
        }
        
        .sweet-portofolio-status {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .sweet-portofolio-status-success {
            background: #d1fae5;
            color: #065f46;
        }
        
        .sweet-portofolio-status-error {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .sweet-portofolio-notice {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .sweet-portofolio-notice-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        .sweet-portofolio-notice-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        
        .sweet-portofolio-submit {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            font-size: 16px;
        }
        
        .sweet-portofolio-submit:hover {
            background: #2563eb;
        }
        
        .sweet-portofolio-help-text {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            font-size: 13px;
            color: #0c4a6e;
        }
        
        /* Additional styles for new elements */
        .sweet-portofolio-card-title {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 20px;
            color: #1f2937;
            border-bottom: 2px solid #f3f4f6;
            padding-bottom: 10px;
        }
        
        .sweet-portofolio-form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .sweet-portofolio-form-section:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .sweet-portofolio-section-title {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 16px;
            color: #4b5563;
            font-weight: 600;
        }
        
        .sweet-portofolio-label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #374151;
        }
        
        .sweet-portofolio-input,
        .sweet-portofolio-select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            background-color: #ffffff;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }
        
        .sweet-portofolio-input:focus,
        .sweet-portofolio-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background-color: #ffffff;
        }
        
        .sweet-portofolio-input::placeholder {
            color: #9ca3af;
        }
        
        .sweet-portofolio-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20width%3D%2716%27%20height%3D%2716%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20fill%3D%27%236b7280%27%20d%3D%27M7%2010l5%205%205-5z%27%2F%3E%3C%2Fsvg%3E");
            background-position: right 10px center;
            background-repeat: no-repeat;
            background-size: 16px;
            padding-right: 40px;
        }
        
        .sweet-portofolio-select:hover {
            border-color: #9ca3af;
        }
        
        /* Style for WordPress dropdown pages */
        #portofolio_page_select,
        #portofolio_preview_page_select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            background-color: #ffffff;
            transition: all 0.2s ease;
            box-sizing: border-box;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20width%3D%2716%27%20height%3D%2716%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cpath%20fill%3D%27%236b7280%27%20d%3D%27M7%2010l5%205%205-5z%27%2F%3E%3C%2Fsvg%3E");
            background-position: right 10px center;
            background-repeat: no-repeat;
            background-size: 16px;
            padding-right: 40px;
        }
        
        #portofolio_page_select:focus,
        #portofolio_preview_page_select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background-color: #ffffff;
        }
        
        #portofolio_page_select:hover,
        #portofolio_preview_page_select:hover {
            border-color: #9ca3af;
        }
        
        .sweet-portofolio-checkbox-label {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            background: #f9fafb;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .sweet-portofolio-checkbox-label:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
        }
        
        .sweet-portofolio-checkbox {
            margin-right: 10px;
            width: 16px;
            height: 16px;
            accent-color: #3b82f6;
            cursor: pointer;
        }
        
        .sweet-portofolio-checkbox-text {
            font-size: 14px;
            color: #374151;
        }
        
        .sweet-portofolio-form-actions {
            margin-top: 30px;
            text-align: right;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sweet-portofolio-form-row {
                flex-direction: column;
            }
            
            .sweet-portofolio-form-col {
                margin-bottom: 15px;
            }
            
            .sweet-portofolio-actions {
                flex-direction: column;
            }
            
            .sweet-portofolio-button-group {
                flex-direction: column;
            }
            
            .sweet-portofolio-form-actions {
                text-align: center;
            }
        }
        </style>';
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
                <a href="<?php echo admin_url('admin.php?page=portofolio-settings&cache-cleared=true'); ?>" class="sweet-portofolio-button sweet-portofolio-button-secondary">Clear Cache</a>
                <a href="<?php echo admin_url('admin.php?page=portofolio-settings&refresh-data=true'); ?>" class="sweet-portofolio-button sweet-portofolio-button-primary">Refresh Portfolio Data</a>
                <a href="<?php echo admin_url('admin.php?page=portofolio-settings&generate-pages=true'); ?>" class="sweet-portofolio-button sweet-portofolio-button-secondary">Generate Pages</a>
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
                console.log('DOM loaded');
                console.log('wpApiSettings:', window.wpApiSettings);

                // Check if Alpine.js is loaded
                if (typeof Alpine === 'undefined') {
                    console.error('Alpine.js is not loaded');
                    return;
                }

                console.log('Alpine.js is loaded');
            });

            document.addEventListener('alpine:init', () => {
                console.log('Alpine.js initializing');

                // Function to generate portfolio page
                Alpine.data('portfolioPageGenerator', () => ({
                    generating: false,
                    message: '',
                    messageType: '',

                    async generatePortfolioPage(force = true) {
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

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
function portofolio_whatsapp_settings_page() {
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

function portofolio_settings_page_content() {
    $access_key = get_option('portofolio_access_key');
    $portfolioSelection = (array) get_option('portofolio_selection', []);
    
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
            
            if (isset($_GET['cache-cleared-redirect']) && $_GET['cache-cleared-redirect'] == 'true') {
                echo '<div id="message" class="updated notice is-dismissible"><p>Cache cleared successfully.</p></div>';
            }
            
            if (isset($_GET['data-refreshed']) && $_GET['data-refreshed'] == 'true') {
                echo '<div id="message" class="updated notice is-dismissible"><p>Portfolio data refreshed successfully.</p></div>';
            }
            ?>
        </p>
        
        <form method="post" action="options.php">
            <?php settings_fields('portofolio-whatsapp-settings-group'); // Prefix added to settings group ?>
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
                        <?php
                        $selected_page = esc_attr(get_option('portofolio_page'));
                        wp_dropdown_pages(array(
                            'name' => 'portofolio_page',
                            'show_option_none' => '-- Select a Page --',
                            'option_none_value' => '-1',
                            'selected' => $selected_page,
                        ));
                        ?>
                        <br>
                        <span>
                            Pastikan sudah memasukkan shortcode di bawah ini pada page yang dipilih.<br>
                            [sweet-portofolio-jenis-web] Digunakan untuk menampilkan tombol filter berdasarkan jenis web.<br>
                            [sweet-portofolio-list default="profil-perusahaan"] Digunakan untuk menampilkan list thumbnail portofolio.<br>
                            [sweet-portofolio-list include="1982,1670" title="no"] Digunakan untuk menampilkan portofolio berdasarkan id nya
                        </span>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Preview Page</th>
                    <td>
                        <?php
                        $selected_page = esc_attr(get_option('portofolio_preview_page'));
                        wp_dropdown_pages(array(
                            'name' => 'portofolio_preview_page',
                            'show_option_none' => '-- Select a Page --',
                            'option_none_value' => '-1',
                            'selected' => $selected_page,
                        ));
                        ?>
                        <br>
                        <span>
                            Pastikan sudah merubah page template menjadi 'Preview Portofolio' pada page yang dipilih.
                        </span>
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
    <?php
}

function portofolio_register_whatsapp_settings() {
    register_setting('portofolio-whatsapp-settings-group', 'portofolio_whatsapp_number'); // Prefix added to setting name
    register_setting('portofolio-whatsapp-settings-group', 'portofolio_access_key', 'portofolio_validate_access_key'); // Prefix added to setting name
    register_setting('portofolio-whatsapp-settings-group', 'portofolio_credit');
    register_setting('portofolio-whatsapp-settings-group', 'portofolio_image_size'); // Register the new setting for image size
    register_setting('portofolio-whatsapp-settings-group', 'portofolio_page');
    register_setting('portofolio-whatsapp-settings-group', 'portofolio_preview_page');
    register_setting('portofolio-whatsapp-settings-group', 'portofolio_style_thumbnail');
    register_setting('portofolio-whatsapp-settings-group', 'portofolio_selection');
}

function portofolio_validate_access_key($input) {
    $old_value = get_option('portofolio_access_key');
    
    // If access key has changed, clear transients
    if ($old_value !== $input) {
        delete_transient('web_data_transient');
        delete_transient('jenis_web_data');
    }
    
    return $input;
}
add_action('admin_init', 'portofolio_register_whatsapp_settings');

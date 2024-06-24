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
    

    // Cek apakah data sudah ada dalam sesi
    $data = get_transient('jenis_web_data');

    if (!$data) {
        $api_url = 'https://my.websweetstudio.com/wp-json/wp/v2/jenis-web?access_key=' . $access_key;
        $response = wp_remote_get($api_url);

        if (is_wp_error($response)) {
            return 'Error fetching data.';
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Simpan data dalam transient selama 1 jam (3600 detik)
        $transient_key = 'jenis_web_data';
        $transient_set = set_transient($transient_key, $data, 12 * 3600);
    }
    ?>
    <div class="wrap">
        <h2>Portofolio WhatsApp Settings</h2>
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
                    <td><input type="text" name="portofolio_access_key" value="<?php echo esc_attr(get_option('portofolio_access_key')); ?>" /></td>
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
    register_setting('portofolio-whatsapp-settings-group', 'portofolio_access_key'); // Prefix added to setting name
    register_setting('portofolio-whatsapp-settings-group', 'portofolio_credit');
    register_setting('portofolio-whatsapp-settings-group', 'portofolio_image_size'); // Register the new setting for image size
    register_setting('portofolio-whatsapp-settings-group', 'portofolio_page');
    register_setting('portofolio-whatsapp-settings-group', 'portofolio_preview_page');
    register_setting('portofolio-whatsapp-settings-group', 'portofolio_style_thumbnail');
    register_setting('portofolio-whatsapp-settings-group', 'portofolio_selection');
}
add_action('admin_init', 'portofolio_register_whatsapp_settings');

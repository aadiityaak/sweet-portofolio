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
                            <option value="thumbnail" <?php selected(get_option('portofolio_image_size'), 'thumbnail'); ?>>Thumbnail (400 x 400)</option>
                            <option value="large" <?php selected(get_option('portofolio_image_size'), 'large'); ?>>Large (700 x 700)</option>
                            <option value="full" <?php selected(get_option('portofolio_image_size'), 'full'); ?>>Full (1000 x 1000)</option>
                        </select>
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
}
add_action('admin_init', 'portofolio_register_whatsapp_settings');

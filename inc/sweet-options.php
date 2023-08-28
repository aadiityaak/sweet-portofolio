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
        'Portofolio WhatsApp',
        'Portofolio WhatsApp',
        'manage_options',
        'portofolio-whatsapp-settings', // Prefix added to page slug
        'portofolio_whatsapp_settings_page_content',
        'dashicons-whatsapp',
        30
    );
}
add_action('admin_menu', 'portofolio_whatsapp_settings_page');

function portofolio_whatsapp_settings_page_content() {
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
                    <th scope="row">WhatsApp Number</th>
                    <td><input type="text" name="portofolio_access_key" value="<?php echo esc_attr(get_option('portofolio_access_key')); ?>" /></td>
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
}
add_action('admin_init', 'portofolio_register_whatsapp_settings');

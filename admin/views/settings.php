<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap wcpi-wrap">
    <h1><?php esc_html_e( 'Settings', WCPI_TEXT_DOMAIN ); ?></h1>

    <?php if ( isset( $_GET['updated'] ) ) : ?>
        <div class="notice notice-success"><p><?php esc_html_e( 'Settings saved.', WCPI_TEXT_DOMAIN ); ?></p></div>
    <?php endif; ?>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="wcpi-card">
        <input type="hidden" name="action" value="wcpi_save_settings">
        <?php WCPI_Security::nonce_field( 'wcpi_save_settings' ); ?>
        <table class="form-table">
            <tr><th><?php esc_html_e( 'Currency Display Preference', WCPI_TEXT_DOMAIN ); ?></th><td><input class="regular-text" type="text" name="currency_display" value="<?php echo esc_attr( $settings['currency_display'] ?? get_option( 'woocommerce_currency', 'USD' ) ); ?>"></td></tr>
            <tr><th><?php esc_html_e( 'Include Tax in Revenue', WCPI_TEXT_DOMAIN ); ?></th><td><label><input type="checkbox" name="include_tax" value="yes" <?php checked( $settings['include_tax'] ?? 'no', 'yes' ); ?>> <?php esc_html_e( 'Yes', WCPI_TEXT_DOMAIN ); ?></label></td></tr>
            <tr><th><?php esc_html_e( 'Include Shipping in Revenue', WCPI_TEXT_DOMAIN ); ?></th><td><label><input type="checkbox" name="include_shipping" value="yes" <?php checked( $settings['include_shipping'] ?? 'yes', 'yes' ); ?>> <?php esc_html_e( 'Yes', WCPI_TEXT_DOMAIN ); ?></label></td></tr>
            <tr><th><?php esc_html_e( 'Default Dashboard Range', WCPI_TEXT_DOMAIN ); ?></th><td><select name="default_dashboard_range"><?php foreach ( WCPI_Helpers::date_presets() as $key => $label ) : ?><option value="<?php echo esc_attr( $key ); ?>" <?php selected( $settings['default_dashboard_range'] ?? 'last_30_days', $key ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select></td></tr>
            <tr><th><?php esc_html_e( 'Enable Logs', WCPI_TEXT_DOMAIN ); ?></th><td><label><input type="checkbox" name="enable_logs" value="yes" <?php checked( $settings['enable_logs'] ?? 'no', 'yes' ); ?>> <?php esc_html_e( 'Enable debug and sync logs', WCPI_TEXT_DOMAIN ); ?></label></td></tr>
            <tr><th><?php esc_html_e( 'Log Retention Days', WCPI_TEXT_DOMAIN ); ?></th><td><input type="number" min="1" name="log_retention_days" value="<?php echo esc_attr( $settings['log_retention_days'] ?? 30 ); ?>"></td></tr>
            <tr><th><?php esc_html_e( 'Cache Retention Days', WCPI_TEXT_DOMAIN ); ?></th><td><input type="number" min="1" name="cache_retention_days" value="<?php echo esc_attr( $settings['cache_retention_days'] ?? 7 ); ?>"></td></tr>
            <tr><th><?php esc_html_e( 'Export Retention Days', WCPI_TEXT_DOMAIN ); ?></th><td><input type="number" min="1" name="export_retention_days" value="<?php echo esc_attr( $settings['export_retention_days'] ?? 14 ); ?>"></td></tr>
            <tr><th><?php esc_html_e( 'Enable Backups', WCPI_TEXT_DOMAIN ); ?></th><td><label><input type="checkbox" name="backup_enabled" value="yes" <?php checked( $settings['backup_enabled'] ?? 'no', 'yes' ); ?>> <?php esc_html_e( 'Store encrypted backup snapshots in uploads/backups', WCPI_TEXT_DOMAIN ); ?></label></td></tr>
            <tr><th><?php esc_html_e( 'Backup Retention Limit', WCPI_TEXT_DOMAIN ); ?></th><td><input type="number" min="1" name="backup_retention_limit" value="<?php echo esc_attr( $settings['backup_retention_limit'] ?? 5 ); ?>"></td></tr>
            <tr><th><?php esc_html_e( 'Delete Plugin Data on Uninstall', WCPI_TEXT_DOMAIN ); ?></th><td><label><input type="checkbox" name="delete_on_uninstall" value="yes" <?php checked( $settings['delete_on_uninstall'] ?? 'no', 'yes' ); ?>> <?php esc_html_e( 'Delete custom tables, settings and plugin upload files when uninstalling.', WCPI_TEXT_DOMAIN ); ?></label></td></tr>
        </table>
        <p><button class="button button-primary"><?php esc_html_e( 'Save Settings', WCPI_TEXT_DOMAIN ); ?></button></p>
    </form>
</div>

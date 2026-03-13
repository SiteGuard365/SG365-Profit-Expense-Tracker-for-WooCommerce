<?php
/**
 * Settings page.
 *
 * @package WCPI
 */

defined( 'ABSPATH' ) || exit;

class WCPI_Settings_Page {

    /**
     * Render settings page.
     *
     * @return void
     */
    public static function render(): void {
        WCPI_Security::verify_access();
        $settings = WCPI_Settings_Manager::group( 'general' );
        include WCPI_PLUGIN_DIR . 'admin/views/settings.php';
    }
}

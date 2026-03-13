<?php
/**
 * Reports page.
 *
 * @package WCPI
 */

defined( 'ABSPATH' ) || exit;

class WCPI_Reports_Page {

    /**
     * Render.
     *
     * @return void
     */
    public static function render(): void {
        WCPI_Security::verify_access();
        include WCPI_PLUGIN_DIR . 'admin/views/reports.php';
    }
}

<?php
/**
 * Dashboard page.
 *
 * @package WCPI
 */

defined( 'ABSPATH' ) || exit;

class WCPI_Dashboard_Page {

    /**
     * Render page.
     *
     * @return void
     */
    public static function render(): void {
        WCPI_Security::verify_access();
        $range      = WCPI_Helpers::resolve_date_range( WCPI_Settings_Manager::get( 'general', 'default_dashboard_range', 'last_30_days' ) );
        $data       = WCPI_Analytics_Engine::dashboard( $range['from'], $range['to'] );
        $today      = WCPI_Helpers::resolve_date_range( 'today' );
        $this_month = WCPI_Helpers::resolve_date_range( 'this_month' );
        $today_data = WCPI_Analytics_Engine::dashboard( $today['from'], $today['to'] );
        $month_data = WCPI_Analytics_Engine::dashboard( $this_month['from'], $this_month['to'] );
        include WCPI_PLUGIN_DIR . 'admin/views/dashboard.php';
    }
}

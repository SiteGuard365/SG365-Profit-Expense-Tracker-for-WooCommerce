<?php
/**
 * AJAX endpoints.
 *
 * @package WCPI
 */

defined( 'ABSPATH' ) || exit;

class WCPI_Ajax {

    /**
     * Register.
     *
     * @return void
     */
    public function register(): void {
        add_action( 'wp_ajax_wcpi_dashboard_data', array( $this, 'dashboard_data' ) );
        add_action( 'wp_ajax_wcpi_reports_data', array( $this, 'reports_data' ) );
        add_action( 'wp_ajax_wcpi_rebuild_summaries', array( $this, 'rebuild_summaries' ) );
        add_action( 'wp_ajax_wcpi_run_diagnostics', array( $this, 'run_diagnostics' ) );
    }

    /**
     * Dashboard data.
     *
     * @return void
     */
    public function dashboard_data(): void {
        WCPI_Security::verify_access();
        check_ajax_referer( 'wcpi_admin_nonce', 'nonce' );

        $preset = WCPI_Security::text( $_POST['preset'] ?? WCPI_Settings_Manager::get( 'general', 'default_dashboard_range', 'last_30_days' ) );
        $from   = WCPI_Security::date( $_POST['from'] ?? '' );
        $to     = WCPI_Security::date( $_POST['to'] ?? '' );
        $range  = WCPI_Helpers::resolve_date_range( $preset, $from, $to );

        wp_send_json_success( WCPI_Analytics_Engine::dashboard( $range['from'], $range['to'] ) );
    }

    /**
     * Reports data.
     *
     * @return void
     */
    public function reports_data(): void {
        WCPI_Security::verify_access();
        check_ajax_referer( 'wcpi_admin_nonce', 'nonce' );

        $preset = WCPI_Security::text( $_POST['preset'] ?? 'last_30_days' );
        $from   = WCPI_Security::date( $_POST['from'] ?? '' );
        $to     = WCPI_Security::date( $_POST['to'] ?? '' );
        $range  = WCPI_Helpers::resolve_date_range( $preset, $from, $to );

        $response = array(
            'top_products' => WCPI_Report_Query::product_profitability( $range['from'], $range['to'], 20, 'profit_desc' ),
            'low_margin'   => WCPI_Report_Query::product_profitability( $range['from'], $range['to'], 20, 'margin_asc' ),
            'high_revenue' => WCPI_Report_Query::product_profitability( $range['from'], $range['to'], 20, 'revenue_desc' ),
            'most_sold'    => WCPI_Report_Query::product_profitability( $range['from'], $range['to'], 20, 'qty_desc' ),
            'summary_rows' => WCPI_Report_Query::summaries( $range['from'], $range['to'] ),
        );

        wp_send_json_success( $response );
    }

    /**
     * Rebuild summaries.
     *
     * @return void
     */
    public function rebuild_summaries(): void {
        WCPI_Security::verify_access();
        check_ajax_referer( 'wcpi_admin_nonce', 'nonce' );

        $from = WCPI_Security::date( $_POST['from'] ?? gmdate( 'Y-m-d' ) );
        $to   = WCPI_Security::date( $_POST['to'] ?? gmdate( 'Y-m-d' ) );

        WCPI_Daily_Summary::rebuild_range( $from, $to );

        wp_send_json_success(
            array(
                'message' => sprintf(
                    /* translators: 1: from date 2: to date */
                    __( 'Summaries rebuilt for %1$s to %2$s.', WCPI_TEXT_DOMAIN ),
                    $from,
                    $to
                ),
            )
        );
    }

    /**
     * Diagnostics.
     *
     * @return void
     */
    public function run_diagnostics(): void {
        WCPI_Security::verify_access();
        check_ajax_referer( 'wcpi_admin_nonce', 'nonce' );

        global $wpdb;
        $results = array();
        foreach ( WCPI_DB::tables() as $key => $table ) {
            $exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
            $results[ $key ] = array(
                'table' => $table,
                'exists'=> $exists,
                'rows'  => $exists ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ) : 0, // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
            );
        }

        $base = WCPI_Helpers::upload_base();
        $results['filesystem'] = array(
            'dir'      => $base['dir'],
            'writable' => is_dir( $base['dir'] ) && wp_is_writable( $base['dir'] ),
        );

        wp_send_json_success( $results );
    }
}

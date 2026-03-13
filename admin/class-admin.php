<?php
/**
 * Admin bootstrap.
 *
 * @package WCPI
 */

defined( 'ABSPATH' ) || exit;

class WCPI_Admin {

    /**
     * Register admin features.
     *
     * @return void
     */
    public function register(): void {
        ( new WCPI_Admin_Menu() )->register();

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'admin_post_wcpi_save_settings', array( $this, 'save_settings' ) );
        add_action( 'admin_post_wcpi_generate_export', array( $this, 'generate_export' ) );
        add_action( 'admin_post_wcpi_maintenance', array( $this, 'maintenance_action' ) );
        add_action( 'admin_post_wcpi_save_product_costs_page', array( $this, 'save_product_costs_page' ) );
    }

    /**
     * Enqueue admin assets.
     *
     * @param string $hook_suffix Hook.
     * @return void
     */
    public function enqueue_assets( string $hook_suffix ): void {
        if ( false === strpos( $hook_suffix, 'wcpi' ) && false === strpos( $hook_suffix, 'product' ) ) {
            return;
        }

        wp_enqueue_style( 'wcpi-admin', WCPI_PLUGIN_URL . 'assets/css/admin.css', array(), WCPI_VERSION );
        wp_enqueue_style( 'wcpi-dashboard', WCPI_PLUGIN_URL . 'assets/css/dashboard.css', array( 'wcpi-admin' ), WCPI_VERSION );
        wp_enqueue_script( 'wcpi-admin', WCPI_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ), WCPI_VERSION, true );
        wp_enqueue_script( 'wcpi-dashboard', WCPI_PLUGIN_URL . 'assets/js/dashboard.js', array( 'jquery', 'wcpi-admin' ), WCPI_VERSION, true );
        wp_enqueue_script( 'wcpi-reports', WCPI_PLUGIN_URL . 'assets/js/reports.js', array( 'jquery', 'wcpi-admin' ), WCPI_VERSION, true );

        wp_localize_script(
            'wcpi-admin',
            'wcpiAdmin',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'wcpi_admin_nonce' ),
                'i18n'    => array(
                    'loading' => __( 'Loading…', WCPI_TEXT_DOMAIN ),
                    'error'   => __( 'Something went wrong.', WCPI_TEXT_DOMAIN ),
                ),
            )
        );
    }

    /**
     * Save settings.
     *
     * @return void
     */
    public function save_settings(): void {
        WCPI_Security::verify_access();
        WCPI_Security::verify_nonce( 'wcpi_save_settings' );

        $group    = 'general';
        $defaults = WCPI_Settings_Manager::defaults()['general'];

        foreach ( $defaults as $key => $default_value ) {
            $value = $_POST[ $key ] ?? ( is_numeric( $default_value ) ? $default_value : 'no' );
            if ( is_numeric( $default_value ) ) {
                $value = absint( $value );
            } else {
                $value = WCPI_Security::text( $value );
            }
            WCPI_Settings_Manager::set( $group, $key, $value );
        }

        wp_safe_redirect( admin_url( 'admin.php?page=wcpi-settings&updated=1' ) );
        exit;
    }

    /**
     * Generate export.
     *
     * @return void
     */
    public function generate_export(): void {
        WCPI_Security::verify_access();
        WCPI_Security::verify_nonce( 'wcpi_generate_export' );

        $report = WCPI_Security::text( $_POST['report'] ?? 'daily_summary' );
        $format = WCPI_Security::text( $_POST['format'] ?? 'csv' );
        $preset = WCPI_Security::text( $_POST['preset'] ?? 'last_30_days' );
        $from   = WCPI_Security::date( $_POST['from'] ?? '' );
        $to     = WCPI_Security::date( $_POST['to'] ?? '' );
        $range  = WCPI_Helpers::resolve_date_range( $preset, $from, $to );

        $export = 'pdf' === $format ? WCPI_Export_PDF::generate( $report, $range['from'], $range['to'] ) : WCPI_Export_CSV::generate( $report, $range['from'], $range['to'] );
        $url    = is_wp_error( $export ) ? '' : rawurlencode( $export['url'] );

        wp_safe_redirect( admin_url( 'admin.php?page=wcpi-export&generated=1&file=' . $url ) );
        exit;
    }

    /**
     * Maintenance.
     *
     * @return void
     */
    public function maintenance_action(): void {
        WCPI_Security::verify_access();
        WCPI_Security::verify_nonce( 'wcpi_maintenance' );

        $action = WCPI_Security::text( $_POST['maintenance_action'] ?? '' );

        switch ( $action ) {
            case 'clear_cache':
                WCPI_Cache::flush_group();
                WCPI_Aggregates::purge();
                break;
            case 'clear_exports':
                WCPI_Filesystem::cleanup_old_files( 'exports', 0 );
                break;
            case 'clear_logs':
                WCPI_Filesystem::cleanup_old_files( 'logs', 0 );
                global $wpdb;
                $wpdb->query( 'TRUNCATE TABLE ' . WCPI_DB::table( 'logs' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
                break;
            case 'rebuild_last_30':
                $range = WCPI_Helpers::resolve_date_range( 'last_30_days' );
                WCPI_Daily_Summary::rebuild_range( $range['from'], $range['to'] );
                break;
            case 'create_backup':
                WCPI_Backup::create_snapshot();
                break;
            case 'optimize_tables':
                global $wpdb;
                foreach ( WCPI_DB::tables() as $table ) {
                    $wpdb->query( "OPTIMIZE TABLE {$table}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
                }
                break;
        }

        wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=wcpi-diagnostics&updated=1' ) );
        exit;
    }

    /**
     * Save costs from list page.
     *
     * @return void
     */
    public function save_product_costs_page(): void {
        WCPI_Security::verify_access();
        WCPI_Security::verify_nonce( 'wcpi_save_product_costs_page' );

        $product_ids = array_map( 'absint', (array) ( $_POST['product_id'] ?? array() ) );

        foreach ( $product_ids as $index => $product_id ) {
            if ( $product_id <= 0 ) {
                continue;
            }

            $payload = array(
                'product_id'      => $product_id,
                'variation_id'    => absint( $_POST['variation_id'][ $index ] ?? 0 ),
                'cost_price'      => WCPI_Security::decimal( $_POST['cost_price'][ $index ] ?? 0 ),
                'packaging_cost'  => WCPI_Security::decimal( $_POST['packaging_cost'][ $index ] ?? 0 ),
                'handling_cost'   => WCPI_Security::decimal( $_POST['handling_cost'][ $index ] ?? 0 ),
                'extra_cost'      => WCPI_Security::decimal( $_POST['extra_cost'][ $index ] ?? 0 ),
                'total_unit_cost' => WCPI_Security::decimal( $_POST['total_unit_cost'][ $index ] ?? 0 ),
            );

            if ( empty( $payload['total_unit_cost'] ) ) {
                $payload['total_unit_cost'] = $payload['cost_price'] + $payload['packaging_cost'] + $payload['handling_cost'] + $payload['extra_cost'];
            }

            WCPI_Product_Cost_Manager::upsert_cost( $payload, 'List table update' );
        }

        wp_safe_redirect( admin_url( 'admin.php?page=wcpi-product-costs&updated=1' ) );
        exit;
    }
}

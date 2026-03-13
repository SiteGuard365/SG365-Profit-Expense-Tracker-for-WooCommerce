<?php
/**
 * Diagnostics page.
 *
 * @package WCPI
 */

defined( 'ABSPATH' ) || exit;

class WCPI_Diagnostics_Page {

    /**
     * Render.
     *
     * @return void
     */
    public static function render(): void {
        WCPI_Security::verify_access();

        global $wpdb;
        $tables = array();
        foreach ( WCPI_DB::tables() as $key => $table ) {
            $exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
            $tables[ $key ] = array(
                'name'   => $table,
                'exists' => $exists,
                'rows'   => $exists ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ) : 0, // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
            );
        }

        $base = WCPI_Helpers::upload_base();
        $log_page = max( 1, absint( $_GET['log_paged'] ?? 1 ) );
        $per_page = 20;
        $offset   = ( $log_page - 1 ) * $per_page;
        $logs     = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT * FROM ' . WCPI_DB::table( 'logs' ) . ' ORDER BY created_at DESC LIMIT %d OFFSET %d',
                $per_page,
                $offset
            ),
            ARRAY_A
        );
        $log_count = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . WCPI_DB::table( 'logs' ) );

        include WCPI_PLUGIN_DIR . 'admin/views/diagnostics.php';
    }
}

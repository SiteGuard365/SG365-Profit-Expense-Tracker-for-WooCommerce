<?php
/**
 * Settings manager.
 *
 * @package WCPI
 */

defined( 'ABSPATH' ) || exit;

class WCPI_Settings_Manager {

    /**
     * Defaults.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function defaults(): array {
        return array(
            'general' => array(
                'currency_display'         => get_option( 'woocommerce_currency', 'USD' ),
                'include_tax'              => 'no',
                'include_shipping'         => 'yes',
                'default_dashboard_range'  => 'last_30_days',
                'enable_logs'              => 'no',
                'log_retention_days'       => 30,
                'cache_retention_days'     => 7,
                'export_retention_days'    => 14,
                'backup_enabled'           => 'no',
                'backup_retention_limit'   => 5,
                'delete_on_uninstall'      => 'no',
                'last_sync_status'         => '',
            ),
        );
    }

    /**
     * Register settings hooks.
     *
     * @return void
     */
    public function register(): void {
        add_action( 'admin_init', array( $this, 'maybe_upgrade' ) );
    }

    /**
     * Ensure defaults exist.
     *
     * @return void
     */
    public static function seed_defaults(): void {
        global $wpdb;
        $table = WCPI_DB::table( 'settings_meta' );

        foreach ( self::defaults() as $group => $settings ) {
            foreach ( $settings as $key => $value ) {
                $exists = $wpdb->get_var(
                    $wpdb->prepare( "SELECT id FROM {$table} WHERE setting_key = %s", $key )
                );

                if ( $exists ) {
                    continue;
                }

                $wpdb->insert(
                    $table,
                    array(
                        'setting_group' => $group,
                        'setting_key'   => $key,
                        'setting_value' => is_scalar( $value ) ? (string) $value : wp_json_encode( $value ),
                        'autoload_flag' => 0,
                        'updated_at'    => current_time( 'mysql', true ),
                    ),
                    array( '%s', '%s', '%s', '%d', '%s' )
                );
            }
        }
    }

    /**
     * Get a setting.
     *
     * @param string $group Group.
     * @param string $key Key.
     * @param mixed  $default Default.
     * @return mixed
     */
    public static function get( string $group, string $key, $default = '' ) {
        global $wpdb;
        $table = WCPI_DB::table( 'settings_meta' );

        $value = $wpdb->get_var(
            $wpdb->prepare( "SELECT setting_value FROM {$table} WHERE setting_group = %s AND setting_key = %s LIMIT 1", $group, $key )
        );

        if ( null === $value ) {
            return $default;
        }

        if ( is_numeric( $value ) && ! str_contains( (string) $value, '.' ) ) {
            return (string) $value;
        }

        return $value;
    }

    /**
     * Set a setting.
     *
     * @param string $group Group.
     * @param string $key Key.
     * @param mixed  $value Value.
     * @param int    $autoload Autoload.
     * @return void
     */
    public static function set( string $group, string $key, $value, int $autoload = 0 ): void {
        global $wpdb;
        $table = WCPI_DB::table( 'settings_meta' );

        $wpdb->replace(
            $table,
            array(
                'id'            => $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE setting_key = %s", $key ) ),
                'setting_group' => $group,
                'setting_key'   => $key,
                'setting_value' => is_scalar( $value ) ? (string) $value : wp_json_encode( $value ),
                'autoload_flag' => $autoload,
                'updated_at'    => current_time( 'mysql', true ),
            ),
            array( '%d', '%s', '%s', '%s', '%d', '%s' )
        );
    }

    /**
     * Get all group settings.
     *
     * @param string $group Group.
     * @return array<string, string>
     */
    public static function group( string $group ): array {
        global $wpdb;
        $table = WCPI_DB::table( 'settings_meta' );

        $rows = $wpdb->get_results(
            $wpdb->prepare( "SELECT setting_key, setting_value FROM {$table} WHERE setting_group = %s", $group ),
            ARRAY_A
        );

        $settings = array();
        foreach ( $rows as $row ) {
            $settings[ $row['setting_key'] ] = $row['setting_value'];
        }

        return $settings;
    }

    /**
     * Upgrade/install missing settings.
     *
     * @return void
     */
    public function maybe_upgrade(): void {
        self::seed_defaults();
    }
}

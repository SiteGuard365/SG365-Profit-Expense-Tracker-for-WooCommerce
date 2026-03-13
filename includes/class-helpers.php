<?php
/**
 * Generic helpers.
 *
 * @package WCPI
 */

defined( 'ABSPATH' ) || exit;

class WCPI_Helpers {

    /**
     * Whether WooCommerce is active.
     *
     * @return bool
     */
    public static function is_woocommerce_active(): bool {
        return class_exists( 'WooCommerce' );
    }

    /**
     * Standard capability.
     *
     * @return string
     */
    public static function capability(): string {
        return 'manage_woocommerce';
    }

    /**
     * Allowed expense categories.
     *
     * @return array<string, string>
     */
    public static function expense_categories(): array {
        return array(
            'advertising'   => __( 'Advertising', WCPI_TEXT_DOMAIN ),
            'shipping'      => __( 'Shipping', WCPI_TEXT_DOMAIN ),
            'packaging'     => __( 'Packaging', WCPI_TEXT_DOMAIN ),
            'salaries'      => __( 'Salaries', WCPI_TEXT_DOMAIN ),
            'warehouse'     => __( 'Warehouse', WCPI_TEXT_DOMAIN ),
            'software'      => __( 'Software', WCPI_TEXT_DOMAIN ),
            'tax_adjustment'=> __( 'Tax Adjustment', WCPI_TEXT_DOMAIN ),
            'utilities'     => __( 'Utilities', WCPI_TEXT_DOMAIN ),
            'miscellaneous' => __( 'Miscellaneous', WCPI_TEXT_DOMAIN ),
        );
    }

    /**
     * Get upload URL or path.
     *
     * @return array<string, string>
     */
    public static function upload_base(): array {
        $uploads = wp_upload_dir();
        $basedir = trailingslashit( $uploads['basedir'] ) . 'wc-profit-intelligence/';
        $baseurl = trailingslashit( $uploads['baseurl'] ) . 'wc-profit-intelligence/';

        return array(
            'dir' => $basedir,
            'url' => $baseurl,
        );
    }

    /**
     * Date presets.
     *
     * @return array<string, string>
     */
    public static function date_presets(): array {
        return array(
            'today'       => __( 'Today', WCPI_TEXT_DOMAIN ),
            'yesterday'   => __( 'Yesterday', WCPI_TEXT_DOMAIN ),
            'last_7_days' => __( 'Last 7 Days', WCPI_TEXT_DOMAIN ),
            'last_30_days'=> __( 'Last 30 Days', WCPI_TEXT_DOMAIN ),
            'this_month'  => __( 'This Month', WCPI_TEXT_DOMAIN ),
            'last_month'  => __( 'Last Month', WCPI_TEXT_DOMAIN ),
            'this_quarter'=> __( 'This Quarter', WCPI_TEXT_DOMAIN ),
            'this_year'   => __( 'This Year', WCPI_TEXT_DOMAIN ),
            'custom'      => __( 'Custom Range', WCPI_TEXT_DOMAIN ),
        );
    }

    /**
     * Resolve date range.
     *
     * @param string      $preset Preset.
     * @param string|null $from From.
     * @param string|null $to To.
     * @return array<string, string>
     */
    public static function resolve_date_range( string $preset = 'last_30_days', ?string $from = null, ?string $to = null ): array {
        $timezone = wp_timezone();
        $today    = new DateTimeImmutable( 'now', $timezone );
        $start    = $today->setTime( 0, 0, 0 );
        $end      = $today->setTime( 23, 59, 59 );

        switch ( $preset ) {
            case 'today':
                break;
            case 'yesterday':
                $start = $start->modify( '-1 day' );
                $end   = $end->modify( '-1 day' );
                break;
            case 'last_7_days':
                $start = $start->modify( '-6 days' );
                break;
            case 'last_30_days':
                $start = $start->modify( '-29 days' );
                break;
            case 'this_month':
                $start = $start->modify( 'first day of this month' );
                $end   = $end->modify( 'last day of this month' );
                break;
            case 'last_month':
                $start = $start->modify( 'first day of last month' );
                $end   = $end->modify( 'last day of last month' );
                break;
            case 'this_quarter':
                $month         = (int) $today->format( 'n' );
                $quarter_start = (int) ( floor( ( $month - 1 ) / 3 ) * 3 ) + 1;
                $start         = $today->setDate( (int) $today->format( 'Y' ), $quarter_start, 1 )->setTime( 0, 0, 0 );
                $end           = $start->modify( '+2 months' )->modify( 'last day of this month' )->setTime( 23, 59, 59 );
                break;
            case 'this_year':
                $start = $today->setDate( (int) $today->format( 'Y' ), 1, 1 )->setTime( 0, 0, 0 );
                $end   = $today->setDate( (int) $today->format( 'Y' ), 12, 31 )->setTime( 23, 59, 59 );
                break;
            case 'custom':
                if ( $from ) {
                    $start = new DateTimeImmutable( $from . ' 00:00:00', $timezone );
                }
                if ( $to ) {
                    $end = new DateTimeImmutable( $to . ' 23:59:59', $timezone );
                }
                break;
        }

        return array(
            'from' => $start->format( 'Y-m-d' ),
            'to'   => $end->format( 'Y-m-d' ),
        );
    }

    /**
     * Currency symbol.
     *
     * @return string
     */
    public static function currency_symbol(): string {
        if ( function_exists( 'get_woocommerce_currency_symbol' ) ) {
            return get_woocommerce_currency_symbol();
        }
        return '$';
    }

    /**
     * Format price.
     *
     * @param float $amount Amount.
     * @return string
     */
    public static function format_price( float $amount ): string {
        if ( function_exists( 'wc_price' ) ) {
            return wp_kses_post( wc_price( $amount ) );
        }
        return esc_html( self::currency_symbol() . number_format_i18n( $amount, 2 ) );
    }

    /**
     * Safe array get.
     *
     * @param array<mixed> $array Array.
     * @param string|int   $key Key.
     * @param mixed        $default Default.
     * @return mixed
     */
    public static function get( array $array, $key, $default = '' ) {
        return $array[ $key ] ?? $default;
    }

    /**
     * Human bytes.
     *
     * @param int $bytes Size.
     * @return string
     */
    public static function human_bytes( int $bytes ): string {
        return size_format( $bytes );
    }
}

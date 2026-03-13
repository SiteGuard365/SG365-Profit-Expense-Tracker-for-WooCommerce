<?php
/**
 * Analytics engine.
 *
 * @package WCPI
 */

defined( 'ABSPATH' ) || exit;

class WCPI_Analytics_Engine {

    /**
     * Calculate one date.
     *
     * @param string $date Date.
     * @return array<string, float|int>
     */
    public static function calculate_date( string $date ): array {
        $orders = self::get_orders_for_date( $date );

        $totals = array(
            'orders_count'       => 0,
            'items_sold'         => 0,
            'gross_revenue'      => 0.0,
            'discounts_total'    => 0.0,
            'refunds_total'      => 0.0,
            'shipping_collected' => 0.0,
            'tax_collected'      => 0.0,
            'product_cost_total' => 0.0,
            'expenses_total'     => (float) self::get_expenses_total( $date, $date ),
            'net_profit'         => 0.0,
            'margin_percent'     => 0.0,
        );

        foreach ( $orders as $order ) {
            $metrics = WCPI_Profit_Calculator::order_metrics( $order );
            foreach ( $metrics as $key => $value ) {
                if ( isset( $totals[ $key ] ) ) {
                    $totals[ $key ] += $value;
                }
            }
        }

        $totals['net_profit']     = $totals['gross_revenue'] - $totals['product_cost_total'] - $totals['expenses_total'] - $totals['refunds_total'];
        $totals['margin_percent'] = $totals['gross_revenue'] > 0 ? ( $totals['net_profit'] / $totals['gross_revenue'] ) * 100 : 0;

        return $totals;
    }

    /**
     * Dashboard payload.
     *
     * @param string $from From.
     * @param string $to To.
     * @return array<string, mixed>
     */
    public static function dashboard( string $from, string $to ): array {
        $cache_key = 'dashboard_' . $from . '_' . $to;
        $cached    = WCPI_Aggregates::get( 'dashboard', $cache_key, $from, $to );
        if ( $cached ) {
            return $cached;
        }

        $rows = WCPI_Report_Query::summaries( $from, $to );

        if ( empty( $rows ) ) {
            WCPI_Daily_Summary::rebuild_range( $from, $to );
            $rows = WCPI_Report_Query::summaries( $from, $to );
        }

        $totals = array(
            'revenue'         => 0.0,
            'expenses'        => 0.0,
            'net_profit'      => 0.0,
            'orders_count'    => 0,
            'units_sold'      => 0,
            'product_cost'    => 0.0,
            'refunds_total'   => 0.0,
            'aov'             => 0.0,
            'margin_percent'  => 0.0,
            'chart_labels'    => array(),
            'chart_revenue'   => array(),
            'chart_profit'    => array(),
            'chart_margin'    => array(),
            'expense_breakdown'=> WCPI_Report_Query::expense_breakdown( $from, $to ),
        );

        foreach ( $rows as $row ) {
            $totals['revenue']        += (float) $row['gross_revenue'];
            $totals['expenses']       += (float) $row['expenses_total'];
            $totals['net_profit']     += (float) $row['net_profit'];
            $totals['orders_count']   += (int) $row['orders_count'];
            $totals['units_sold']     += (int) $row['items_sold'];
            $totals['product_cost']   += (float) $row['product_cost_total'];
            $totals['refunds_total']  += (float) $row['refunds_total'];
            $totals['chart_labels'][] = $row['summary_date'];
            $totals['chart_revenue'][]= (float) $row['gross_revenue'];
            $totals['chart_profit'][] = (float) $row['net_profit'];
            $totals['chart_margin'][] = (float) $row['margin_percent'];
        }

        $totals['aov']            = $totals['orders_count'] > 0 ? $totals['revenue'] / $totals['orders_count'] : 0;
        $totals['margin_percent'] = $totals['revenue'] > 0 ? ( $totals['net_profit'] / $totals['revenue'] ) * 100 : 0;
        $totals['top_products']   = WCPI_Report_Query::product_profitability( $from, $to, 10, 'profit_desc' );
        $totals['low_margin']     = WCPI_Report_Query::product_profitability( $from, $to, 10, 'margin_asc' );

        WCPI_Aggregates::set( 'dashboard', $cache_key, $from, $to, $totals, 900 );
        return $totals;
    }

    /**
     * Expenses total in range.
     *
     * @param string $from From.
     * @param string $to To.
     * @return float
     */
    public static function get_expenses_total( string $from, string $to ): float {
        global $wpdb;
        return (float) $wpdb->get_var(
            $wpdb->prepare(
                'SELECT SUM(amount) FROM ' . WCPI_DB::table( 'expenses' ) . ' WHERE expense_date BETWEEN %s AND %s',
                $from,
                $to
            )
        );
    }

    /**
     * Fetch orders for a local date.
     *
     * @param string $date Date Y-m-d.
     * @return array<int, WC_Order>
     */
    private static function get_orders_for_date( string $date ): array {
        $order_ids = wc_get_orders(
            array(
                'limit'        => -1,
                'return'       => 'ids',
                'status'       => array( 'processing', 'completed', 'on-hold' ),
                'date_created' => $date . '...' . $date,
            )
        );

        $orders = array();
        foreach ( $order_ids as $order_id ) {
            $order = wc_get_order( $order_id );
            if ( $order instanceof WC_Order ) {
                $orders[] = $order;
            }
        }

        return $orders;
    }
}

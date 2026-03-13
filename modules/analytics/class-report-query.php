<?php
/**
 * Analytics report SQL.
 *
 * @package WCPI
 */

defined( 'ABSPATH' ) || exit;

class WCPI_Report_Query {

    /**
     * Get product profitability rows.
     *
     * @param string $from From.
     * @param string $to To.
     * @param int    $limit Limit.
     * @param string $sort Sort key.
     * @return array<int, array<string, mixed>>
     */
    public static function product_profitability( string $from, string $to, int $limit = 20, string $sort = 'profit_desc' ): array {
        global $wpdb;

        $lookup_table = $wpdb->prefix . 'wc_order_product_lookup';
        $stats_table  = $wpdb->prefix . 'wc_order_stats';
        $costs_table  = WCPI_DB::table( 'product_costs' );

        if ( ! WCPI_Helpers::is_woocommerce_active() || $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $lookup_table ) ) !== $lookup_table ) {
            return array();
        }

        $revenue_expr = 'SUM(opl.product_net_revenue)';
        if ( 'yes' === WCPI_Settings_Manager::get( 'general', 'include_tax', 'no' ) ) {
            $revenue_expr = 'SUM(opl.product_net_revenue + opl.tax_amount)';
        }

        $sort_map = array(
            'profit_desc'   => 'profit DESC',
            'margin_asc'    => 'margin_percent ASC',
            'revenue_desc'  => 'revenue DESC',
            'qty_desc'      => 'quantity_sold DESC',
        );
        $order_by = $sort_map[ $sort ] ?? 'profit DESC';

        $sql = "
            SELECT
                opl.product_id,
                opl.variation_id,
                SUM(opl.product_qty) AS quantity_sold,
                {$revenue_expr} AS revenue,
                COALESCE(vc.total_unit_cost, pc.total_unit_cost, 0) AS unit_cost,
                SUM(opl.product_qty) * COALESCE(vc.total_unit_cost, pc.total_unit_cost, 0) AS cost,
                ({$revenue_expr} - (SUM(opl.product_qty) * COALESCE(vc.total_unit_cost, pc.total_unit_cost, 0))) AS profit,
                CASE
                    WHEN {$revenue_expr} > 0 THEN (({$revenue_expr} - (SUM(opl.product_qty) * COALESCE(vc.total_unit_cost, pc.total_unit_cost, 0))) / {$revenue_expr}) * 100
                    ELSE 0
                END AS margin_percent,
                CASE
                    WHEN SUM(opl.product_qty) > 0 THEN {$revenue_expr} / SUM(opl.product_qty)
                    ELSE 0
                END AS avg_selling_price
            FROM {$lookup_table} opl
            INNER JOIN {$stats_table} os ON os.order_id = opl.order_id
            LEFT JOIN {$costs_table} vc ON vc.product_id = opl.product_id AND vc.variation_id = opl.variation_id
            LEFT JOIN {$costs_table} pc ON pc.product_id = opl.product_id AND pc.variation_id = 0
            WHERE DATE(os.date_created) BETWEEN %s AND %s
                AND os.status IN ('wc-processing','wc-completed','wc-on-hold')
            GROUP BY opl.product_id, opl.variation_id, unit_cost
            ORDER BY {$order_by}
            LIMIT %d
        ";

        $rows = $wpdb->get_results(
            $wpdb->prepare( $sql, $from, $to, $limit ),
            ARRAY_A
        );

        foreach ( $rows as &$row ) {
            $product_obj = wc_get_product( (int) ( $row['variation_id'] ?: $row['product_id'] ) );
            $row['name'] = $product_obj ? $product_obj->get_name() : __( 'Unknown product', WCPI_TEXT_DOMAIN );
            $row['sku']  = $product_obj ? $product_obj->get_sku() : '';
        }

        return $rows;
    }

    /**
     * Expense breakdown by category.
     *
     * @param string $from From.
     * @param string $to To.
     * @return array<int, array<string, mixed>>
     */
    public static function expense_breakdown( string $from, string $to ): array {
        global $wpdb;
        $table = WCPI_DB::table( 'expenses' );
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT category, SUM(amount) as total
                FROM {$table}
                WHERE expense_date BETWEEN %s AND %s
                GROUP BY category
                ORDER BY total DESC",
                $from,
                $to
            ),
            ARRAY_A
        );
    }

    /**
     * Summary rows by date.
     *
     * @param string $from From.
     * @param string $to To.
     * @return array<int, array<string, mixed>>
     */
    public static function summaries( string $from, string $to ): array {
        global $wpdb;
        $table = WCPI_DB::table( 'daily_summary' );

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE summary_date BETWEEN %s AND %s ORDER BY summary_date ASC",
                $from,
                $to
            ),
            ARRAY_A
        );
    }
}

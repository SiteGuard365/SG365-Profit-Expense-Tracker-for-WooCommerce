<?php
/**
 * CSV export.
 *
 * @package WCPI
 */

defined( 'ABSPATH' ) || exit;

class WCPI_Export_CSV {

    /**
     * Generate CSV file.
     *
     * @param string $report Report key.
     * @param string $from From.
     * @param string $to To.
     * @return array<string, string>|WP_Error
     */
    public static function generate( string $report, string $from, string $to ) {
        $rows = self::build_rows( $report, $from, $to );
        if ( empty( $rows ) ) {
            return new WP_Error( 'wcpi_empty_export', __( 'No data available for export.', WCPI_TEXT_DOMAIN ) );
        }

        $handle = fopen( 'php://temp', 'r+' );
        foreach ( $rows as $row ) {
            fputcsv( $handle, $row );
        }
        rewind( $handle );
        $contents = stream_get_contents( $handle );
        fclose( $handle );

        $filename = WCPI_Filesystem::secure_filename( 'wcpi-' . $report, 'csv' );
        return WCPI_Filesystem::write_file( 'exports', $filename, $contents );
    }

    /**
     * Build report rows.
     *
     * @param string $report Report.
     * @param string $from From.
     * @param string $to To.
     * @return array<int, array<int, mixed>>
     */
    public static function build_rows( string $report, string $from, string $to ): array {
        switch ( $report ) {
            case 'dashboard_summary':
                $data = WCPI_Analytics_Engine::dashboard( $from, $to );
                return array(
                    array( 'From', $from ),
                    array( 'To', $to ),
                    array( 'Revenue', $data['revenue'] ),
                    array( 'Expenses', $data['expenses'] ),
                    array( 'Net Profit', $data['net_profit'] ),
                    array( 'Orders Count', $data['orders_count'] ),
                    array( 'Units Sold', $data['units_sold'] ),
                    array( 'AOV', $data['aov'] ),
                    array( 'Margin %', $data['margin_percent'] ),
                );

            case 'product_profit':
                $rows = array(
                    array( 'Product', 'SKU', 'Quantity Sold', 'Revenue', 'Cost', 'Profit', 'Margin %', 'Average Selling Price' ),
                );
                foreach ( WCPI_Report_Query::product_profitability( $from, $to, 200, 'profit_desc' ) as $item ) {
                    $rows[] = array(
                        $item['name'],
                        $item['sku'],
                        $item['quantity_sold'],
                        $item['revenue'],
                        $item['cost'],
                        $item['profit'],
                        $item['margin_percent'],
                        $item['avg_selling_price'],
                    );
                }
                return $rows;

            case 'expense_report':
                $query = WCPI_Expense_Manager::query(
                    array(
                        'from'     => $from,
                        'to'       => $to,
                        'per_page' => 5000,
                        'paged'    => 1,
                    )
                );
                $rows = array(
                    array( 'Name', 'Category', 'Amount', 'Date', 'Notes' ),
                );
                foreach ( $query['items'] as $item ) {
                    $rows[] = array(
                        $item['expense_name'],
                        $item['category'],
                        $item['amount'],
                        $item['expense_date'],
                        $item['notes'],
                    );
                }
                return $rows;

            case 'daily_summary':
            default:
                $rows = array(
                    array( 'Date', 'Orders', 'Items Sold', 'Revenue', 'Discounts', 'Refunds', 'Shipping', 'Tax', 'Product Cost', 'Expenses', 'Net Profit', 'Margin %' ),
                );
                foreach ( WCPI_Report_Query::summaries( $from, $to ) as $row ) {
                    $rows[] = array(
                        $row['summary_date'],
                        $row['orders_count'],
                        $row['items_sold'],
                        $row['gross_revenue'],
                        $row['discounts_total'],
                        $row['refunds_total'],
                        $row['shipping_collected'],
                        $row['tax_collected'],
                        $row['product_cost_total'],
                        $row['expenses_total'],
                        $row['net_profit'],
                        $row['margin_percent'],
                    );
                }
                return $rows;
        }
    }
}

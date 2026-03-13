<?php
defined( 'ABSPATH' ) || exit;

$cards = array(
    array( 'label' => __( 'Revenue Today', WCPI_TEXT_DOMAIN ), 'value' => WCPI_Helpers::format_price( (float) $today_data['revenue'] ) ),
    array( 'label' => __( 'Revenue This Month', WCPI_TEXT_DOMAIN ), 'value' => WCPI_Helpers::format_price( (float) $month_data['revenue'] ) ),
    array( 'label' => __( 'Expenses Today', WCPI_TEXT_DOMAIN ), 'value' => WCPI_Helpers::format_price( (float) $today_data['expenses'] ) ),
    array( 'label' => __( 'Expenses This Month', WCPI_TEXT_DOMAIN ), 'value' => WCPI_Helpers::format_price( (float) $month_data['expenses'] ) ),
    array( 'label' => __( 'Revenue (Selected Range)', WCPI_TEXT_DOMAIN ), 'value' => WCPI_Helpers::format_price( (float) $data['revenue'] ) ),
    array( 'label' => __( 'Gross Profit', WCPI_TEXT_DOMAIN ), 'value' => WCPI_Helpers::format_price( (float) ( $data['revenue'] - $data['product_cost'] ) ) ),
    array( 'label' => __( 'Net Profit', WCPI_TEXT_DOMAIN ), 'value' => WCPI_Helpers::format_price( (float) $data['net_profit'] ), 'class' => ( (float) $data['net_profit'] >= 0 ? 'wcpi-positive-card' : 'wcpi-negative-card' ) ),
    array( 'label' => __( 'Margin %', WCPI_TEXT_DOMAIN ), 'value' => esc_html( number_format_i18n( (float) $data['margin_percent'], 2 ) . '%' ) ),
    array( 'label' => __( 'Orders', WCPI_TEXT_DOMAIN ), 'value' => esc_html( number_format_i18n( (int) $data['orders_count'] ) ) ),
    array( 'label' => __( 'Units Sold', WCPI_TEXT_DOMAIN ), 'value' => esc_html( number_format_i18n( (int) $data['units_sold'] ) ) ),
    array( 'label' => __( 'Average Order Value', WCPI_TEXT_DOMAIN ), 'value' => WCPI_Helpers::format_price( (float) $data['aov'] ) ),
    array( 'label' => __( 'Refunds', WCPI_TEXT_DOMAIN ), 'value' => WCPI_Helpers::format_price( (float) $data['refunds_total'] ) ),
);
$context = 'dashboard';
$default_preset = WCPI_Settings_Manager::get( 'general', 'default_dashboard_range', 'last_30_days' );
$default_from = $range['from'];
$default_to = $range['to'];
?>
<div class="wrap wcpi-wrap" id="wcpi-dashboard-page">
    <h1><?php esc_html_e( 'Profit Dashboard', WCPI_TEXT_DOMAIN ); ?></h1>

    <?php include WCPI_PLUGIN_DIR . 'templates/partials/filters.php'; ?>
    <?php include WCPI_PLUGIN_DIR . 'templates/partials/summary-cards.php'; ?>

    <div class="wcpi-grid wcpi-two-col">
        <?php
        $title = __( 'Revenue vs Net Profit', WCPI_TEXT_DOMAIN );
        $canvas_id = 'wcpiRevenueProfitChart';
        $height = '330px';
        include WCPI_PLUGIN_DIR . 'templates/partials/chart-block.php';

        $title = __( 'Margin Trend', WCPI_TEXT_DOMAIN );
        $canvas_id = 'wcpiMarginChart';
        include WCPI_PLUGIN_DIR . 'templates/partials/chart-block.php';
        ?>
    </div>

    <div class="wcpi-grid wcpi-two-col">
        <?php
        $title = __( 'Expense Breakdown', WCPI_TEXT_DOMAIN );
        $canvas_id = 'wcpiExpenseDonutChart';
        $height = '300px';
        include WCPI_PLUGIN_DIR . 'templates/partials/chart-block.php';

        $title = __( 'Sales Trend', WCPI_TEXT_DOMAIN );
        $canvas_id = 'wcpiSalesTrendChart';
        include WCPI_PLUGIN_DIR . 'templates/partials/chart-block.php';
        ?>
    </div>

    <div class="wcpi-grid wcpi-two-col">
        <div class="wcpi-card">
            <div class="wcpi-card-header"><h2><?php esc_html_e( 'Top Profitable Products', WCPI_TEXT_DOMAIN ); ?></h2></div>
            <div id="wcpi-top-products">
                <?php $rows = $data['top_products']; include WCPI_PLUGIN_DIR . 'templates/partials/table-top-products.php'; ?>
            </div>
        </div>
        <div class="wcpi-card">
            <div class="wcpi-card-header"><h2><?php esc_html_e( 'Low Margin Products', WCPI_TEXT_DOMAIN ); ?></h2></div>
            <div id="wcpi-low-margin">
                <?php $rows = $data['low_margin']; include WCPI_PLUGIN_DIR . 'templates/partials/table-low-margin.php'; ?>
            </div>
        </div>
    </div>

    <script>
        window.wcpiDashboardBootstrap = <?php echo wp_json_encode( $data ); ?>;
    </script>
</div>

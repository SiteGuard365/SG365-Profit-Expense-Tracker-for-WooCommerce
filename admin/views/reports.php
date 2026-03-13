<?php
defined( 'ABSPATH' ) || exit;
$context = 'reports';
$default_preset = 'last_30_days';
$default_from = '';
$default_to = '';
?>
<div class="wrap wcpi-wrap" id="wcpi-reports-page">
    <h1><?php esc_html_e( 'Reports', WCPI_TEXT_DOMAIN ); ?></h1>
    <?php include WCPI_PLUGIN_DIR . 'templates/partials/filters.php'; ?>

    <div class="wcpi-grid wcpi-two-col">
        <div class="wcpi-card">
            <div class="wcpi-card-header"><h2><?php esc_html_e( 'Top Profitable Products', WCPI_TEXT_DOMAIN ); ?></h2></div>
            <div id="wcpi-report-top-products"><p><?php esc_html_e( 'Apply filters to load report.', WCPI_TEXT_DOMAIN ); ?></p></div>
        </div>
        <div class="wcpi-card">
            <div class="wcpi-card-header"><h2><?php esc_html_e( 'Low Margin Products', WCPI_TEXT_DOMAIN ); ?></h2></div>
            <div id="wcpi-report-low-margin"><p><?php esc_html_e( 'Apply filters to load report.', WCPI_TEXT_DOMAIN ); ?></p></div>
        </div>
    </div>

    <div class="wcpi-grid wcpi-two-col">
        <div class="wcpi-card">
            <div class="wcpi-card-header"><h2><?php esc_html_e( 'Highest Revenue Products', WCPI_TEXT_DOMAIN ); ?></h2></div>
            <div id="wcpi-report-high-revenue"><p><?php esc_html_e( 'Apply filters to load report.', WCPI_TEXT_DOMAIN ); ?></p></div>
        </div>
        <div class="wcpi-card">
            <div class="wcpi-card-header"><h2><?php esc_html_e( 'Most Sold Products', WCPI_TEXT_DOMAIN ); ?></h2></div>
            <div id="wcpi-report-most-sold"><p><?php esc_html_e( 'Apply filters to load report.', WCPI_TEXT_DOMAIN ); ?></p></div>
        </div>
    </div>

    <div class="wcpi-card">
        <div class="wcpi-card-header"><h2><?php esc_html_e( 'Daily Summary', WCPI_TEXT_DOMAIN ); ?></h2></div>
        <div id="wcpi-report-summary"></div>
    </div>
</div>

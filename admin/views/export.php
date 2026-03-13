<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap wcpi-wrap">
    <h1><?php esc_html_e( 'Export', WCPI_TEXT_DOMAIN ); ?></h1>

    <?php if ( ! empty( $_GET['generated'] ) ) : ?>
        <div class="notice notice-success">
            <p>
                <?php esc_html_e( 'Export generated successfully.', WCPI_TEXT_DOMAIN ); ?>
                <?php if ( ! empty( $_GET['file'] ) ) : ?>
                    <a href="<?php echo esc_url( rawurldecode( sanitize_text_field( wp_unslash( $_GET['file'] ) ) ) ); ?>" class="button button-secondary" target="_blank" rel="noopener"><?php esc_html_e( 'Download File', WCPI_TEXT_DOMAIN ); ?></a>
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="wcpi-card">
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="wcpi_generate_export">
            <?php WCPI_Security::nonce_field( 'wcpi_generate_export' ); ?>
            <table class="form-table">
                <tr><th><label for="report"><?php esc_html_e( 'Report', WCPI_TEXT_DOMAIN ); ?></label></th><td><select name="report" id="report"><option value="dashboard_summary"><?php esc_html_e( 'Dashboard Summary', WCPI_TEXT_DOMAIN ); ?></option><option value="product_profit"><?php esc_html_e( 'Product Profit Report', WCPI_TEXT_DOMAIN ); ?></option><option value="expense_report"><?php esc_html_e( 'Expense Report', WCPI_TEXT_DOMAIN ); ?></option><option value="daily_summary"><?php esc_html_e( 'Daily Summary Report', WCPI_TEXT_DOMAIN ); ?></option></select></td></tr>
                <tr><th><label for="format"><?php esc_html_e( 'Format', WCPI_TEXT_DOMAIN ); ?></label></th><td><select name="format" id="format"><option value="csv">CSV</option><option value="pdf">PDF</option></select></td></tr>
                <tr><th><label for="preset"><?php esc_html_e( 'Date Preset', WCPI_TEXT_DOMAIN ); ?></label></th><td><select name="preset" id="preset"><?php foreach ( WCPI_Helpers::date_presets() as $key => $label ) : ?><option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select></td></tr>
                <tr><th><label for="from"><?php esc_html_e( 'From', WCPI_TEXT_DOMAIN ); ?></label></th><td><input type="date" name="from" id="from"></td></tr>
                <tr><th><label for="to"><?php esc_html_e( 'To', WCPI_TEXT_DOMAIN ); ?></label></th><td><input type="date" name="to" id="to"></td></tr>
            </table>
            <p><button class="button button-primary"><?php esc_html_e( 'Generate Export', WCPI_TEXT_DOMAIN ); ?></button></p>
        </form>
    </div>
</div>

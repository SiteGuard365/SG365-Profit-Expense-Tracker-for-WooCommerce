<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap wcpi-wrap" id="wcpi-diagnostics-page">
    <h1><?php esc_html_e( 'Diagnostics', WCPI_TEXT_DOMAIN ); ?></h1>

    <div class="wcpi-grid wcpi-two-col">
        <div class="wcpi-card">
            <div class="wcpi-card-header"><h2><?php esc_html_e( 'System Health', WCPI_TEXT_DOMAIN ); ?></h2></div>
            <ul class="wcpi-list">
                <li><strong><?php esc_html_e( 'WooCommerce', WCPI_TEXT_DOMAIN ); ?>:</strong> <?php echo WCPI_Helpers::is_woocommerce_active() ? esc_html__( 'Active', WCPI_TEXT_DOMAIN ) : esc_html__( 'Missing', WCPI_TEXT_DOMAIN ); ?></li>
                <li><strong><?php esc_html_e( 'Upload Directory', WCPI_TEXT_DOMAIN ); ?>:</strong> <?php echo esc_html( $base['dir'] ); ?></li>
                <li><strong><?php esc_html_e( 'Writable', WCPI_TEXT_DOMAIN ); ?>:</strong> <?php echo wp_kses_post( wp_is_writable( $base['dir'] ) ? '<span class="wcpi-positive">Yes</span>' : '<span class="wcpi-negative">No</span>' ); ?></li>
                <li><strong><?php esc_html_e( 'Last Sync Status', WCPI_TEXT_DOMAIN ); ?>:</strong> <?php echo esc_html( WCPI_Settings_Manager::get( 'general', 'last_sync_status', __( 'No sync logged yet.', WCPI_TEXT_DOMAIN ) ) ); ?></li>
            </ul>
        </div>

        <div class="wcpi-card">
            <div class="wcpi-card-header"><h2><?php esc_html_e( 'Maintenance Tools', WCPI_TEXT_DOMAIN ); ?></h2></div>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="wcpi-stack">
                <input type="hidden" name="action" value="wcpi_maintenance">
                <?php WCPI_Security::nonce_field( 'wcpi_maintenance' ); ?>
                <button name="maintenance_action" value="clear_cache" class="button"><?php esc_html_e( 'Clear Cache', WCPI_TEXT_DOMAIN ); ?></button>
                <button name="maintenance_action" value="clear_exports" class="button"><?php esc_html_e( 'Clear Exports', WCPI_TEXT_DOMAIN ); ?></button>
                <button name="maintenance_action" value="clear_logs" class="button"><?php esc_html_e( 'Clear Logs', WCPI_TEXT_DOMAIN ); ?></button>
                <button name="maintenance_action" value="rebuild_last_30" class="button button-primary"><?php esc_html_e( 'Rebuild Last 30 Days', WCPI_TEXT_DOMAIN ); ?></button>
                <button name="maintenance_action" value="create_backup" class="button"><?php esc_html_e( 'Create Backup Snapshot', WCPI_TEXT_DOMAIN ); ?></button>
                <button name="maintenance_action" value="optimize_tables" class="button"><?php esc_html_e( 'Optimize Tables', WCPI_TEXT_DOMAIN ); ?></button>
            </form>
        </div>
    </div>

    <div class="wcpi-card">
        <div class="wcpi-card-header"><h2><?php esc_html_e( 'Table Health', WCPI_TEXT_DOMAIN ); ?></h2></div>
        <table class="widefat striped wcpi-table">
            <thead><tr><th><?php esc_html_e( 'Key', WCPI_TEXT_DOMAIN ); ?></th><th><?php esc_html_e( 'Table', WCPI_TEXT_DOMAIN ); ?></th><th><?php esc_html_e( 'Exists', WCPI_TEXT_DOMAIN ); ?></th><th><?php esc_html_e( 'Rows', WCPI_TEXT_DOMAIN ); ?></th></tr></thead>
            <tbody>
                <?php foreach ( $tables as $key => $table ) : ?>
                    <tr>
                        <td><?php echo esc_html( $key ); ?></td>
                        <td><code><?php echo esc_html( $table['name'] ); ?></code></td>
                        <td><?php echo wp_kses_post( $table['exists'] ? '<span class="wcpi-positive">Yes</span>' : '<span class="wcpi-negative">No</span>' ); ?></td>
                        <td><?php echo esc_html( number_format_i18n( $table['rows'] ) ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="wcpi-card">
        <div class="wcpi-card-header"><h2><?php esc_html_e( 'Recent Logs', WCPI_TEXT_DOMAIN ); ?></h2></div>
        <table class="widefat striped wcpi-table">
            <thead><tr><th><?php esc_html_e( 'Date', WCPI_TEXT_DOMAIN ); ?></th><th><?php esc_html_e( 'Level', WCPI_TEXT_DOMAIN ); ?></th><th><?php esc_html_e( 'Context', WCPI_TEXT_DOMAIN ); ?></th><th><?php esc_html_e( 'Message', WCPI_TEXT_DOMAIN ); ?></th></tr></thead>
            <tbody>
                <?php if ( empty( $logs ) ) : ?>
                    <tr><td colspan="4"><?php esc_html_e( 'No logs available.', WCPI_TEXT_DOMAIN ); ?></td></tr>
                <?php else : ?>
                    <?php foreach ( $logs as $log ) : ?>
                        <tr>
                            <td><?php echo esc_html( $log['created_at'] ); ?></td>
                            <td><?php echo esc_html( strtoupper( $log['log_level'] ) ); ?></td>
                            <td><?php echo esc_html( $log['context'] ); ?></td>
                            <td><?php echo esc_html( $log['message'] ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php $log_total_pages = (int) max( 1, ceil( $log_count / $per_page ) ); ?>
        <?php if ( $log_total_pages > 1 ) : ?>
            <div class="tablenav"><div class="tablenav-pages"><?php
                echo wp_kses_post(
                    paginate_links(
                        array(
                            'base'      => add_query_arg( array( 'log_paged' => '%#%' ) ),
                            'format'    => '',
                            'current'   => $log_page,
                            'total'     => $log_total_pages,
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                        )
                    )
                );
            ?></div></div>
        <?php endif; ?>
        <p class="description"><?php echo esc_html( sprintf( __( '%d total log entries.', WCPI_TEXT_DOMAIN ), $log_count ) ); ?></p>
    </div>
</div>

<?php
defined( 'ABSPATH' ) || exit;
$edit = $edit ?: array(
    'id' => 0,
    'expense_name' => '',
    'category' => 'miscellaneous',
    'amount' => '',
    'expense_date' => gmdate( 'Y-m-d' ),
    'notes' => '',
    'attachment_path' => '',
);
?>
<div class="wrap wcpi-wrap">
    <h1><?php esc_html_e( 'Expenses', WCPI_TEXT_DOMAIN ); ?></h1>

    <?php if ( isset( $_GET['updated'] ) ) : ?>
        <div class="notice notice-success"><p><?php esc_html_e( 'Expense saved.', WCPI_TEXT_DOMAIN ); ?></p></div>
    <?php endif; ?>
    <?php if ( isset( $_GET['deleted'] ) ) : ?>
        <div class="notice notice-success"><p><?php esc_html_e( 'Expense deleted.', WCPI_TEXT_DOMAIN ); ?></p></div>
    <?php endif; ?>

    <div class="wcpi-grid wcpi-two-col">
        <div class="wcpi-card">
            <div class="wcpi-card-header"><h2><?php echo $edit['id'] ? esc_html__( 'Edit Expense', WCPI_TEXT_DOMAIN ) : esc_html__( 'Add Expense', WCPI_TEXT_DOMAIN ); ?></h2></div>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="wcpi_save_expense">
                <input type="hidden" name="expense_id" value="<?php echo esc_attr( $edit['id'] ); ?>">
                <input type="hidden" name="existing_attachment" value="<?php echo esc_attr( $edit['attachment_path'] ); ?>">
                <?php WCPI_Security::nonce_field( 'wcpi_save_expense' ); ?>
                <table class="form-table">
                    <tr><th><label for="expense_name"><?php esc_html_e( 'Expense Name', WCPI_TEXT_DOMAIN ); ?></label></th><td><input class="regular-text" type="text" name="expense_name" id="expense_name" value="<?php echo esc_attr( $edit['expense_name'] ); ?>" required></td></tr>
                    <tr><th><label for="category"><?php esc_html_e( 'Category', WCPI_TEXT_DOMAIN ); ?></label></th><td><select name="category" id="category"><?php foreach ( WCPI_Helpers::expense_categories() as $key => $label ) : ?><option value="<?php echo esc_attr( $key ); ?>" <?php selected( $edit['category'], $key ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select></td></tr>
                    <tr><th><label for="amount"><?php esc_html_e( 'Amount', WCPI_TEXT_DOMAIN ); ?></label></th><td><input type="number" step="0.000001" min="0" name="amount" id="amount" value="<?php echo esc_attr( $edit['amount'] ); ?>" required></td></tr>
                    <tr><th><label for="expense_date"><?php esc_html_e( 'Expense Date', WCPI_TEXT_DOMAIN ); ?></label></th><td><input type="date" name="expense_date" id="expense_date" value="<?php echo esc_attr( $edit['expense_date'] ); ?>" required></td></tr>
                    <tr><th><label for="notes"><?php esc_html_e( 'Notes', WCPI_TEXT_DOMAIN ); ?></label></th><td><textarea name="notes" id="notes" rows="4" class="large-text"><?php echo esc_textarea( $edit['notes'] ); ?></textarea></td></tr>
                    <tr><th><label for="attachment"><?php esc_html_e( 'Receipt Attachment', WCPI_TEXT_DOMAIN ); ?></label></th><td><input type="file" name="attachment" id="attachment" accept=".pdf,.jpg,.jpeg,.png,.webp"><?php if ( ! empty( $edit['attachment_path'] ) ) : ?><p class="description"><?php echo esc_html( basename( $edit['attachment_path'] ) ); ?></p><?php endif; ?></td></tr>
                </table>
                <p><button class="button button-primary"><?php esc_html_e( 'Save Expense', WCPI_TEXT_DOMAIN ); ?></button></p>
            </form>
        </div>

        <div class="wcpi-card">
            <div class="wcpi-card-header"><h2><?php esc_html_e( 'Category Totals', WCPI_TEXT_DOMAIN ); ?></h2></div>
            <ul class="wcpi-list">
                <?php foreach ( $query['category_sum'] as $row ) : ?>
                    <li><strong><?php echo esc_html( WCPI_Helpers::expense_categories()[ $row['category'] ] ?? $row['category'] ); ?>:</strong> <?php echo wp_kses_post( WCPI_Helpers::format_price( (float) $row['total'] ) ); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <form method="get" class="wcpi-card wcpi-filter-bar">
        <input type="hidden" name="page" value="wcpi-expenses">
        <label><span><?php esc_html_e( 'Search', WCPI_TEXT_DOMAIN ); ?></span><input type="search" name="s" value="<?php echo esc_attr( $filters['search'] ); ?>"></label>
        <label><span><?php esc_html_e( 'Category', WCPI_TEXT_DOMAIN ); ?></span><select name="category"><option value=""><?php esc_html_e( 'All', WCPI_TEXT_DOMAIN ); ?></option><?php foreach ( WCPI_Helpers::expense_categories() as $key => $label ) : ?><option value="<?php echo esc_attr( $key ); ?>" <?php selected( $filters['category'], $key ); ?>><?php echo esc_html( $label ); ?></option><?php endforeach; ?></select></label>
        <label><span><?php esc_html_e( 'From', WCPI_TEXT_DOMAIN ); ?></span><input type="date" name="from" value="<?php echo esc_attr( $filters['from'] ); ?>"></label>
        <label><span><?php esc_html_e( 'To', WCPI_TEXT_DOMAIN ); ?></span><input type="date" name="to" value="<?php echo esc_attr( $filters['to'] ); ?>"></label>
        <button class="button button-primary"><?php esc_html_e( 'Filter', WCPI_TEXT_DOMAIN ); ?></button>
    </form>

    <div class="wcpi-card">
        <table class="widefat striped wcpi-table">
            <thead><tr><th><?php esc_html_e( 'Name', WCPI_TEXT_DOMAIN ); ?></th><th><?php esc_html_e( 'Category', WCPI_TEXT_DOMAIN ); ?></th><th><?php esc_html_e( 'Amount', WCPI_TEXT_DOMAIN ); ?></th><th><?php esc_html_e( 'Date', WCPI_TEXT_DOMAIN ); ?></th><th><?php esc_html_e( 'Actions', WCPI_TEXT_DOMAIN ); ?></th></tr></thead>
            <tbody>
                <?php if ( empty( $query['items'] ) ) : ?>
                    <tr><td colspan="5"><?php esc_html_e( 'No expenses found.', WCPI_TEXT_DOMAIN ); ?></td></tr>
                <?php else : ?>
                    <?php foreach ( $query['items'] as $item ) : ?>
                        <tr>
                            <td><?php echo esc_html( $item['expense_name'] ); ?><br><small><?php echo esc_html( wp_trim_words( $item['notes'], 10 ) ); ?></small></td>
                            <td><?php echo esc_html( WCPI_Helpers::expense_categories()[ $item['category'] ] ?? $item['category'] ); ?></td>
                            <td><?php echo wp_kses_post( WCPI_Helpers::format_price( (float) $item['amount'] ) ); ?></td>
                            <td><?php echo esc_html( $item['expense_date'] ); ?></td>
                            <td>
                                <a class="button button-small" href="<?php echo esc_url( admin_url( 'admin.php?page=wcpi-expenses&edit_expense=' . absint( $item['id'] ) ) ); ?>"><?php esc_html_e( 'Edit', WCPI_TEXT_DOMAIN ); ?></a>
                                <a class="button button-small" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=wcpi_delete_expense&expense_id=' . absint( $item['id'] ) ), 'wcpi_delete_expense', '_wcpi_nonce' ) ); ?>" onclick="return confirm('<?php echo esc_js( __( 'Delete this expense?', WCPI_TEXT_DOMAIN ) ); ?>');"><?php esc_html_e( 'Delete', WCPI_TEXT_DOMAIN ); ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ( $query['total_pages'] > 1 ) : ?>
            <div class="tablenav"><div class="tablenav-pages"><?php
                echo wp_kses_post(
                    paginate_links(
                        array(
                            'base'      => add_query_arg( array( 'paged' => '%#%' ) ),
                            'format'    => '',
                            'current'   => $filters['paged'],
                            'total'     => $query['total_pages'],
                            'prev_text' => '&laquo;',
                            'next_text' => '&raquo;',
                        )
                    )
                );
            ?></div></div>
        <?php endif; ?>
    </div>
</div>

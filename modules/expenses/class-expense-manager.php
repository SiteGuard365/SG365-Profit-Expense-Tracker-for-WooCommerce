<?php
/**
 * Expense manager.
 *
 * @package WCPI
 */

defined( 'ABSPATH' ) || exit;

class WCPI_Expense_Manager {

    /**
     * Register hooks.
     *
     * @return void
     */
    public function register(): void {
        add_action( 'admin_post_wcpi_save_expense', array( $this, 'handle_save' ) );
        add_action( 'admin_post_wcpi_delete_expense', array( $this, 'handle_delete' ) );
    }

    /**
     * Save expense.
     *
     * @return void
     */
    public function handle_save(): void {
        WCPI_Security::verify_access();
        WCPI_Security::verify_nonce( 'wcpi_save_expense' );

        global $wpdb;
        $table = WCPI_DB::table( 'expenses' );
        $id    = absint( $_POST['expense_id'] ?? 0 );

        $attachment_path = WCPI_Helpers::get( $_POST, 'existing_attachment', '' );
        if ( ! empty( $_FILES['attachment']['name'] ) ) {
            $upload = $this->handle_receipt_upload();
            if ( ! is_wp_error( $upload ) ) {
                $attachment_path = $upload['file'];
            }
        }

        $data = array(
            'expense_name'    => WCPI_Security::text( $_POST['expense_name'] ?? '' ),
            'category'        => sanitize_key( $_POST['category'] ?? 'miscellaneous' ),
            'amount'          => WCPI_Security::decimal( $_POST['amount'] ?? 0 ),
            'expense_date'    => WCPI_Security::date( $_POST['expense_date'] ?? gmdate( 'Y-m-d' ) ),
            'notes'           => WCPI_Security::textarea( $_POST['notes'] ?? '' ),
            'attachment_path' => sanitize_text_field( $attachment_path ),
            'created_by'      => get_current_user_id(),
            'updated_at'      => current_time( 'mysql', true ),
        );

        if ( ! array_key_exists( $data['category'], WCPI_Helpers::expense_categories() ) ) {
            $data['category'] = 'miscellaneous';
        }

        if ( $id > 0 ) {
            $wpdb->update(
                $table,
                $data,
                array( 'id' => $id ),
                array( '%s', '%s', '%f', '%s', '%s', '%s', '%d', '%s' ),
                array( '%d' )
            );
        } else {
            $data['created_at'] = current_time( 'mysql', true );
            $wpdb->insert(
                $table,
                $data,
                array( '%s', '%s', '%f', '%s', '%s', '%s', '%d', '%s', '%s' )
            );
        }

        WCPI_Daily_Summary::rebuild_range( $data['expense_date'], $data['expense_date'] );
        wp_safe_redirect( admin_url( 'admin.php?page=wcpi-expenses&updated=1' ) );
        exit;
    }

    /**
     * Delete expense.
     *
     * @return void
     */
    public function handle_delete(): void {
        WCPI_Security::verify_access();
        WCPI_Security::verify_nonce( 'wcpi_delete_expense' );
        global $wpdb;
        $table = WCPI_DB::table( 'expenses' );
        $id    = absint( $_GET['expense_id'] ?? 0 );

        $expense_date = $wpdb->get_var( $wpdb->prepare( "SELECT expense_date FROM {$table} WHERE id = %d", $id ) );

        $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );

        if ( $expense_date ) {
            WCPI_Daily_Summary::rebuild_range( $expense_date, $expense_date );
        }

        wp_safe_redirect( admin_url( 'admin.php?page=wcpi-expenses&deleted=1' ) );
        exit;
    }

    /**
     * Query expenses.
     *
     * @param array<string, mixed> $args Filters.
     * @return array<string, mixed>
     */
    public static function query( array $args = array() ): array {
        global $wpdb;
        $table    = WCPI_DB::table( 'expenses' );
        $page     = max( 1, absint( $args['paged'] ?? 1 ) );
        $per_page = max( 1, absint( $args['per_page'] ?? 20 ) );
        $offset   = ( $page - 1 ) * $per_page;
        $where    = array( '1=1' );
        $values   = array();

        if ( ! empty( $args['category'] ) ) {
            $where[]  = 'category = %s';
            $values[] = sanitize_key( $args['category'] );
        }
        if ( ! empty( $args['search'] ) ) {
            $where[]  = '(expense_name LIKE %s OR notes LIKE %s)';
            $term     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $values[] = $term;
            $values[] = $term;
        }
        if ( ! empty( $args['from'] ) ) {
            $where[]  = 'expense_date >= %s';
            $values[] = $args['from'];
        }
        if ( ! empty( $args['to'] ) ) {
            $where[]  = 'expense_date <= %s';
            $values[] = $args['to'];
        }

        $sql_where = implode( ' AND ', $where );

        $rows_sql  = "SELECT * FROM {$table} WHERE {$sql_where} ORDER BY expense_date DESC, id DESC LIMIT %d OFFSET %d";
        $count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$sql_where}";
        $sum_sql   = "SELECT category, SUM(amount) AS total FROM {$table} WHERE {$sql_where} GROUP BY category";

        $rows_params = array_merge( $values, array( $per_page, $offset ) );
        $items       = $wpdb->get_results( $wpdb->prepare( $rows_sql, ...$rows_params ), ARRAY_A );

        if ( ! empty( $values ) ) {
            $total_items  = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, ...$values ) );
            $category_sum = $wpdb->get_results( $wpdb->prepare( $sum_sql, ...$values ), ARRAY_A );
        } else {
            $total_items  = (int) $wpdb->get_var( $count_sql ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
            $category_sum = $wpdb->get_results( $sum_sql, ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
        }

        return array(
            'items'        => $items,
            'total_items'  => $total_items,
            'total_pages'  => (int) ceil( $total_items / $per_page ),
            'category_sum' => $category_sum,
        );
    }

    /**
     * Get expense by id.
     *
     * @param int $id Expense id.
     * @return array<string, mixed>|null
     */
    public static function get( int $id ): ?array {
        global $wpdb;
        $row = $wpdb->get_row(
            $wpdb->prepare( 'SELECT * FROM ' . WCPI_DB::table( 'expenses' ) . ' WHERE id = %d', $id ),
            ARRAY_A
        );
        return $row ?: null;
    }

    /**
     * Handle receipt upload.
     *
     * @return array<string, mixed>|WP_Error
     */
    private function handle_receipt_upload() {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        WCPI_Filesystem::ensure_base_structure();

        $overrides = array(
            'test_form' => false,
            'mimes'     => array(
                'jpg'  => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png'  => 'image/png',
                'webp' => 'image/webp',
                'pdf'  => 'application/pdf',
            ),
            'unique_filename_callback' => static function ( $dir, $name, $ext ) {
                return WCPI_Filesystem::secure_filename( 'receipt', $ext );
            },
        );

        return wp_handle_upload( $_FILES['attachment'], $overrides );
    }
}

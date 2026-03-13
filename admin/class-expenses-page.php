<?php
/**
 * Expenses page.
 *
 * @package WCPI
 */

defined( 'ABSPATH' ) || exit;

class WCPI_Expenses_Page {

    /**
     * Render page.
     *
     * @return void
     */
    public static function render(): void {
        WCPI_Security::verify_access();

        $filters = array(
            'category' => WCPI_Security::text( $_GET['category'] ?? '' ),
            'search'   => WCPI_Security::text( $_GET['s'] ?? '' ),
            'from'     => WCPI_Security::text( $_GET['from'] ?? '' ),
            'to'       => WCPI_Security::text( $_GET['to'] ?? '' ),
            'paged'    => max( 1, absint( $_GET['paged'] ?? 1 ) ),
            'per_page' => 20,
        );

        $query = WCPI_Expense_Manager::query( $filters );
        $edit  = absint( $_GET['edit_expense'] ?? 0 ) ? WCPI_Expense_Manager::get( absint( $_GET['edit_expense'] ) ) : null;

        include WCPI_PLUGIN_DIR . 'admin/views/expenses.php';
    }
}

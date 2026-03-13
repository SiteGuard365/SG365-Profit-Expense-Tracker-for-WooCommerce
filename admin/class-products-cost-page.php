<?php
/**
 * Product costs page.
 *
 * @package WCPI
 */

defined( 'ABSPATH' ) || exit;

class WCPI_Products_Cost_Page {

    /**
     * Render page.
     *
     * @return void
     */
    public static function render(): void {
        WCPI_Security::verify_access();
        $page     = max( 1, absint( $_GET['paged'] ?? 1 ) );
        $per_page = 20;
        $search   = WCPI_Security::text( $_GET['s'] ?? '' );
        $type     = WCPI_Security::text( $_GET['product_type'] ?? '' );

        $args = array(
            'status' => array( 'publish', 'private' ),
            'limit'  => $per_page,
            'page'   => $page,
            'return' => 'objects',
        );

        if ( $search ) {
            $args['search'] = '*' . $search . '*';
        }

        if ( $type ) {
            $args['type'] = $type;
        }

        $products = wc_get_products( $args );

        $count_args = $args;
        $count_args['limit']  = -1;
        $count_args['page']   = 1;
        $count_args['return'] = 'ids';

        $product_ids  = wc_get_products( $count_args );
        $total_count  = is_array( $product_ids ) ? count( $product_ids ) : 0;
        $total_pages  = (int) max( 1, ceil( $total_count / $per_page ) );

        include WCPI_PLUGIN_DIR . 'admin/views/product-costs.php';
    }
}

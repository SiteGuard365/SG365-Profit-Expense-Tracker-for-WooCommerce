<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap wcpi-wrap">
    <h1><?php esc_html_e( 'Product Costs', WCPI_TEXT_DOMAIN ); ?></h1>

    <?php if ( isset( $_GET['updated'] ) ) : ?>
        <div class="notice notice-success"><p><?php esc_html_e( 'Product costs updated.', WCPI_TEXT_DOMAIN ); ?></p></div>
    <?php endif; ?>

    <form method="get" class="wcpi-card wcpi-filter-bar">
        <input type="hidden" name="page" value="wcpi-product-costs">
        <label>
            <span><?php esc_html_e( 'Search products', WCPI_TEXT_DOMAIN ); ?></span>
            <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>">
        </label>
        <label>
            <span><?php esc_html_e( 'Product type', WCPI_TEXT_DOMAIN ); ?></span>
            <select name="product_type">
                <option value=""><?php esc_html_e( 'All', WCPI_TEXT_DOMAIN ); ?></option>
                <option value="simple" <?php selected( $type, 'simple' ); ?>><?php esc_html_e( 'Simple', WCPI_TEXT_DOMAIN ); ?></option>
                <option value="variable" <?php selected( $type, 'variable' ); ?>><?php esc_html_e( 'Variable', WCPI_TEXT_DOMAIN ); ?></option>
            </select>
        </label>
        <button class="button button-primary"><?php esc_html_e( 'Filter', WCPI_TEXT_DOMAIN ); ?></button>
    </form>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="wcpi_save_product_costs_page">
        <?php WCPI_Security::nonce_field( 'wcpi_save_product_costs_page' ); ?>

        <div class="wcpi-card">
            <table class="widefat striped wcpi-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Product', WCPI_TEXT_DOMAIN ); ?></th>
                        <th><?php esc_html_e( 'SKU', WCPI_TEXT_DOMAIN ); ?></th>
                        <th><?php esc_html_e( 'Cost Price', WCPI_TEXT_DOMAIN ); ?></th>
                        <th><?php esc_html_e( 'Packaging', WCPI_TEXT_DOMAIN ); ?></th>
                        <th><?php esc_html_e( 'Handling', WCPI_TEXT_DOMAIN ); ?></th>
                        <th><?php esc_html_e( 'Extra', WCPI_TEXT_DOMAIN ); ?></th>
                        <th><?php esc_html_e( 'Total', WCPI_TEXT_DOMAIN ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $products ) ) : ?>
                        <tr><td colspan="7"><?php esc_html_e( 'No products found.', WCPI_TEXT_DOMAIN ); ?></td></tr>
                    <?php else : ?>
                        <?php foreach ( $products as $product ) : ?>
                            <?php
                            $product_id = $product->get_id();
                            $row = WCPI_Product_Cost_Manager::get_cost_row( $product_id, 0 );
                            ?>
                            <tr>
                                <td>
                                    <?php echo esc_html( $product->get_name() ); ?>
                                    <input type="hidden" name="product_id[]" value="<?php echo esc_attr( $product_id ); ?>">
                                    <input type="hidden" name="variation_id[]" value="0">
                                </td>
                                <td><?php echo esc_html( $product->get_sku() ); ?></td>
                                <td><input class="small-text" type="number" step="0.000001" name="cost_price[]" value="<?php echo esc_attr( $row['cost_price'] ); ?>"></td>
                                <td><input class="small-text" type="number" step="0.000001" name="packaging_cost[]" value="<?php echo esc_attr( $row['packaging_cost'] ); ?>"></td>
                                <td><input class="small-text" type="number" step="0.000001" name="handling_cost[]" value="<?php echo esc_attr( $row['handling_cost'] ); ?>"></td>
                                <td><input class="small-text" type="number" step="0.000001" name="extra_cost[]" value="<?php echo esc_attr( $row['extra_cost'] ); ?>"></td>
                                <td><input class="small-text" type="number" step="0.000001" name="total_unit_cost[]" value="<?php echo esc_attr( $row['total_unit_cost'] ); ?>"></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <p><button class="button button-primary"><?php esc_html_e( 'Save Visible Costs', WCPI_TEXT_DOMAIN ); ?></button></p>

            <?php if ( $total_pages > 1 ) : ?>
                <div class="tablenav"><div class="tablenav-pages"><?php
                    echo wp_kses_post(
                        paginate_links(
                            array(
                                'base'      => add_query_arg( array( 'paged' => '%#%' ) ),
                                'format'    => '',
                                'current'   => $page,
                                'total'     => $total_pages,
                                'prev_text' => '&laquo;',
                                'next_text' => '&raquo;',
                            )
                        )
                    );
                ?></div></div>
            <?php endif; ?>
        </div>
    </form>
</div>

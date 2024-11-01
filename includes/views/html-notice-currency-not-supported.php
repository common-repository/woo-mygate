<?php
/**
 * Admin View: Notice - Currency not supported.
 *
 * @package WooCommerce_MyGate/Admin/Notices
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="error inline">
	<p><strong><?php _e( 'MyGate Disabled', 'woocommerce-mygate' ); ?></strong>: <?php printf( __( 'Currency <code>%s</code> is not supported. Please see MyGate for the list of supported currencies', 'woocommerce-mygate' ), get_woocommerce_currency() ); ?>
	</p>
</div>

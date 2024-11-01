<?php
/**
 * Admin View: Notice - Token missing
 *
 * @package WooCommerce_Mygate/Admin/Notices
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="error inline">
	<p><strong><?php _e( 'MyGate Disabled', 'woocommerce-mygate' ); ?></strong>: <?php _e( 'If you\'re not using MyGate in sandbox mode, please provide a valid Merchant ID.', 'woocommerce-mygate' ); ?>
	</p>
</div>

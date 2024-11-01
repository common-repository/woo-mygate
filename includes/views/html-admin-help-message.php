<?php
/**
 * Admin help message.
 *
 * @package WooCommerce_Mygate/Admin/Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( apply_filters( 'woocommerce_mygate_help_message', true ) ) : ?>
	<div class="updated inline woocommerce-message">
		<p><?php echo esc_html( sprintf( __( 'Help me keep %s free by making a donation or rating %s on WordPress.org. Thanks in advance!', 'woocommerce-mygate' ), __( 'WooCommerce Mygate', 'woocommerce-mygate' ), '&#9733;&#9733;&#9733;&#9733;&#9733;' ) ); ?></p>
		<p><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=J78732V5VULA6" target="_blank" class="button button-primary"><?php esc_html_e( 'Make a donation', 'woocommerce-mygate' ); ?></a> <a href="https://wordpress.org/support/view/plugin-reviews/woocommerce-mygate?filter=5#postform" target="_blank" class="button button-secondary"><?php esc_html_e( 'Make a review', 'woocommerce-mygate' ); ?></a></p>
	</div>
<?php endif;

<?php
/**
 * Admin options screen.
 *
 * @package WooCommerce_MyGate/Admin/Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<h3><?php echo esc_html( $this->method_title ); ?></h3>

<?php
	if ( 'yes' == $this->get_option( 'enabled' ) && 'no' == $this->get_option( 'sandbox' ) ) {
		if ( ! $this->using_supported_currency() && ! class_exists( 'woocommerce_wpml' ) ) {
			include dirname( __FILE__ ) . '/html-notice-currency-not-supported.php';
		}

		if ( 'F5785ECF-1EAE-40A0-9D37-93E2E8A4BAB3' === $this->merchantid ) {
			include dirname( __FILE__ ) . '/html-notice-merchantid-missing.php';
		}

	}
        
?>

<?php echo wpautop( $this->method_description ); ?>

<?php include dirname( __FILE__ ) . '/html-admin-help-message.php'; ?>

<table class="form-table">
	<?php $this->generate_settings_html(); ?>
</table>

<?php

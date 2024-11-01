<?php

/**
 * Supremecreative (Pty) Ltd
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to amir@supremecreative.co.za so I can send you a copy.
 *
 * @copyright   Copyright (c) 2017 Supremecreative (Pty) Ltd
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Mygate gateway
 *
 * @package    WooCommerce_Mygate/Gateway
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Mygate_Gateway class.
 */
class WC_Mygate_Gateway extends WC_Payment_Gateway {
    
 	/**
	 * Constructor for the gateway.
	 *
	 * @return void
	 */
        public function __construct() 
        {
            
		$this->id             = 'mygate';
		$this->icon           = apply_filters( 'woocommerce_mygate_icon', plugins_url( 'assets/images/mygate.png', plugin_dir_path( __FILE__ ) ) );
		$this->has_fields     = false;
		$this->method_title   = __( 'Mygate', 'woocommerce-mygate' );            
		$this->method_description = __( 'Accept payments by credit card, bank debit or banking ticket using the Mygate payment gateway.', 'woocommerce-mygate' );
		$this->order_button_text  = __( 'Checkout with Mygate', 'woocommerce-mygate' );

		// Load settings.
		$this->init_form_fields();
		$this->init_settings();   

		// Define user set variables.
		$this->title          = $this->get_option( 'title' );
		$this->description    = $this->get_option( 'description' );
		$this->merchantid     = $this->get_option( 'merchantid', 'F5785ECF-1EAE-40A0-9D37-93E2E8A4BAB3' );
		$this->applicationid  = $this->get_option( 'applicationid', '3C5F80CB-F2DA-45D3-800D-A876E6258F17' );           
		$this->debug          = $this->get_option( 'debug' );
                $this->sandbox        = $this->get_option( 'sandbox' );
                
		// Actions.
		add_action( 'woocommerce_api_wc_gateway_mygate', array( $this, 'ipn_handler' ) );
		add_action( 'woocommerce_receipt_mygate', array( $this, 'receipt_page' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
                
		if ( 'yes' === $this->debug ) {
			$this->log = wc_get_logger();
		}                
        }        
        
	/**
	 * Returns a bool that indicates if currency is amongst the supported ones.
	 *
	 * @return bool
	 */
	protected function using_supported_currency() 
        {
                $supported_currencies = ['ZAR', 'USD', 'EUR', 'GDP', 'KES', 'BWP', 'GHS', 'SCR', 'TZS', 'UGX', 'ZMW', 'MZN', 'NGN', 'MUR'];
                $current_currency = get_woocommerce_currency();
                return in_array($current_currency, $supported_currencies);
	} 

        
	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() 
        {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'woocommerce-mygate' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Mygate Payment Gateway', 'woocommerce-mygate' ),
				'default' => 'yes'
			),
			'title' => array(
				'title'       => __( 'Title', 'woocommerce-mygate' ),
				'type'        => 'text',
				'description' => __( 'This specifies the title the user sees at checkout.', 'woocommerce-mygate' ),
				'desc_tip'    => true,
				'default'     => __( 'Mygate', 'woocommerce-mygate' )
			),
			'description' => array(
				'title'       => __( 'Customer Message', 'woocommerce-mygate' ),
				'type'        => 'textarea',
				'description' => __( 'This specifies the description the user sees at checkout.', 'woocommerce-mygate' ),
				'desc_tip'    => true,
				'default'     => __( 'Pay with credit or debit card using the Mygate payment gateway.', 'woocommerce-mygate' )
			),
			'merchantid' => array(
				'title'       => __( 'Merchant ID', 'woocommerce-mygate' ),
				'type'        => 'text',
				'label'       => __( 'Enable Sandbox', 'woocommerce-mygate' ),
				'placeholder'     => 'F5785ECF-1EAE-40A0-9D37-93E2E8A4BAB3',
				'description' => __( 'Please specify your Merchant ID, which you may obtain from MyGate. NB: The Sandbox Merchant ID will automatically be used if one is not specified.', 'woocommerce-mygate' ),
			),
			'applicationid' => array(
				'title'       => __( 'Application ID', 'woocommerce-mygate' ),
				'type'        => 'text',
				'label'       => __( 'Enable Sandbox', 'woocommerce-mygate' ),
                                'placeholder' => '3C5F80CB-F2DA-45D3-800D-A876E6258F17',  
				'description' => sprintf(  __( 'Please specify your Application ID, which you may obtain from MyGate.  NB: The Sandbox Application ID (for the ZAR currency) will automatically be used if one is not specified. You may obtain sandbox Application IDs for other currencies %s.', 'woocommerce-mygate' ), '<a target="_blank" href="https://developers.mygateglobal.com/my_virtual.php">' . __( 'here', 'woocommerce-mygate' ) . '</a>' ),
			),
			'sandbox' => array(
				'title'       => __( 'Enable Mygate Sandbox Mode', 'woocommerce-mygate' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable Sandbox', 'woocommerce-mygate' ),
				'default'     => 'no',
				'description' => __( 'Mygate Sandbox can be used to test payments. You may need to provide an Application ID for your chosen currency, which you may obtaion from Mygate.', 'woocommerce-mygate' )
			),
			'testing' => array(
				'title'       => __( 'Gateway Debugging', 'woocommerce-mygate' ),
				'type'        => 'title',
				'description' => ''
			),
			'debug' => array(
				'title'       => __( 'Debug Log', 'woocommerce-mygate' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable logging', 'woocommerce-mygate' ),
				'default'     => 'no',
				'description' => sprintf( __( 'Log Mygate events, such as API requests, inside %s', 'woocommerce-mygate' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-status&tab=logs&log_file=' . esc_attr( $this->id ) . '-' . sanitize_file_name( wp_hash( $this->id ) ) . '.log' ) ) . '">' . __( 'System Status &gt; Logs', 'woocommerce-mygate' ) . '</a>' )
			)                    
		);
	}
        

	/**
	 * Admin page.
	 */
	public function admin_options() 
        {
		include dirname( __FILE__ ) . '/views/html-admin-page.php';
	}

	/**
	 * Generate the args to form.
	 *
	 * @param  object $order Order data.
	 *
	 * @return array         Form arguments.
	 */
	public function get_form_args( $order ) 
        {

		$args = array(

			// Customer info.
			'Recipient'                 => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
			'email'                     => $order->get_billing_email(),
			'telephone'                 => str_replace( array( '(', '-', ' ', ')' ), '', $order->get_billing_phone() ),

			// Billing Address info.
			'billing_first_name'        => $order->get_billing_first_name(),
			'billing_last_name'         => $order->get_billing_last_name(),                    
			'billing_address_1'         => $order->get_billing_address_1(),
			'billing_address_2'         => $order->get_billing_address_2(),
			'billing_city'              => $order->get_billing_city(),
			'billing_state'             => $order->get_billing_state(),
			'billing_postcode'          => $order->get_billing_postcode(),
                    
                        'shipping_first_name'       => $order->get_shipping_first_name(),
                        'shipping_last_name'        => $order->get_shipping_last_name(),
                        'shipping_company'          => $order->get_shipping_company(),
			'shipping_address_1'        => $order->get_shipping_address_1(),
			'shipping_address_2'        => $order->get_shipping_address_2(),
			'shipping_city'             => $order->get_shipping_city(),
			'shipping_state'            => $order->get_shipping_state(),
			'shipping_postcode'         => $order->get_shipping_postcode(),
                        'shipping_country'          => $order->get_shipping_country(),                    

			// Tax.
			'total_tax'                 => $order->get_total_tax(),

			// Payment Info.
			'order_id'                  => $order->get_id(),

			// Shipping info.
			'OrderShippingCost'         => number_format( $order->get_shipping_total(), 2, '.', '' ),
			'shipping_method'           => $order->get_shipping_method(),

			// Return.
			'RedirectSuccessfulURL'     => $this->get_return_url( $order ),
			'RedirectFailedURL'         => $this->get_return_url( $order ),
                        'Amount'                    => $order->get_total(),
                        'Currency'                  => get_woocommerce_currency()

		);

		// Cart Contents.
		$item_loop = 0;
		if ( count( $order->get_items() ) > 0 ) {
			foreach ( $order->get_items() as $item ) {
				if ( $item['qty'] ) {
					$item_loop++;
					$item_name = $item['name'];
					$item_meta = new WC_Order_Item_Meta( $item );

					if ( $meta = $item_meta->display( true, true ) ) {
						$item_name .= ' (' . $meta . ')';
					}

					$args['product_code_' . $item_loop ]    = $item_loop;
					$args['product_description_' . $item_loop ] = sanitize_text_field( $item_name );
					$args[ 'ItemQty' ]      = $item['qty'];
					$args['product_total_' . $item_loop ]     = $order->get_item_total( $item, false );
				}
			}
		}

		$args = apply_filters( 'woocommerce_mygate_args', $args, $order );

		return $args;
	}        
        
	/**
	 * Output for the order received page.
	 *
	 * @param int $order_id Order ID.
	 */
	public function receipt_page( $order_id ) 
        {
		$order     = wc_get_order( $order_id );
		$args      = $this->get_form_args( $order );
		$form_args = array();
                $mode = ( 'yes' === $this->sandbox ) ? '0' : '1';
                
		// Sort args.
		ksort( $args );

		if ( 'yes' === $this->debug ) {
			$this->log->add( $this->id, 'Payment arguments for order ' . $order->get_order_number() . ': ' . print_r( $args, true ) );
		}

		$form_args[] = '<input type="hidden" name="MerchantID" value="' . $this->merchantid . '" />';
                $form_args[] = '<input type="hidden" name="ApplicationID" value="' . $this->applicationid . '" />';
                $form_args[] = '<input type="hidden" name="MerchantReference" value="' . $order_id . '" />';
                $form_args[] = '<input type="hidden" name="Mode" value="' . $mode . '" />';
                
		foreach ( $args as $key => $value ) {
			$form_args[] = '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
		}

		wc_enqueue_js( '
			jQuery.blockUI({
				message: "' . esc_js( __( 'Thank you for your order. We are now redirecting you to Mygate to make payment.', 'woocommerce-mygate' ) ) . '",
				baseZ: 99999,
				overlayCSS: {
					background: "#fff",
					opacity: 0.6
				},
				css: {
					padding:         "20px",
					zindex:          "9999999",
					textAlign:       "center",
					color:           "#555",
					border:          "3px solid #aaa",
					backgroundColor: "#fff",
					cursor:          "wait",
					lineHeight:      "24px",
				}
			});
			jQuery( "#submit-payment-form" ).click();
		' );

		echo '<p>' . __( 'Thank you for your order, please click the button below to pay with Mygate.', 'woocommerce-mygate' ) . '</p>';
		echo '<form action="https://virtual.mygateglobal.com/PaymentPage.cfm" method="post" id="payment-form" target="_top">';
		echo implode( '', $form_args );
		echo '<input type="submit" class="button alt" id="submit-payment-form" value="' . __( 'Pay via Mygate', 'woocommerce-mygate' ) . '" /> <a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . __( 'Cancel order &amp; restore cart', 'woocommerce-mygate' ) . '</a>';
		echo '</form>';
	}        
        
	/**
	 * IPN handler.
	 */
	public function ipn_handler() 
        {
		@ob_clean();

		$order_data = $this->get_mygate_order_data( $_POST );

		if ( ! empty( $order_data ) ) {
			header( 'HTTP/1.1 200 OK' );
			$this->update_order_status( $order_data );
		} else {
			$message = __( 'MyGate Request Unauthorized', 'woocommerce-mygate' );
			wp_die( $message, $message, array( 'response' => 401 ) );
		}
	} 
        
	/**
	 * Get MyGate order data.
	 *
	 * @param  array $args
	 *
	 * @return array
	 */
	protected function get_mygate_order_data( $args ) 
        {
		$args           = wp_unslash( $args );
		$transaction_id = '';
		$order_id       = '';

		if ( isset( $args['_RESULT'] ) && isset( $args['_3DSTATUS'] ) && isset( $args['_MERCHANTREFERENCE'] ) && isset( $args['_TRANSACTIONINDEX'] ) ) {
			$transaction_id = sanitize_text_field( $args['_TRANSACTIONINDEX'] );
			$order_id       = sanitize_text_field( $args['_MERCHANTREFERENCE'] );
		} 

		if ( ! $transaction_id && ! $order_id ) {
			if ( 'yes' === $this->debug ) {
				$this->log->add( $this->id, 'Unable to check the Mygate transaction because it is missing the IPN data...' );
			}

			return array();
		}

		if ( 'yes' === $this->debug ) {
			$this->log->add( $this->id, sprintf( 'Checking Mygate transaction #%s data for order %s...', $transaction_id, $order_id ) );
		}

                $result = sanitize_text_field( $args ["_RESULT"] );
                $threedsecure = sanitize_text_field( $args ["_3DSTATUS"] );
                $acquirerDateTime = sanitize_text_field( $args ["_ACQUIRERDATETIME"] );
                $price = sanitize_text_field( $args ["_AMOUNT"] );
                $cardCountry = sanitize_text_field( $args ["_CARDCOUNTRY"] );
                $countryCode = sanitize_text_field( $args ["_COUNTRYCODE"] );
                $currencyCode = sanitize_text_field( $args ["_CURRENCYCODE"] );
                $merchantReference = sanitize_text_field( $args ["_MERCHANTREFERENCE"] );
                $transactionIndex = sanitize_text_field( $args ["_TRANSACTIONINDEX"] );
                $payMethod = sanitize_text_field( $args ["_PAYMETHOD"] );
                $errorCode = sanitize_text_field( $args ['_ERROR_CODE'] );
                $errorMessage = sanitize_text_field( $args ['_ERROR_MESSAGE'] );
                $errorDetail = sanitize_text_field( $args ['_ERROR_DETAIL'] );
                $errorSource = sanitize_text_field( $args ['_ERROR_SOURCE'] );
                $bankErrorCode = sanitize_text_field( $args ["_BANK_ERROR_CODE"] );
                $bankErrorMessage = sanitize_text_field( $args ["_BANK_ERROR_MESSAGE"] );                

		$data = array(
			'transaction_id' => $transaction_id,
			'order_id'    => $order_id,
                        'result' => $result ,
                        'threedsecure' => $threedsecure ,
                        'acquirerDateTime' => $acquirerDateTime ,
                        'price' => $price ,
                        'currencyCode' => $currencyCode,
                        'cardCountry' => $cardCountry,
                        'countryCode' => $countryCode,
                        'merchantReference' => $merchantReference,
                        'transactionIndex' => $transactionIndex,
                        'payMethod' => $payMethod,
                        'errorCode' => $errorCode,
                        'errorMessage' => $errorMessage,
                        'errorDetail' => $errorDetail,
                        'errorSource' => $errorSource,
                        'bankErrorCode' => $bankErrorCode,
                        'bankErrorMessage' => $bankErrorMessage,                    
		);

		if ( 'yes' === $this->debug ) {
			$this->log->add( $this->id, 'Mygate order data response: ' . print_r( $data, true ) );
		}

		return $data;
		
	}

	/**
	 * Update order status.
	 *
	 * @param array $transaction_data MyGate transaction data.
	 */
	protected function update_order_status( $data ) 
        {
		$order_id = intval( sanitize_text_field( $data['order_id'] ) );
		$order    = wc_get_order( $order_id );

		if ( $order->get_id() === $order_id ) {
			if ( 'yes' === $this->debug ) {
				$this->log->add( $this->id, 'Payment status from order ' . $order->get_order_number() . ': ' . ( $data['order_id'] >= 0 ) ? 'Transaction Successful' : 'Transaction Failed.' );
			}

			// Save order details.
			$order->set_transaction_id( sanitize_text_field( $data['transaction_id'] ) );

			// Update order status.
                        if ( $data['result'] == 0 ) { 
                                $order->add_order_note( __( 'Mygate: Awaiting payment – stock is reduced, but you need to confirm payment.', 'woocommerce-mygate' ) );
                                $order->update_status( 'on-hold', __( 'Mygate: Awaiting payment – stock is reduced, but you need to confirm payment.', 'woocommerce-mygate' ) );
                                $order->payment_complete();                            
                        }
                        
                        if( $data['result'] == 1 ) {
                                $order->add_order_note( __( 'Mygate: The fraud module is providing a flag or unnecessary parameters were sent to the API in the request message.', 'woocommerce-mygate' ) );
                                $order->update_status( 'on-hold' );                            
                        }
      
                        switch ( intval( $data['result'] ) ) {
                                
                                case 0: 
                                        $order->add_order_note( __( 'Mygate: Awaiting payment – stock is reduced, but you need to confirm payment.', 'woocommerce-mygate' ) );
                                        $order->update_status( 'on-hold', __( 'Mygate: Awaiting payment – stock is reduced, but you need to confirm payment.', 'woocommerce-mygate' ) );
                                        $order->payment_complete();  
                                        break;
                                    
                                case 1: 
                                        $order->add_order_note( __( 'Mygate: The fraud module is providing a flag or unnecessary parameters were sent to the API in the request message.', 'woocommerce-mygate' ) );
                                        $order->update_status( 'on-hold' );                                      
                                        break;
                                    
                                default:      
                                        $order->update_status( 'failed', __( 'Mygate: Payment failed with error code ' . $data['errorCode'] , 'woocommerce-mygate' ) );
                                        break;
                            
                        }

                        $order->save();
		}
	}        
        
	/**
	 * Process the payment and return the result.
	 *
	 * @param int    $order_id Order ID.
	 *
	 * @return array           Redirect.
	 */
	public function process_payment( $order_id ) 
        {
                $order = wc_get_order( $order_id );

		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url( true ),
		);
	}       
        
}


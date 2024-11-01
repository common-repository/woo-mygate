<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/amir-canteetu/
 * @since             1.0.0
 * @package           Mygate
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Mygate
 * Plugin URI:        https://github.com/amir-canteetu/woocommerce-mygate
 * Description:       Woocommerce plugin for payments via Mygate payment gateway.
 * Version:           1.0.0
 * Author:            Amir Canteetu
 * Author URI:        https://github.com/amir-canteetu/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mygate
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_Mygate' ) ) :

    /**
     * WooCommerce Mygate main class.
     */
    class WC_Mygate {

            /**
             * Plugin version.
             *
             * @var string
             */
            const VERSION = '1.0.0';

            /**
             * Instance of this class.
             *
             * @var object
             */
            protected static $instance = null;

            /**
             * Initialize the plugin actions.
             */
            public function __construct() {

                    add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

                    if ( class_exists( 'WC_Payment_Gateway' ) ) {
                            $this->includes();

                            add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );
                            add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );

                    } else {
                            add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
                    }
            }

            /**
             * Return an instance of this class.
             *
             * @return object A single instance of this class.
             */
            public static function get_instance() {
                    // If the single instance hasn't been set, set it now.
                    if ( null == self::$instance ) {
                            self::$instance = new self;
                    }

                    return self::$instance;
            }

            /**
             * Load the plugin text domain for translation.
             */
            public function load_plugin_textdomain() {
                    load_plugin_textdomain( 'woocommerce-mygate', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
            }

            /**
             * Includes.
             */
            private function includes() {
                    include_once dirname( __FILE__ ) . '/includes/class-wc-mygate-gateway.php';
            }

            /**
             * Action links.
             *
             * @param  array $links
             *
             * @return array
             */
            public function plugin_action_links( $links ) {
                    $plugin_links = array();

                    $plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=mygate' ) ) . '">' . __( 'Settings', 'woocommerce-mygate' ) . '</a>';

                    return array_merge( $plugin_links, $links );
            }

            /**
             * Add the gateway to WooCommerce.
             *
             * @param   array $methods WooCommerce payment methods.
             *
             * @return  array          Payment methods with Mygate.
             */
            public function add_gateway( $methods ) {
                $methods[] = 'WC_Mygate_Gateway'; 
                return $methods;                
            }


            /**
             * WooCommerce fallback notice.
             *
             * @return string
             */
            public function woocommerce_missing_notice() {
                    include dirname( __FILE__ ) . '/includes/views/html-notice-missing-woocommerce.php';
            }
    }

    add_action( 'plugins_loaded', array( 'WC_Mygate', 'get_instance' ) );

endif;

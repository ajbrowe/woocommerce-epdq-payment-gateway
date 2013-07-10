<?php
/*
Plugin Name: WooCommerce ePDQ Payment Gateway
Plugin URI: http://www.nomfolio.com/wp/woocommerce-epdq-payment-gateway
Description: Extends WooCommerce with an barkley bank epdq gateway.
Version: 1.0
Author: MAK Joy
Author URI: http://www.nomfolio.com/me
 
	Copyright: © 2003 MAK Joy.
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if( !defined('NOM_EPDQ_DIR') )
	define('NOM_EPDQ_DIR', dirname(__FILE__) . '/' );

add_action('plugins_loaded', 'woocommerce_nom_epdq_init', 0);
 
function woocommerce_nom_epdq_init() {
 
	if ( !class_exists( 'WC_Payment_Gateway' ) ) return;
 
	/**
 	 * Localisation
	 */
	load_plugin_textdomain('woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');
    
	/**
 	 * Gateway class
 	 */
	
	require_once NOM_EPDQ_DIR . 'class.epdq.php';	
	
	
	/**
 	* Add the Gateway to WooCommerce
 	**/
	function woocommerce_add_nom_epdq_gateway($methods) {
		$methods[] = 'WC_Nom_EPDQ';
		return $methods;
	}
	
	add_filter('woocommerce_payment_gateways', 'woocommerce_add_nom_epdq_gateway' );
} 
<?php
/*
 * Plugin Name: WooCommerce Alfapay Payment Gateway
 * Plugin URI: https://www.fleekbiz.com
 * Description:  Accept payments for Bank Al Falah
 * Author: Johar Shakil
 * Author URI: https://www.fleekbiz.com
 * Version: 1.0.1
 *
/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
 
 
add_action( 'plugins_loaded', 'alfapay_init_gateway_class_redirection', 0 );
function alfapay_init_gateway_class_redirection() {
    //if condition use to do nothin while WooCommerce is not installed
		require 'class_woocommerce_gateway_AlfaPay.php';
  // class add it too WooCommerce
  add_filter( 'woocommerce_payment_gateways', 'alfapay_add_gateway_class_redirection' );
  function alfapay_add_gateway_class_redirection( $methods ) {
    $methods[] = 'WC_AlfaPay_Payment_Gateway_Redirection';
    return $methods;
  }
}


// Add custom action links
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'alfa_gateway_redirection_action_links' );
function alfa_gateway_redirection_action_links( $links ) {
  $plugin_links = array(
    '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '">' . __( 'Settings', 'alfapay-payment-gateway-redirection' ) . '</a>',
  );
  return array_merge( $plugin_links, $links );
}





<?php
/*
 * Plugin Name: WooCommerce Payment Gateway - Sign2Pay
 * Plugin URI: http://www.sign2pay.com/woocommerce/
 * Description: Process SEPA Direct Debit Payments in 18 European Countries
 * Version: 1.0.1
 * Author: sign2pay
 * Author URI: https://www.sign2pay.com
 * Requires at least: 3.1

 * @package WordPress
 * @author sign2pay
 */

/*
 * Title   : Sign2Pay extension for Woo-Commerece
 * Author  : Sign2Pay
 * Url     : http://sign2pay.com
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


function woocommerce_updater_notice() {

  $plugin = plugin_basename( __FILE__ );
  $plugin_data = get_plugin_data( __FILE__, false );

  if (! class_exists( 'WooCommerce' ) ){
    if( is_plugin_active($plugin) ) {
      deactivate_plugins( $plugin );
      wp_die( "<strong>".$plugin_data['Name']."</strong> requires <strong>WooCommerce 2.1</strong> or higher to be installed and active. Please activate WooCommerce and try again.<br /><br />Back to the WordPress <a href='".get_admin_url(null, 'plugins.php')."'>Plugins page</a>." );
    }
  }else if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ) ) {

    if( is_plugin_active($plugin) ) {
      deactivate_plugins( $plugin );
      wp_die( "<strong>".$plugin_data['Name']."</strong> requires <strong>WooCommerce 2.1</strong> or higher, and has been deactivated! Please upgrade WooCommerce and try again.<br /><br />Back to the WordPress <a href='".get_admin_url(null, 'plugins.php')."'>Plugins page</a>." );
    }
  }
}
add_action( 'admin_init', 'woocommerce_updater_notice' );


if ( ! function_exists( 'woothemes_queue_update' ) ) {
  require_once( 'includes/woo-includes/woo-functions.php' );
}


define( 'SIGN2PAY__VERSION', '1.0.0' );
define( 'SIGN2PAY__TEXTDOMAIN', 'sign2pay' );

function init_sign2pay_gateway()
{
    if (class_exists('WC_Payment_Gateway'))
    {
        include_once('class.sign2payextension.php');
    }
}

add_action('plugins_loaded', 'init_sign2pay_gateway', 0);
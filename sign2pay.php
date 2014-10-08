<?php
/*
 * Plugin Name: WooCommerce Payment Gateway - Sign2Pay
 * Plugin URI: http://www.sign2pay.com/woocommerce/
 * Description: Process SEPA Direct Debit Payments in 18 European Countries
 * Version: 1.0.0
 * Author: sign2pay
 * Author URI: http://www.sign2pay.com
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
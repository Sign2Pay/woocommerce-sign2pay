<?php
/*
 * Title   : Sign2Pay extension for Woo-Commerece
 * Author  : Sign2Pay
 * Url     : http://sign2pay.com
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

class WC_Gateway_Sign2Pay extends WC_Payment_Gateway
{
    var $notify_url;
    var $s2p_domain;

    public function __construct()
    {
        // Register plugin information
        $this->id                   = 'Sign2Pay';
        $this->has_fields           = false;
        $this->debug                = true;
        $this->order_button_text    = __( 'Proceed to Sign2Pay', 'woocommerce' );
        $this->notify_url           = WC()->api_request_url( 'WC_Gateway_Sign2Pay' );

        if("local.wordpress.dev" == $_SERVER['SERVER_NAME']){
          $this->s2p_domain = "sign2pay.dev";
        }else{
          $this->s2p_domain = "sign2pay.com";
        }

        // Create plugin fields and settings
        $this->init_form_fields();
        $this->init_settings();

        // Get setting values
        foreach ( $this->settings as $key => $val ) $this->$key = $val;

        // Load plugin checkout icon
        $this->icon         = WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__)) . '/images/s2p_logo_receipt.png';

        // Logs
        if ( 'yes' == $this->debug ) {
          $this->log = new WC_Logger();
        }

        // tell WooCommerce to save options
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id  , array($this, 'process_admin_options'));
        add_action( 'admin_notices'                                             , array($this, 'perform_ssl_check'    ));
        add_action( 'woocommerce_receipt_' . $this->id                          , array( $this, 'inline_sign2pay'     ));
        add_action( 'woocommerce_thankyou'                                      , array($this, 'thankyou_page'        ));

        // style
        wp_register_style( 's2pStyleSheet', plugins_url('css/s2p.css', __FILE__) );
        wp_register_style( 's2pAdminStyleSheet', plugins_url('css/s2p_admin.css', __FILE__) );
        wp_register_style( 's2pSweetStyleSheet', plugins_url('css/sweet-alert.css', __FILE__) );

        // javascripts
        wp_register_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI.min.js', array( 'jquery' ), '2.66', true );
        wp_register_script( 's2pAdminJS', plugins_url('js/s2p_admin.js', __FILE__), array('jquery'), SIGN2PAY__VERSION, true );
        wp_register_script( 's2pSweetJS', plugins_url('js/sweet-alert.min.js', __FILE__), array('jquery'), SIGN2PAY__VERSION, true );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        // Payment listener/API hook
        add_action( 'woocommerce_api_wc_gateway_sign2pay', array( $this, 'check_s2p_postback' ) );
    }

    /*
     * Check if SSL is enabled and notify the user.
     */
    function perform_ssl_check() {
      if ( get_option( 'woocommerce_force_ssl_checkout' ) == 'no' && $this->enabled == 'yes' ) {
          echo '<div class="error"><p>' . sprintf( __('Sign2Pay is enabled and the <a href="%s">force SSL option</a> is disabled; your checkout is not secure! Please enable SSL and ensure your server has a valid SSL certificate.', 'woothemes' ), admin_url( 'admin.php?page=woocommerce' ) ) . '</p></div>';
          }
    }

    /*
     * Initialize Gateway Settings Form Fields.
     */
    function init_form_fields() {

      $this->form_fields = array(
      'enabled'     => array(
        'title'       => __( 'Enable/Disable', 'woothemes' ),
        'label'       => __( 'Enable Sign2Pay', 'woothemes' ),
        'type'        => 'checkbox',
        'description' => '',
        'default'     => 'no'
        ),
      'title'       => array(
        'title'       => __( 'Title', 'woothemes' ),
        'type'        => 'text',
        'description' => __( 'This controls the title which the user sees during checkout.', 'woothemes' ),
        'default'     => __( 'Direct Debit (Sign2Pay)', 'woothemes' ),
        'desc_tip'    => true
        ),
      'description' => array(
        'title'       => __( 'Description', 'woothemes' ),
        'type'        => 'textarea',
        'description' => __( 'This controls the description which the user sees during checkout.', 'woothemes' ),
        'default'     => 'Secure Mobile Payment via Sign2Pay.',
        'desc_tip'    => true
        ),
      'debug' => array(
        'title'       => __( 'Debug Log', 'woocommerce' ),
        'type'        => 'checkbox',
        'label'       => __( 'Enable logging', 'woocommerce' ),
        'default'     => 'no',
        'description' => sprintf( __( 'Log Sign2Pay events, such as IPN requests, inside <code>%s</code>', 'woocommerce' ), wc_get_log_file_path( 'sign2pay' ) )
      ),
      'api_details' => array(
        'title'       => __( 'API Credentials', 'woocommerce' ),
        'type'        => 'title',
        'description' => sprintf( __( 'Enter your Sign2Pay API credentials from your %sMerchant Admin%s.', 'woocommerce' ), '<a href="https://merchant.sign2pay.com">', '</a>' ),
      ),
      'merchant_id'    => array(
        'title'       => __( 'Merchant ID', 'woothemes' ),
        'type'        => 'text',
        'description' => sprintf( __( 'This is your Merchant ID that was created when registering for for Sign2Pay. Read more about it in the Sign2Pay <a href="%s">documentation</a>.', 'woocommerce' ), 'https://sign2pay.com/docs' ),
        'default'     => ''
        ),
      'token'    => array(
        'title'       => __( 'Application Token', 'woothemes' ),
        'type'        => 'text',
        'description' => sprintf( __( 'This is the Application Token generated that represents your site. Read more about it in the Sign2Pay <a href="%s">documentation</a>.', 'woocommerce' ), 'https://sign2pay.com/docs' ),
        'default'     => ''
        ),
      'api_token'    => array(
        'title'       => __( 'API Token', 'woothemes' ),
        'type'        => 'text',
        'description' => sprintf( __( 'This is the Sign2Pay API Token used to sign postbacks and make requests to the S2P API. Read more about it in the Sign2Pay <a href="%s">documentation</a>.', 'woocommerce' ), 'https://sign2pay.com/docs' ),
        'default'     => ''
        )
      );
    }

    /*
     * UI - Admin Panel Options
     */
    public function admin_options()
    {
        include_once('includes/form.admin.php');
        wp_enqueue_style( 's2pAdminStyleSheet' );
        wp_enqueue_style('s2pSweetStyleSheet');

        wp_enqueue_script('s2pSweetJS');
        wp_enqueue_script('s2pAdminJS');
    }

    /*
     * UI - Payment page js for Sign2Pay.
     */
    public function payment_fields()
    {
        global $woocommerce;
        $total = $woocommerce->cart->total * 100;
        include_once('includes/javascript.risk_assessment.php');
    }

    /*
     * Enqueues the s2p.css
     *
     * @since 1.1
     */
    public function enqueue_scripts() {
      global $woocommerce;
      wp_enqueue_style( 's2pStyleSheet' );
      wp_enqueue_script('jquery-blockui');
    }

    /*
     * Process the payment
     */
    function process_payment($order_id) {
      global $woocommerce;

      $order = new WC_Order( $order_id );

      return array(
        'result'    => 'success',
        'redirect'  => $this->get_checkout_payment_url( $order )
      );
    }

    /*
     * Order page that loads up Sign2Pay Inline Payments
     */
    function inline_sign2pay( $order_id ) {
      $order = new WC_Order( $order_id );
      include_once('includes/javascript.payment.php');
    }

    /*
     * Thank you page that loads after successful payment from guest checkout
     */
    function thankyou_page( $order_id ) {
      $order = new WC_Order( $order_id );
    }

    /*
     * Get checkout payment url
     */
    protected function get_checkout_payment_url( $order ){
      if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ) ) {
        return add_query_arg('order', $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay'))));
      }
      return $order->get_checkout_payment_url( true);
    }

  /*
   * Check Postback for required params and valid signature
   * @access public
   * @return bool
   */
  public function validate_postback( $s2p_postback ) {

    $required_keys = array(
      "ref_id",
      "merchant_id",
      "purchase_id",
      "amount",
      "status",
      "token",
      "timestamp",
      "signature"
    );

    $requirements_met = (count(array_diff($required_keys, array_keys($s2p_postback))) == 0);

    if (false == $requirements_met) {
      if ( 'yes' == $this->debug ) {
        $this->log->add( 'sign2pay', 'Malformed Postback - Missing Required: ' . implode(",", array_diff($required_keys, array_keys($s2p_postback))));
      }
      return false;
    }

    $order_exists = ! empty( $s2p_postback["ref_id"] ) && $this->check_order($s2p_postback);

    if(false == $order_exists){
      if ( 'yes' == $this->debug ) {
        $this->log->add( 'sign2pay', 'Malformed Postback - Order Does not Exist: ' . $s2p_postback["ref_id"]);
      }
      return false;
    }

    $valid_signature = $this->verify_signature($s2p_postback);

    if(false == $valid_signature){
      if ( 'yes' == $this->debug ) {
        $this->log->add( 'sign2pay', 'Malformed Postback - Invalid Signature: ' . implode(",", $s2p_postback));
      }
      return false;
    }
    return true;
  }

  /*
   * Check Postback order exists and is has a payable status
   */

  public function check_order($s2p_postback){
    $order_id   = $s2p_postback["ref_id"];
    $order      = new WC_Order( (int) $order_id);
    $status = $order->status;
    $payable = "pending";

    if($status != $payable){

      if(empty($status)){
        $message =  'Order does not exist';
      }else{
        $message =  'Order is not payable. Current Order Status: ' . $status;
        $order->add_order_note( __('Payment error: ', "woothemes") . $message, 'error' );
      }

      if ( 'yes' == $this->debug ) {
        $this->log->add( 'sign2pay', $message);
      }

      $this->postback_failed_with_message($order_id, $message);
      return false;
    }
    return true;
  }

  /*
   * Verify signature supplied in postback is valid
   * @access public
   * @return bool
   */
  public function verify_signature( $s2p_postback ) {
    return $s2p_postback["signature"] === hash_hmac(
          "sha256",
          $s2p_postback["timestamp"] . $s2p_postback["token"],
          $this->get_option('api_token')
      );
  }

  /*
   * Exit from Postback with response
   * @access public
   */

  public function postback_failed_with_message($order_id, $message){

    header( 'HTTP/1.1 403 FORBIDDEN' );
    $arr = array('status' => "failure",
      'params' => array(
        'ref_id'  => $order_id,
        'message' => $message
      )
    );

    $reponse = json_encode($arr);
    echo $reponse;
    exit;
  }

  /*
   * Exit from Postback with response
   * @access public
   */

  public function postback_failed_with_redirect($redirect, $params){

    header( 'HTTP/1.1 403 FORBIDDEN' );
    $arr = array('status' => "failure",
      'redirect_to' => $redirect,
      'params' => $params
    );

    $reponse = json_encode($arr);
    echo $reponse;
    exit;
  }

  /*
   * Check for Sign2Pay IPN Response
   *
   * @access public
   * @return void
   */
  public function check_s2p_postback() {
    global $woocommerce;

    @ob_clean();

    $s2p_postback = ! empty( $_POST ) ? $_POST : false;

    if ( $s2p_postback && $this->validate_postback( $s2p_postback ) ) {

      $order_id     = $_REQUEST["ref_id"];
      $merchant_id  = $_REQUEST["merchant_id"];
      $purchase_id  = $_REQUEST["purchase_id"];
      $amount       = $_REQUEST["amount"];
      $status       = $_REQUEST["status"];
      $token        = $_REQUEST["token"];
      $timestamp    = $_REQUEST["timestamp"];
      $signature    = $_REQUEST["signature"];

      $order = new WC_Order( (int) $order_id);

      header( 'HTTP/1.1 200 OK' );
      // Mark Payment as completed
      $order->add_order_note( __('Payment completed', "woothemes") );
      $order->payment_complete();

      // empty woocommerce cart
      $woocommerce->cart->empty_cart();

      $redirect = $this->get_return_url( $order );

      $arr = array('status' => "success",
         'redirect_to' => $redirect,
         'params' => array(
             "total"          => $amount,
             "id"             => $order_id,
             "purchase_id"    => $purchase_id,
             "signature"      => true,
             "status"         => $status,
             "authorization"  => $timestamp
         )
      );

      $reponse = json_encode($arr);
      echo $reponse;
      exit;
    }
  }
}
/*
 * Add the gateway to woocommerce
 */

function add_sign2pay_gateway($methods)
{
    array_push($methods, 'WC_Gateway_Sign2Pay');
    return $methods;
}

add_filter('woocommerce_payment_gateways', 'add_sign2pay_gateway');
<?php
/*
 * Title   : Sign2Pay extension for Woo-Commerece
 * Author  : Sign2Pay
 * Url     : https://www.sign2pay.com
 * License : GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
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
        $this->s2p_domain           = "sign2pay.com";
        $this->s2p_api_version      = "v2";
        $this->log_path             = wc_get_log_file_path( 'sign2pay' );
        $this->serving_from         = $this->get_implementation_url();

        // Create plugin fields and settings
        $this->init_form_fields();
        $this->init_settings();

        // Get setting values
        foreach ( $this->settings as $key => $val ) $this->$key = $val;

        // Load plugin icons
        $this->icon           = WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__)) . '/images/s2p_logo_receipt.png';
        $this->settings_icon  = WP_PLUGIN_URL . "/" . plugin_basename( dirname(__FILE__)) . '/images/s2p_logo_white.png';
        // style
        wp_register_style( 's2pStyleSheet', plugins_url('css/s2p.css', __FILE__) );
        wp_register_style( 's2pAdminStyleSheet', plugins_url('css/s2p_admin.css', __FILE__) );
        wp_register_style( 's2pSweetStyleSheet', plugins_url('css/sweet-alert.css', __FILE__) );

        // javascripts
        wp_register_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI.min.js', array( 'jquery' ), '2.66', true );
        wp_register_script( 's2pAdminJS', plugins_url('js/s2p_admin.js', __FILE__), array('jquery'), SIGN2PAY__VERSION, true );
        wp_register_script( 's2pSweetJS', plugins_url('js/sweet-alert.min.js', __FILE__), array('jquery'), SIGN2PAY__VERSION, true );

        // All WC Action hooks
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id  , array($this, 'process_admin_options'));
        add_action( 'admin_notices'                                             , array($this, 'perform_ssl_check'    ));
        add_action( 'woocommerce_review_order_before_submit'                    , array($this, 'risk_assessment_js'   ));
        add_action( 'woocommerce_receipt_' . $this->id                          , array($this, 'inline_sign2pay'      ));
        add_action( 'woocommerce_thankyou'                                      , array($this, 'thankyou_page'        ));

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        // Payment listener/API hook
        add_action( 'woocommerce_api_wc_gateway_sign2pay', array( $this, 'check_s2p_postback' ) );

    }


    /**
     * Log errors / messages to WooCommerce error log
     *
     * @since 2.1
     * @param string $message
     */
    public function log( $message ) {

      if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ) ) {
        global $woocommerce;

        if ( ! is_object( $this->logger ) )
          $this->logger = $woocommerce->logger();
      } else {
        if ( ! is_object( $this->logger ) )
          $this->logger = new WC_Logger();
      }

      $this->logger->add( 'sign2pay', $message );
    }

    /*
     * Check if SSL is enabled and notify the user.
     */
    function perform_ssl_check() {
      if ( get_option( 'woocommerce_force_ssl_checkout' ) == 'no' && $this->enabled == 'yes' ) {
          echo '<div class="error"><p>' . sprintf( __('Sign2Pay is enabled and the <a href="%s">force SSL option</a> is disabled; your checkout is not secure! Please enable SSL and ensure your server has a valid SSL certificate.', 'woothemes' ), admin_url( 'admin.php?page=woocommerce' ) ) . '</p></div>';
          }
    }

    public function get_implementation_url(){
      if(isset($_SERVER['HTTPS'])){
            $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
        }
        else{
            $protocol = 'http';
        }
        return $protocol . "://" . $_SERVER['HTTP_HOST'];
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
        'description' => sprintf( __( 'Log Sign2Pay events, such as IPN requests. <a id="show_log_path" href="#show_log_path">view log path<input class="log_path" type="hidden" value="%s" /></a>', 'woocommerce' ), wc_get_log_file_path( 'sign2pay' ) )
      ),
      'api_details' => array(
        'title'       => __( 'API Credentials', 'woocommerce' ),
        'type'        => 'title',
        'description' => sprintf( __( 'Enter your Sign2Pay API credentials from your %sMerchant Admin%s. <p>Don\'t forget that the implementation URL your entered when creating your S2P application needs to be set to <strong> ' . $this->serving_from . '</strong>.</p>', 'woocommerce' ), '<a href="https://merchant.sign2pay.com">', '</a>' ),
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
     * UI - Payment page markup for Sign2Pay.
     */
    public function payment_fields()
    {
      $out = "<script>jQuery(function($){ $('#payment li.payment_method_Sign2Pay:first').addClass('ignore');});</script>";

      if ( $this->description ) {
        $out .= $this->description;
      }
      echo $out;
    }

    /*
     * UI - Payment page js for Sign2Pay.
     */
    public function risk_assessment_js()
    {
      global $woocommerce;
      if ( is_checkout() ) {
        $risk_assessment = true;
        $total = $woocommerce->cart->total * 100;
        include_once('includes/javascript.risk_assessment.php');
      }
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
      $risk_assessment = false;
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
        $this->log('Malformed Postback - Missing Required: ' . implode(",", array_diff($required_keys, array_keys($s2p_postback))), "error");
      }
      return false;
    }

    $order_exists = ! empty( $s2p_postback["ref_id"] ) && $this->check_order($s2p_postback);

    if(false == $order_exists){
      if ( 'yes' == $this->debug ) {
        $this->log('Malformed Postback - Order Does not Exist: ' . $s2p_postback["ref_id"], "error");
      }
      return false;
    }

    $valid_signature = $this->verify_signature($s2p_postback);

    if(false == $valid_signature){
      if ( 'yes' == $this->debug ) {
        $this->log('Malformed Postback - Invalid Signature: ' . implode(",", $s2p_postback), "error");
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
        $this->log($message, "error");
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
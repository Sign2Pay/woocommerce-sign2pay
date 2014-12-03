<?php
/*
 * Title   : Sign2Pay extension for Woo-Commerece
 * Author  : Sign2Pay
 * Url     : http://sign2pay.com
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */
?>

<div class="initializing">
  <h1>
    <img src="https://s3-eu-west-1.amazonaws.com/s2p/sign2PayLogo_2x.png"/>
  </h1>
  <h4>Secure Mobile Payments with your Signature</h4>

  <div id="sign2pay" style="display: block !important;">
    <span class='s2p-button-text'>
      Thank you for your order.
      <br/>
      We are now initializing Sign2Pay to authorize your payment.
    </span>
  </div>
</div>

<script type="text/javascript">
  window.sign2PayOptions = {
    merchant_id: <?php echo '"' . $this->get_option( 'merchant_id' )  .'"' ?>,
    token: <?php echo '"' . $this->get_option( 'token' ) .'"' ?>,
    checkout_type: 'multi',
    domain : <?php echo '"' . $this->s2p_domain .'"' ?>,
    launch : "on_load",
    first_name: <?php echo '"' . $order->billing_first_name .'"' ?>,
    last_name: <?php echo '"' . $order->billing_last_name .'"' ?>,
    email: <?php echo '"' . $order->billing_email .'"' ?>,
    address: <?php echo '"' . $order->billing_address_1 .' ' . $order->billing_address_2 .'"'?>,
    postal_code: <?php echo '"' . $order->billing_postcode .'"' ?>,
    city: <?php echo '"' . $order->billing_city .'"' ?>,
    country: <?php echo '"' . $order->billing_country .'"' ?>,
    amount: <?php echo '"' . $order->order_total * 100 .'"' ?>,
    ref_id : <?php echo '"' . $order->id .'"' ?>,
    close : function(){
      console.log("s2p was closed");
      window.location.href = <?php echo '"' . esc_url( $order->get_cancel_order_url() )  .'"'?>;
    },
    open : function(){
      console.log("s2p was opened");
      jQuery(".s2p-button-text").text("Pay with Sign2Pay");
      jQuery("#sign2pay").addClass("reinit");
    }
  };

  (function() {
    var s = document.createElement("script");
    s.type = "text/javascript";
    s.src = "<?php echo $this->merchant_js ?>";
    s.async = true;
    t = document.getElementsByTagName('body')[0];
    t.appendChild(s);
  })();
</script>
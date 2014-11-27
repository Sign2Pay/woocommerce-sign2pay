<?php
/*
 * Title   : Sign2Pay extension for Woo-Commerece
 * Author  : Sign2Pay
 * Url     : http://sign2pay.com
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */
?>

<style>
.initializing{
  text-align: center;
  padding:2em;
  width:66%;
  max-width: 300px;
  margin: 0 auto 1.5em;
  border-radius: 12px;
  text-align: center;
  background-image: -webkit-gradient(linear, left top, left bottom, color-stop(0, #4381c3), color-stop(1, #6eb3eb));
  background-image: -o-linear-gradient(bottom, #4381c3 0%, #6eb3eb 100%);
  background-image: -moz-linear-gradient(bottom, #4381c3 0%, #6eb3eb 100%);
  background-image: -webkit-linear-gradient(bottom, #4381c3 0%, #6eb3eb 100%);
  background-image: -ms-linear-gradient(bottom, #4381c3 0%, #6eb3eb 100%);
  background-image: linear-gradient(to bottom, #4381c3 0%, #6eb3eb 100%);
  border-bottom: 1px solid #4381c3;
  -webkit-box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1), 0 1px 0 rgba(0, 0, 0, 0.1);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1), 0 1px 0 rgba(0, 0, 0, 0.1);
  color:white;
}
.initializing h1 img{
  margin: auto;
  width:66%;
  max-width: 200px;
  padding: 0;
  border:none;
  background-color: transparent;

}

.woocommerce-checkout .type-page h1::before{
  display:none;
}
.initializing h4{
  margin-bottom: 1em;
  font-family: 'HelveticaNeue-UltraLight', 'Helvetica Neue UltraLight', 'Helvetica Neue', Arial, Helvetica, sans-serif;
  font-weight: 300;
  letter-spacing: 1px;
  font-size: 20px;
  line-height: 1em;
  text-shadow: 1px 1px 2px rgba(0,0,0,0.6);
  color:white;
}
.loading{
  margin: 0 auto 1.5em;
  width:30px;
}

.s2p_banks_wrap{
    width:50px;
    margin: 0 auto 1em;
}
.s2p_banks_wrap img{
  width:50px;
}

#sign2pay.reinit{
  cursor: pointer;
  display: inline-block;
  padding: 6px 12px;
  margin-bottom: 0;
  line-height: 1.42857143;
  text-align: center;
  white-space: nowrap;
  vertical-align: middle;
  cursor: pointer;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  background-image: none;
  border: 2px solid transparent;
  border-radius: 4px;
  color: #fff;
  background-color: #49A3CC;
  border-color: #357ebd;
  line-height: 1.33;
  border-radius: 6px;
  text-decoration: none;
  margin-right:2%;
  font-size: 17px;
  line-height:1.5em;
  border-size:2px;
  background-color: #7DBB3B;
  border-color: #6da333;
}

</style>

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
    s.src = <?php echo "'//" . $this->s2p_domain . "/merchant.js';" ?>
    s.async = true;
    t = document.getElementsByTagName('body')[0];
    t.appendChild(s);
  })();


</script>
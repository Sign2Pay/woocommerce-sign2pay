<?php
/*
 * Title   : Sign2Pay extension for Woo-Commerece
 * Author  : Sign2Pay
 * Url     : http://sign2pay.com
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */
?>

<div id="woocommerce_sign2pay"></div>
<?php

      wc_enqueue_js(
        '
        unblock = function(){
          $.unblockUI();
        };

        if (window.addEventListener) {
            window.addEventListener("message", unblock, false);
        } else if (window.attachEvent) {
            window.attachEvent("onmessage", unblock);
        }

        $.blockUI({
          message: "' . esc_js( __( 'Thank you for your order. We are now initializing Sign2Pay to authorize your payment.', "woothemes" ) ) . '",
          baseZ: 99999,
          overlayCSS:
          {
            background: "#000",
            opacity: 0.6
          },
          css: {
            padding:        "20px",
            zindex:         "9999999",
            textAlign:      "center",
            color:          "#555",
            border:         "3px solid #aaa",
            backgroundColor:"#fff",
            cursor:         "wait",
            lineHeight:     "24px",
          }
        });

      '
      );


  echo '<input type="button" class="button" value="Checkout" id="s2p_init_transport"/>';
  echo '<a class="button cancel" href="'.esc_url( $order->get_cancel_order_url() ).'">'.__( 'Cancel order &amp; restore cart', "woothemes" ).'</a>';
?>

<script type="text/javascript">
  window.sign2PayOptions = {
    el : "#s2p_init_transport",
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
    ref_id : <?php echo '"' . $order->id .'"' ?>

  };

  (function() {
    var s = document.createElement("script");
    s.type = "text/javascript";
    s.src = <?php echo "'//" . $this->s2p_domain . "/assets/merchant.js';" ?>
    s.async = true;
    t = document.getElementsByTagName('body')[0];
    t.appendChild(s);
  })();


</script>
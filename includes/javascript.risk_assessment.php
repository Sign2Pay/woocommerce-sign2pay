<?php
/*
 * Title   : Sign2Pay extension for Woo-Commerece
 * Author  : Sign2Pay
 * Url     : http://sign2pay.com
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */
?>

<?php
  // Description of payment method from settings
  if ( $this->description ) { ?>
    <p><?php echo $this->description; ?></p>
  <?php } ?>

<script type="text/javascript">
  jQuery(function($) {
    $("#payment li.payment_method_sign2pay:first").addClass("ignore").hide();
  });

  window.sign2PayOptions = {
    el : "#payment li.payment_method_sign2pay:first",
    merchant_id: <?php echo '"' . $this->get_option( 'merchant_id' )  .'"' ?>,
    token: <?php echo '"' . $this->get_option( 'token' ) .'"' ?>,
    checkout_type: 'single',
    domain : "sign2pay.com",
    map:{
      first_name: '#billing_first_name',
      last_name: '#billing_last_name',
      email: '#billing_email',
      address: '#billing_address_1',
      postal_code: '#billing_postcode',
      city: '#billing_city',
      country: '#billing_country',
      amount: function(){
        return <?php echo $total; ?>;
      },
      ref_id : function(){
        m = <?php echo '"' . $this->get_option( 'merchant_id' )  .'";' ?>
        t = Date.now();
        return m + "_" + t
      }
    }
  };

  (function() {
    var s = document.createElement("script");
    s.type = "text/javascript";
    s.src = "//sign2pay.com/assets/merchant.js";
    s.async = true;
    t = document.getElementsByTagName('script')[0];
    t.parentNode.insertBefore(s, t);
  })();

</script>
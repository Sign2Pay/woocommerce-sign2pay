<?php
/*
 * Title   : Sign2Pay extension for Woo-Commerece
 * Author  : Sign2Pay
 * Url     : http://sign2pay.com
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */
?>
<?php if(isset($risk_assessment) && $risk_assessment == true) {?>

  <script type="text/javascript">

    window.sign2PayOptions = {
      el : "#payment li.payment_method_Sign2Pay:first",
      merchant_id: <?php echo '"' . $this->get_option( 'merchant_id' )  .'"' ?>,
      token: <?php echo '"' . $this->get_option( 'token' ) .'"' ?>,
      checkout_type: 'single',
      domain : <?php echo '"' . $this->s2p_domain .'"' ?>,
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
        s.src = <?php echo "'//" . $this->s2p_domain . "/merchant.js';" ?>
        s.async = true;
        t = document.getElementsByTagName('body')[0];
        t.appendChild(s);
        window.s2p_merchant_script_appended = true;
        console.log("merchant_js appended");
    })();

    jQuery(function($){
      $('body').bind( 'update_checkout', function() {
        if(typeof(window.s2p) != "undefined"){
          if(typeof(window.s2p.options) != "undefined"){
            window.s2p.options.reInitS2P();
          }
        }
      });
    });

  </script>
<?php }?>
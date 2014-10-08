(function() {
  jQuery(function($){

    var validateSettings = function(){

      var purchase = {
        merchant_id: $("#woocommerce_Sign2Pay_merchant_id").val(),
        token : $("#woocommerce_Sign2Pay_token").val()
      };

      var options = {
        url: "https://sign2pay.com/api/v2/application/validate",
        type: "POST",
        data : {"format" : "json", purchase : purchase},
        beforeSend : function(xhr){
          var token = $("#woocommerce_Sign2Pay_api_token").val();
          xhr.setRequestHeader("Authorization", "Token token=" + token);
        }
      };

      $.ajax(options)
        .done(function(){
          console.log("all done");
        })
        .success(function(response){
          sweetAlert("Yeehaw...", "Looks like you're good to go!", "success");
          console.log("all good");
        })
        .fail(function(response){
          sweetAlert("Oops...", "Seems like your settings are incorrect. Please verify by signing into your Sign2Pay Merchant Admin.", "error");
          console.log("not good");
      });

    };
    // end of validateSettings

    // bind validate link
    $("#s2p_validate_settings a").on("click", function(e){
      e.preventDefault();
      validateSettings();
    });

  });

})();
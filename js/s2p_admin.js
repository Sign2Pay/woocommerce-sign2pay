(function() {
  jQuery(function($){

    var wp_submit = $(".submit input[name='save']");

    if(wp_submit[0]){
      $("#s2p_save_settings a").on("click", function(e){
        e.preventDefault();
        $(this).closest("form").submit();
      });
      wp_submit.hide();
    }else{
      $("#s2p_save_settings").hide();
    }

    if($("#woocommerce_Sign2Pay_merchant_id")[0]){
      if($("#woocommerce_Sign2Pay_merchant_id").val() == ""){
        $(".s2p_sign_in").closest(".s2p_links").hide()
        $(".s2p_sign_up").closest(".s2p_links").show()
      }else{
        $(".s2p_sign_in").closest(".s2p_links").show()
        $(".s2p_sign_up").closest(".s2p_links").hide()
      }
    }
    var validateSettings = function(){

      var purchase = {
        merchant_id: $("#woocommerce_Sign2Pay_merchant_id").val(),
        token : $("#woocommerce_Sign2Pay_token").val()
      };

      var options = {
        url: window.s2p_protocol+"://" + window.s2p_domain +"/api/"+ window.s2p_api_version+"/application/validate",
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
          if(typeof(response.error) != "undefined"){
            sweetAlert("Oops...", "Seems like your settings are incorrect. \n\n Please verify by signing into your Sign2Pay Merchant Admin.", "error");
          }else{
            sweetAlert("Yeehaw...", "Looks like you're good to go!", "success");
          }
          console.log("all good");
        })
        .fail(function(response){
          sweetAlert("Oops...", "Seems like your settings are incorrect. \n\n Please verify by signing into your Sign2Pay Merchant Admin.", "error");
          console.log("not good");
      });

    };
    // end of validateSettings

    // bind validate link
    $("#s2p_validate_settings a").on("click", function(e){
      e.preventDefault();
      validateSettings();
    });

    var supportedCountries = function(){

      var options = {
        url: window.s2p_protocol+"://" + window.s2p_domain +"/api/"+ window.s2p_api_version+"/countries.json",
        type: "GET",
        data : {"format" : "json"}
      };

      $.ajax(options)
        .done(function(){
          console.log("all done");
        })
        .success(function(response){

          console.log("all good");
          countries = "";
          count = response.length
          $(response).each(function(i, country){
            countries += country.name;
            if(i+1 < count){
              countries += ", ";
            }
          });
          sweetAlert("Sign2Pay Currently Supports:", countries, "success");
        })
        .fail(function(response){
          console.log("not good");
      });
    };

    //bind s2p_supported_countries
    $("a.s2p_supported_countries").on("click", function(e){
      e.preventDefault();
      supportedCountries();
    });

    // show log path
    $("a#show_log_path").on("click", function(e){
      e.preventDefault();
      sweetAlert("Your Sign2Pay Log Path", "You'll find your S2P log file at: \n\n "  + window.s2p_log_path);
    });


  });
})();
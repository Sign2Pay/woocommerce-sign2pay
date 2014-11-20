<?php
/*
 * Title   : Sign2Pay extension for Woo-Commerece
 * Author  : Sign2Pay
 * Url     : http://sign2pay.com
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */
?>

<script type="text/javascript">
  window.s2p_domain       = "<?php echo $this->s2p_domain; ?>";
  window.s2p_api_version  = "<?php echo $this->s2p_api_version; ?>";
  window.s2p_log_path     = "<?php echo $this->log_path; ?>";
  window.s2p_protocol     = "<?php echo $this->protocol; ?>";

</script>

<div class="s2p_wrap">
  <div class="s2p_header">

    <h3 class="s2p_title">
      <img src='<?php echo $this->settings_icon; ?>'/>
    </h3>

    <p class="s2p_byline">
      Secure and easy mobile payments using your personal signature.
    </p>
    <p>
      With a quick and simple integration, you'll be processing Direct Debits across <a class="s2p_supported_countries" href="#countries">18 European countries</a>.
    </p>

  </div>

    <div class="s2p_right_col">
      <div class="s2p_links">
        <h4>Need an Account?</h4>
        <div class="s2p_sign_up">
          <a href="https://<?php echo $this->s2p_domain; ?>" target="_blank">Sign Up</a>
        </div>
        <div class="s2p_clear"></div>
      </div>

      <div class="s2p_links">
        <h4>Merchant Login</h4>
        <div class="s2p_sign_in">
          <a href="https://merchant.<?php echo $this->s2p_domain; ?>/profile/sign_in" target="_blank">Sign In</a>
        </div>
        <div class="s2p_clear"></div>
      </div>

      <div class="s2p_links">
        <h4>Related Links</h4>
        <ul>
          <li>
            <a href="https://merchant.sign2pay.com" target="_blank">Sign2Pay Merchant Admin</a>
          </li>
          <li>
            <a href="https://sign2pay.com/docs/" target="_blank">Sign2Pay Documentation</a>
          </li>
        </ul>
      </div>
    </div>

    <div class="s2p_left_col">
      <table class="s2p_settings form-table">
          <?php $this->generate_settings_html(); ?>
          <tr>
            <td></td>
            <td>
              <div id="s2p_validate_settings">
                <a href="#test">Test Your Settings</a>
              </div>
              <div id="s2p_save_settings">
                <a href="#save">Save Your Settings</a>
              </div>
            </td>
          </tr>
      </table>
    </div>



    <div class="s2p_clear"></div>
</div>
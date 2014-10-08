<?php
/*
 * Title   : Sign2Pay extension for Woo-Commerece
 * Author  : Sign2Pay
 * Url     : http://sign2pay.com
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */
?>

<div class="s2p_header">
  <h3 class="s2p_title">
      <img src='<?php echo $this->icon; ?>'/>
  </h3>

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

  <p>
    Secure and easy mobile payments using your personal signature. With a quick and simple integration, you'll be processing Direct Debits across 18 European countries.
  </p>

</div>
<table class="form-table">
    <?php $this->generate_settings_html(); ?>

    <tr>
      <td></td>
      <td>
        <div id="s2p_validate_settings">
          <a href="#">Test Your Settings</a>
        </div>
      </td>
    </tr>
</table>

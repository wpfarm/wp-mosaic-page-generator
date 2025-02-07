<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $wpmpg;
?>
<h3><?php _e('Plugin Settings','wp-mosaic-page-generator'); ?></h3>
<form method="post" action="">
<input type="hidden" name="wpmpg_update_settings" />
<div id="tabs-bupro-settings" class="wpmpg-multi-tab-options">
<ul class="nav-tab-wrapper bup-nav-pro-features">
<li class="nav-tab bup-pro-li"><a href="#tabs-wpmpg-recaptcha" title="<?php _e('Settings','wp-mosaic-page-generator'); ?>"><?php _e('Common','wp-mosaic-page-generator'); ?> </a></li>

</ul>

<div id="tabs-wpmpg-recaptcha">
<div class="wpmpg-sect  wpmpg-welcome-panel">
  <h3><?php _e('Settings','wp-quote-sp-plugin'); ?></h3>  
  
  <table class="form-table">
<?php	
 
?>
</table>
</div>
<p class="submit">
	<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save','wp-mosaic-page-generator'); ?>"  />
</p>  
</div>
</div>
</form>
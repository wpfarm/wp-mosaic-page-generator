<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $wpmpg;
?>

<div class="wpmpg-welcome-panel">
<div class="row item first uploader">
    <div class="col col-9 info mid">
        <div class="row label"><?php _e('Help Center','wp-mosaic-page-generator'); ?></div>
        <div class="row description"><?php _e('Here you can manage your custom post types.','wp-mosaic-page-generator'); ?></div>
    </div>
</div>

<form method="post" action="">
<input type="hidden" name="wpmpg_update_settings" />

<div  class="">

<div class="row in">
<h3><?php _e('Rank Math Tags','wp-mosaic-page-generator'); ?></h3> 
<p><?php _e('For Rank Math Plugin the meta description tag is named:','wp-mosaic-page-generator'); ?></p> 
<p><strong>rank_math_title</strong></p>
<p><strong>rank_math_description</strong></p>
<p><strong>rank_math_focus_keyword</strong></p>
    
<h3><?php _e('Yoast Tags','wp-mosaic-page-generator'); ?></h3> 
<p><?php _e('For Yoast Plugin the meta tags are the following:','wp-mosaic-page-generator'); ?> </p> 
<p><strong>_yoast_wpseo_title</strong></p> 
<p><strong>_yoast_wpseo_metadesc</strong></p> 
<p><strong>_yoast_wpseo_focuskw</strong></p> 

<h3><?php _e('Custom Fields & Post Types','wp-mosaic-page-generator'); ?></h3>
<p><?php _e('These fields are categorized based on their prefixes, which determine how they function. Here is a quick guide to help you understand how to use them:'); ?> </p> 
   
<p><b><?php _e('Text Fields: Prefix: custom_field'); ?> </b></p> 
<p><?php _e('Example usage:'); ?> </p> 
<p><?php _e('custom_field_title  A field for entering a title.'); ?> </p> 


<p><b><?php _e('Text Fields: Prefix: custom_image'); ?> </b></p> 
<p><?php _e('Example usage:'); ?> </p> 
<p><?php _e('custom_image_banner.  A field for uploading a banner image.'); ?> </p>


</div>
</div>


</form>
</div>
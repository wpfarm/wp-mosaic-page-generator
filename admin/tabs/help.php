<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $wpmpg;

?>

<div class="wpmpg-welcome-panel">
<div class="row item first uploader">
    <div class="col col-9 info mid">
        <div class="row label"><?php _e('Help Center','wp-mosaic-page-generator'); ?></div>
        <div class="row description">Here you can manage your custom post types.</div>
    </div>
    <div class="col col-3 click">
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
    
</div>




</div>




</form>
</div>
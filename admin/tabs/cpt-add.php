<?php
global $wpmpg;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$auxNewPCT = new WpMosaicCPT();        
?>

<div class="wpmpg-welcome-panel">

    <div class="row item first uploader">
        <div class="col col-9 info mid">
            <div class="row label">New Post Type</div>
            <div class="row description">Create a custom post type</div>
        </div>
        <div class="col col-3 click">
        </div>
    </div>    
    


<?php echo wp_kses($auxNewPCT->get_errors(), $wpmpg->allowed_html);?> 
<?php echo wp_kses($auxNewPCT->sucess_message, $wpmpg->allowed_html);?> 

<div class="row edit-fields">
<form method="post" action="">
<input type="hidden" name="wpmpg_create_cpt"  value="wpmpg_create_cpt"/>

<?php wp_nonce_field( 'update_settings', 'wpmpg_nonce_check' ); ?>

    <table width="100%" class="">                      
        <tbody>          
            <tr>
                <td class="wpmpg-colval"><?php _e('Name', 'wp-mosaic-page-generator'); ?></td>
                <td> <input name="cpt_name" id="cpt_name" value="<?php echo $wpmpg->get_post_value('cpt_name')?>" type="text"> </td>        
            </tr> 
            
            <tr>
                <td class="wpmpg-colval"><?php _e('Unique CPT Slug', 'wp-mosaic-page-generator'); ?> </td>
                <td> <input name="cpt_unique_key" id="cptcpt_unique_keyname" value="<?php echo $wpmpg->get_post_value('cpt_unique_key')?>" type="text"> - <?php _e('This value must to be unique: Example: city, state, country, etc.', 'wp-mosaic-page-generator'); ?></td>        
            </tr> 
        </tbody>
    </table>

    <h2><?php _e('Labels', 'wp-mosaic-page-generator'); ?> </h2>


    <table width="100%" class="">                      
        <tbody>          
            <tr>
                <td class="wpmpg-colval" ><?php _e('Singular Name', 'wp-mosaic-page-generator'); ?></td>
                <td> <input name="singular_name" id="singular_name" value="<?php echo $wpmpg->get_post_value('singular_name')?>" type="text"> </td>        
            </tr> 
            
            <tr>
                <td class="wpmpg-colval"><?php _e('Add New', 'wp-mosaic-page-generator'); ?></td>
                <td> <input name="add_new" id="add_new" value="<?php echo $wpmpg->get_post_value('add_new')?>" type="text"> </td>        
            </tr> 

            <tr>
                <td class="wpmpg-colval"><?php _e('Add New Item', 'wp-mosaic-page-generator'); ?></td>
                <td> <input name="add_new_item" id="add_new_item" value="<?php echo $wpmpg->get_post_value('add_new_item')?>" type="text"> </td>        
            </tr> 

            <tr>
                <td class="wpmpg-colval"><?php _e('Edit Item', 'wp-mosaic-page-generator'); ?></td>
                <td> <input name="edit_item" id="edit_item" value="<?php echo $wpmpg->get_post_value('edit_item')?>" type="text"> </td>        
            </tr> 

            <tr>
                <td class="wpmpg-colval"><?php _e('New Item', 'wp-mosaic-page-generator'); ?></td>
                <td> <input name="new_item" id="new_item" value="<?php echo $wpmpg->get_post_value('new_item')?>" type="text"> </td>        
            </tr>

            <tr>
                <td class="wpmpg-colval"><?php _e('View Item', 'wp-mosaic-page-generator'); ?></td>
                <td> <input name="view_item" id="view_item" value="<?php echo $wpmpg->get_post_value('view_item')?>" type="text"> </td>        
            </tr>

            <tr>
                <td class="wpmpg-colval"><?php _e('Search Items', 'wp-mosaic-page-generator'); ?></td>
                <td> <input name="search_items" id="search_items" value="<?php echo $wpmpg->get_post_value('search_items')?>" type="text"> </td>        
            </tr>

            <tr>
                <td class="wpmpg-colval"><?php _e('Not found', 'wp-mosaic-page-generator'); ?></td>
                <td> <input name="not_found" id="not_found" value="<?php echo $wpmpg->get_post_value('not_found')?>" type="text"> </td>        
            </tr>

            <tr>
                <td class="wpmpg-colval"><?php _e('Not found in Trash', 'wp-mosaic-page-generator'); ?></td>
                <td> <input name="not_found_in_trash" id="not_found_in_trash" value="<?php echo $wpmpg->get_post_value('not_found_in_trash')?>" type="text"> </td>        
            </tr>
        </tbody>
    </table>

    <p class="submit">
	<input type="submit" name="submit" id="submit" value="<?php _e('Submit','wp-mosaic-page-generator'); ?>"  />
</p>

   	
</div>
</form>
</div>
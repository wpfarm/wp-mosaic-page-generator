<?php
global $wpmpg;
if ( ! defined( 'ABSPATH' ) ) exit; 

$auxNewPCT = new WpMosaicCPT();      
if(isset($_GET['id']) && $_GET['id']!=''){	
	$id = sanitize_text_field($_GET['id']);	
	$cpt = $auxNewPCT->get_one($id);
    $cpt_att = json_decode($cpt->cpt_properties);
   
    if(!isset($cpt->cpt_id)){
        echo 'error';
        exit;
    } 

}else{ 
	
  $message =  '<div class="wpmpg-ultra-warning"><span><i class="fa fa-check"></i>'.__("Oops! Invalid CPT.",'wp-mosaic-page-generator').'</span></div>';
  echo wp_kses($message, $wpmpg->allowed_html);
  exit;	
	
}

?>

<div class="wpmpg-welcome-panel">
    
    <div class="row item first uploader">
        <div class="col col-9 info mid">
            <div class="row label">Edit Custom Post Type</div>
            <div class="row description">Here you can manage your custom post types.</div>
        </div>
        <div class="col col-3 click">
        </div>
    </div>

<?php echo wp_kses($auxNewPCT->get_errors(), $wpmpg->allowed_html);?> 
<?php echo wp_kses($auxNewPCT->sucess_message, $wpmpg->allowed_html);?> 

<div class="row edit-fields">
<form method="post" action="">
<input type="hidden" name="wpmpg_edit_cpt"  value="wpmpg_edit_cpt"/>
<input name="cpt_id" id="cpt_id" value="<?php echo $cpt->cpt_id?>" type="hidden"> 
<?php wp_nonce_field( 'update_settings', 'wpmpg_nonce_check' ); ?>

    <table width="100%" class="">                      
        <tbody>          
            <tr>
                <td class="wpmpg-colval"><?php _e('Name', 'wp-mosaic-page-generator'); ?></td>
                <td> <input name="cpt_name" id="cpt_name" value="<?php echo $cpt->cpt_name?>" type="text"> </td>        
            </tr> 
            
            <tr>
                <td class="wpmpg-colval"><?php _e('Unique CPT Slug', 'wp-mosaic-page-generator'); ?></td>
                <td> <input name="cpt_unique_key" id="cptcpt_unique_keyname" value="<?php echo $cpt->cpt_unique_key?>" type="text"> </td>        
            </tr> 
        </tbody>
    </table>

    <h2><?php _e('Labels', 'wp-mosaic-page-generator'); ?> </h2>

    <table width="100%" class="">                      
        <tbody>          
            <tr>
                <td class="wpmpg-colval" ><?php _e('Singular Name', 'wp-mosaic-page-generator'); ?></td>
                <td> <input name="singular_name" id="singular_name" value="<?php echo $cpt_att->singular_name?>" type="text"> </td>        
            </tr> 
            
            <tr>
                <td class="wpmpg-colval"><?php _e('Add New', 'wp-mosaic-page-generator'); ?></td>
                <td> <input name="add_new" id="add_new" value="<?php echo $cpt_att->add_new?>" type="text"> </td>        
            </tr> 

            <tr>
                <td class="wpmpg-colval"><?php _e('Add New Item', 'wp-mosaic-page-generator'); ?></td>
                <td> <input name="add_new_item" id="add_new_item" value="<?php echo $cpt_att->add_new_item?>" type="text"> </td>        
            </tr> 

            <tr>
                <td class="wpmpg-colval"><?php _e('Edit Item', 'wp-mosaic-page-generator'); ?></td>
                <td> <input name="edit_item" id="edit_item" value="<?php echo $cpt_att->edit_item?>" type="text"> </td>        
            </tr> 

            <tr>
                <td class="wpmpg-colval"><?php _e('New Item', 'wp-mosaic-page-generator'); ?></td>
                <td> <input name="new_item" id="new_item" value="<?php echo $cpt_att->new_item?>" type="text"> </td>        
            </tr>

            <tr>
                <td class="wpmpg-colval"><?php _e('View Item', 'wp-mosaic-page-generator'); ?></td>
                <td> <input name="view_item" id="view_item" value="<?php echo $cpt_att->view_item?>" type="text"> </td>        
            </tr>

            <tr>
                <td class="wpmpg-colval"><?php _e('Search Items', 'wp-mosaic-page-generator'); ?></td>
                <td> <input name="search_items" id="search_items" value="<?php echo $cpt_att->search_items?>" type="text"> </td>        
            </tr>

            <tr>
                <td class="wpmpg-colval"><?php _e('Not found', 'wp-mosaic-page-generator'); ?></td>
                <td> <input name="not_found" id="not_found" value="<?php echo $cpt_att->not_found?>" type="text"> </td>        
            </tr>

            <tr>
                <td class="wpmpg-colval"><?php _e('Not found in Trash', 'wp-mosaic-page-generator'); ?></td>
                <td> <input name="not_found_in_trash" id="not_found_in_trash" value="<?php echo $cpt_att->not_found_in_trash?>" type="text"> </td>        
            </tr>
        </tbody>
    </table>

    <p class="submit">
	<input type="submit" name="submit" id="submit" value="<?php _e('Submit','wp-mosaic-page-generator'); ?>"  />
</p>
   	
</div>
</form>
</div> 
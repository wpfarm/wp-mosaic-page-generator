<?php
global $wpmpg;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$auxNewPCT = new WpMosaicCPT();  

if(isset($_GET['cpt_id']) && $_GET['cpt_id']!=''){	
	$cpt_id = sanitize_text_field($_GET['cpt_id']);	
	$cpt = $auxNewPCT->get_one($cpt_id);
    $cpt_att = json_decode($cpt->cpt_properties);
   
    if(!isset($cpt->cpt_id)){
        echo 'error';
        exit;
    }

    //get custom post field
    $cpf_id = sanitize_text_field($_GET['cpf_id']);	
	$cpf = $auxNewPCT->get_one_cpf($cpf_id);
 
}else{ 
	
  $message =  '<div class="wpmpg-ultra-warning"><span><i class="fa fa-check"></i>'.__("Oops! Invalid CPT.",'wp-mosaic-page-generator').'</span></div>';
  echo wp_kses($message, $wpmpg->allowed_html);
  exit;	
	
}

?>

<div class="wpmpg-welcome-panel">
    <div class="row item first uploader">
        <div class="col col-9 info mid">
            <div class="row label">Edit Custom Field for <?php echo $cpt->cpt_name?></div>
            <div class="row description">Here you can manage your custom fields.</div>
        </div>
        <div class="col col-3 click">
        </div>
    </div>
<div class="row edit-fields">

<?php echo wp_kses($auxNewPCT->get_errors(), $wpmpg->allowed_html);?> 
<?php echo wp_kses($auxNewPCT->sucess_message, $wpmpg->allowed_html);?> 

<form method="post" action="">
<input type="hidden" name="wpmpg_edit_cpt_cpf"  value="wpmpg_edit_cpt_cpf"/>
<input type="hidden" name="cpf_cpt_id"  id="cpf_cpt_id"  value="<?php echo $cpt_id ?>"/>
<input type="hidden" name="cpf_id"  id="cpf_id"  value="<?php echo $cpf_id ?>"/>

<?php wp_nonce_field( 'update_settings', 'wpmpg_nonce_check' ); ?>
    <table width="100%" class="">                      
        <tbody>   
            
            <tr>
                <td class="wpmpg-colval"><?php _e('Field Type', 'wp-mosaic-page-generator'); ?></td>
                <td><select name="cpf_field_type" id="cpf_field_type" class="form-control ">
                    <option value="1"  <?php if($cpf->cpf_field_type == 1) {echo 'selected="selected"';}; ?>><?php _e('Text', 'wp-mosaic-page-generator'); ?></option>
                    <option value="2" <?php if($cpf->cpf_field_type == 2) {echo 'selected="selected"';}; ?>><?php _e('Image', 'wp-mosaic-page-generator'); ?></option>
            
                 </select> </td>        
            </tr>             
        
            <tr>
                <td class="wpmpg-colval"><?php _e('Field Label', 'wp-mosaic-page-generator'); ?></td>
                <td> <input name="cpf_field_label" id="cpf_field_label" value="<?php echo $cpf->cpf_field_label?>" type="text"> </td>        
            </tr> 
            
            <tr>
                <td class="wpmpg-colval"><?php _e('Field Name', 'wp-mosaic-page-generator'); ?></td>
                <td> <input name="cpf_field_name" id="cpf_field_name" value="<?php echo $cpf->cpf_field_name?>" type="text"> </td>        
            </tr> 

            <tr>
                <td class="wpmpg-colval"><?php _e('Default Value', 'wp-mosaic-page-generator'); ?></td>
                <td> <input name="cpf_field_default_value" id="cpf_field_default_value" value="<?php echo $cpf->cpf_field_default_value?>" type="text"> </td>        
            </tr> 
        </tbody>
    </table>

   
    <div class="row submit">
    <a href="?page=wpmpg&tab=cpt-cpf&id=<?php echo $cpt_id?>">
        <button type="button" class="outline"><?php _e('Back', 'wp-mosaic-page-generator'); ?></button>
    </a>
	<input type="submit" name="submit" id="submit" value="<?php _e('Update','wp-mosaic-page-generator'); ?>"  />
    </div>

   	
</div>
</form>
     
</div>
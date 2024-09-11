<?php
global $wpmpg;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$auxNewPCT = new WpMosaicCPT(); 

if(isset($_GET['id']) && $_GET['id']!=''){	
	$cpt_id = sanitize_text_field($_GET['id']);	
	$cpt = $auxNewPCT->get_one($cpt_id);
    $cpt_att = json_decode($cpt->cpt_properties);
    //print_r($cpt_att);
   
    if(!isset($cpt->cpt_id)){
        echo 'error';
        exit;
    }
 

}else{ 
	
  $message =  '<div class="userscontrol-ultra-warning"><span><i class="fa fa-check"></i>'.__("Oops! Invalid CPT.",'wp-mosaic-page-generator').'</span></div>';
  echo wp_kses($message, $wpmpg->allowed_html);
  exit;	
	
}




$cptRows = $auxNewPCT->get_all_custom_fields($cpt_id );
        
?>

<div class="wpmpg-welcome-panel">
    <div class="row item first uploader">
        <div class="col col-9 info mid">
            <div class="row label">Custom Fields for <?php echo $cpt->cpt_name;?></div>
            <div class="row description">Here you can manage your custom fields.</div>
        </div>
        <div class="col col-3 click">
            <a href="?page=wpmpg&tab=cpt-cpf-add&cpt_id=<?php echo $cpt_id?>">
                <button type="button"><?php _e('Create New', 'wp-mosaic-page-generator'); ?></button>
            </a>
        </div>
    </div>
    
    

 <div class="row post-types">
        
        <?php if (!empty($cptRows)){ ?>
      
          <table width="100%" class="wp-list-table widefat fixed posts table-generic">
           <thead>
               <tr>
                   <th width="2%"><?php _e('#', 'wp-mosaic-page-generator'); ?></th>
                   <th><?php _e('Name', 'wp-mosaic-page-generator'); ?></th>  
                   <th><?php _e('Label', 'wp-mosaic-page-generator'); ?></th> 
                   <th><?php _e('Type', 'wp-mosaic-page-generator'); ?></th>               
                   <th class="actions"><?php _e('Actions', 'wp-mosaic-page-generator'); ?></th>

                   
                   
               </tr>
           </thead>
           
           <tbody>
           
           <?php 
           $i = 1;
           foreach($cptRows as $cpt) {     
            
            $cp_type = ($cpt->cpf_field_type==1) ? 'Text' : 'Image';
              
           ?>
             

               <tr id="acc-row-<?php echo $cpt->cpf_id ?>">
                   <td><?php echo  $i; ?></td>
                   <td><?php echo  esc_attr($cpt->cpf_field_name); ?></td> 
                   <td><?php echo  esc_attr($cpt->cpf_field_label); ?></td>  
                   <td><?php echo  $cp_type; ?></td>
                                  
                   <td class="actions">&nbsp;
                   <a class="right edit outline" href="?page=wpmpg&tab=cpt-cpf-edit&cpf_id=<?php echo esc_attr($cpt->cpf_id)?>&cpt_id=<?php echo esc_attr($cpt_id)?>"   title="<?php _e('Edit','wp-mosaic-page-generator'); ?>">
                       <img src="<?php echo plugins_url( 'images/icon-settings.svg', dirname( __FILE__ ) ); ?>">
                    </a>  
                   <a href="#" class="right delete wpmpg-int-delete-acc-val"  acc-id="<?php echo esc_attr($cpt->cpf_id)?>" title="<?php _e('Delete','wp-mosaic-page-generator'); ?>"><i class="fa fa-trash-o"></i></a></td>
                  
               </tr>
               
               
               <?php

                $i++;
             }
                   
                   } else {
           ?>
           <p><?php _e('There are no custom post fields.','wp-mosaic-page-generator'); ?></p>
           <?php	} ?>

           </tbody>
       </table>
       
       
       </div>
   	
</div>

     

<?php
global $wpmpg;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$cptRows = $wpmpg->get_all_post_types();
$auxCPF = new WpMosaicCPT();
        
?>

<div class="wpmpg-welcome-panel">

<h1 class="wpmpg-extended">Post Types</h1>
 <p class="wpmpg-extended-p">Here you can manage your custom post types.</p> 


 <div class="wpmpg-sect wpmpg-welcome-panel">

 <div class="rownoflex getbwp-button-bart">
    <a href="?page=wpmpg&tab=cpt-add"><button type="button" class="button button-primary button-large"><span style="margin-right:5px"><i class="fa fa-plus"></i></span><?php _e('Create New', 'wp-mosaic-page-generator'); ?></button></a>

 </div>
        
        <?php
           
           
               
               if (!empty($cptRows)){
               
               
               ?>
      
          <table width="100%" class="wp-list-table widefat fixed posts table-generic">
           <thead>
               <tr>
                   <th width="2%"><?php _e('#', 'wp-mosaic-page-generator'); ?></th>
                   <th><?php _e('Name', 'wp-mosaic-page-generator'); ?></th>  
                   <th><?php _e('Unique Post Type Slug', 'wp-mosaic-page-generator'); ?></th> 
                   <th><?php _e('Custom Post Fields', 'wp-mosaic-page-generator'); ?></th>                 
                   <th><?php _e('Actions', 'wp-mosaic-page-generator'); ?></th>                   
                   
               </tr>
           </thead>
           
           <tbody>
           
           <?php 
           $i = 1;
           foreach($cptRows as $cpt) {  
            
            $cptCPFRows = $auxCPF->get_all_custom_fields($cpt->cpt_id);
            $cpf_qty = count($cptCPFRows);
              
           ?>             

               <tr id="acc-row-<?php echo $cpt->cpt_id?>">
                   <td><?php echo  $i; ?></td>
                   <td><?php echo esc_attr($cpt->cpt_name); ?></td>  
                   <td><?php echo esc_attr($cpt->cpt_unique_key); ?></td> 
                   <td><?php echo $cpf_qty; ?></td>                  
                   <td>&nbsp;
                   <a href="?page=wpmpg&tab=cpt-cpf&id=<?php echo esc_attr($cpt->cpt_id)?>"   title="<?php _e('Fields','wp-mosaic-page-generator'); ?>"><i class="fa fa-bars"></i></a> 
                   <a href="?page=wpmpg&tab=cpt-edit&id=<?php echo esc_attr($cpt->cpt_id)?>"   title="<?php _e('Edit','wp-mosaic-page-generator'); ?>"><i class="fa fa-edit"></i></a> 
                   <a href="#" class="wpmpg-int-delete-acc" acc-id="<?php echo esc_attr($cpt->cpt_id)?>" title="<?php _e('Delete','wp-mosaic-page-generator'); ?>"><i class="fa fa-trash-o"></i></a></td>
                  
               </tr>
               
               
               <?php

                $i++;
             }
                   
                   } else {
           ?>
           <p><?php _e('There are no custom post types yet.','wp-mosaic-page-generator'); ?></p>
           <?php	} ?>

           </tbody>
       </table>
       
       
       </div>
   	
</div>

     

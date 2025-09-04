<?php
global $wpmpg;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$auxNewPCT = new WpMosaicCPT(); 
if(isset($_GET['id']) && $_GET['id']!=''){	
	$cpt_id = sanitize_text_field($_GET['id']);	
	$cpt = $auxNewPCT->getCPTWithSlug($cpt_id);
    $cpt_slug = $cpt->cpt_unique_key;
   
    if(!isset($cpt->cpt_id)){
        echo 'error';
        exit;
    } 

}else{ 	
  $message =  '<div class="wpmpg-ultra-warning"><span><i class="fa fa-check"></i>'.__("Oops! Invalid CPT.",'wp-mosaic-page-generator').'</span></div>';
  echo wp_kses($message, $wpmpg->allowed_html);
  exit;		
}
$auxTaxo = new WpMosaicTaxo();
$cptRows = $auxTaxo->getCTPTaxo($cpt_slug );        
?>
<div class="wpmpg-welcome-panel">
    <div class="row item first uploader">
        <div class="col col-9 info mid">
            <div class="row label"><?php _e('Taxonomies for', 'wp-mosaic-page-generator'); ?> <?php echo $cpt->cpt_name;?></div>
            <div class="row description"><?php _e('Here you can manage taxonomies created by the plugin.', 'wp-mosaic-page-generator'); ?></div>
        </div>
        <div class="col col-3 click">
            <a href="?page=wpmpg&tab=cpt">
                <button type="button"><?php _e('Back', 'wp-mosaic-page-generator'); ?></button>
            </a>
        </div>
    </div>      

 <div class="row post-types">        
        <?php if (!empty($cptRows)){ ?>      
          <table width="100%" class="wp-list-table widefat fixed posts table-generic">
           <thead>
               <tr>
                   <th width="2%"><?php _e('#', 'wp-mosaic-page-generator'); ?></th>
                   <th><?php _e('Label', 'wp-mosaic-page-generator'); ?></th>  
                   <th><?php _e('Slug', 'wp-mosaic-page-generator'); ?></th> 
                   <th><?php _e('Terms', 'wp-mosaic-page-generator'); ?></th>               
                   <th class="actions"><?php _e('Actions', 'wp-mosaic-page-generator'); ?></th>                   
               </tr>
           </thead>           
           <tbody>           
           <?php 
           $i = 1;
           foreach($cptRows as $cpt) {               
            $terms = count($auxTaxo ->getTaxpTerms($cpt->tax_slug ));
           ?>             

               <tr id="acc-row-<?php echo $cpt->tax_id ?>">
                   <td><?php echo  $i; ?></td>
                   <td><?php echo esc_attr($cpt->tax_label); ?></td> 
                   <td><?php echo esc_attr($cpt->tax_slug ); ?></td>  
                   <td><?php echo $terms; ?></td>                                  
                   <td class="actions">&nbsp;
                  <a class="left" href="?page=wpmpg&tab=cpt-taxo-terms&slug=<?php echo esc_attr($cpt->tax_slug)?>&taxonomy=<?php echo esc_attr($cpt_id)?>"   title="<?php _e('See Terms','wp-mosaic-page-generator'); ?>">See Terms</a>  <a href="#" class="wpmpg-int-delete-taxo right delete" acc-id="<?php echo esc_attr($cpt->tax_id)?>" title="<?php _e('Delete','wp-mosaic-page-generator'); ?>"><i class="fa fa-trash-o"></i></a> 
                  
                </td>
               </tr>              
               
               <?php
                $i++;
             }
                   
                   } else {
           ?>
            <p><?php _e('There are no custom taxonomies.','wp-mosaic-page-generator'); ?></p>
           <?php	} ?>

           </tbody>
       </table>     
       
       </div>
</div>
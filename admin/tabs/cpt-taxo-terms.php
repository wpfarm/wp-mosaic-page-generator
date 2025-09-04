<?php
global $wpmpg;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$auxNewPCT = new WpMosaicTaxo(); 
if(isset($_GET['slug']) && $_GET['slug']!=''){	
	$taxo_slug = sanitize_text_field($_GET['slug']);	
	$cpt = $auxNewPCT->geTaxoWithSlug($taxo_slug);
 
   
    if(!isset($cpt->tax_id)){
        echo 'error';
        exit;
    } 

    $taxo = $_GET['taxonomy'] ?? '';

}else{ 	
  $message =  '<div class="wpmpg-ultra-warning"><span><i class="fa fa-check"></i>'.__("Oops! Invalid SLUG.",'wp-mosaic-page-generator').'</span></div>';
  echo wp_kses($message, $wpmpg->allowed_html);
  exit;		
}

$cptRows = $auxNewPCT->getTaxpTerms($taxo_slug );        
?>
<div class="wpmpg-welcome-panel">
    <div class="row item first uploader">
        <div class="col col-9 info mid">
            <div class="row label"><?php _e('Terms for', 'wp-mosaic-page-generator'); ?><strong> "<?php echo $cpt->tax_label;?>"" </strong><?php _e('taxonomy', 'wp-mosaic-page-generator'); ?></div>
            <div class="row description"><?php _e('Here you can see all the terms for this taxonomy.', 'wp-mosaic-page-generator'); ?></div>
        </div>
        <div class="col col-3 click">
            <a href="?page=wpmpg&tab=cpt-taxo&id=<?php echo $taxo?>">
                <button type="button"><?php _e('Back', 'wp-mosaic-page-generator'); ?></button>
            </a>
        </div>
    </div>      

 <div class="row post-types">        
        <?php if (!empty($cptRows)){ ?>      
          <table width="100%" class="wp-list-table widefat fixed posts table-generic">
           <thead>
               <tr>
                 
                   <th><?php _e('Name', 'wp-mosaic-page-generator'); ?></th>  
                   <th><?php _e('Slug', 'wp-mosaic-page-generator'); ?></th> 
                   <th><?php _e('Posts', 'wp-mosaic-page-generator'); ?></th>               
                         
               </tr>
           </thead>           
           <tbody>           
           <?php 
           $i = 1;
           foreach($cptRows as $cpt) {               
            $posts = 0;
           ?>             

               <tr id="acc-row-<?php echo $cpt->term_id ?>">
                   <td><?php echo esc_attr($cpt->term_name); ?></td>                      
                   <td><?php echo esc_attr($cpt->term_wp_slug); ?></td>    
                   <td><?php echo $posts; ?></td>                                  
                   
               </tr>              
               
               <?php
                $i++;
             }
                   
                   } else {
           ?>
            <p><?php _e('There are no terms for this taxonomy.','wp-mosaic-page-generator'); ?></p>
           <?php	} ?>

           </tbody>
       </table>     
       
       </div>
</div>
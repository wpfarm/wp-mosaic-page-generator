<?php
class  WpMosaicTaxo extends wpmpgCommon {
    var $ajax_prefix = 'wpmpg';
    var $errors = array();
	var $sucess_message = '';

	public function __construct(){

		add_action( 'wp_ajax_' . $this->ajax_prefix . '_delete_taxonomy', array( $this, 'deleteTaxonomy' ));	
    }	

	public function deleteTaxonomy()	{
		global $wpdb;

		$id = $_POST['acc_id'] ?? '';
		$id = sanitize_text_field($id);	

		if($id==""){
			exit;
		}		
	
        $sql = 'SELECT * FROM ' . $wpdb->prefix . 'cpt_taxonomies  WHERE tax_id = %d ' ;
		$query = $wpdb->prepare( $sql,	$id );
		$taxo = $wpdb->get_row($query);	

		if($taxo){
			$taxo_id = $taxo->tax_id ;	
			$taxo_slug = $taxo->tax_slug ;

			$cptRows = $this->getTaxpTerms($taxo_slug );   

			foreach ($cptRows as $term) {	

				//delete term in WP system
				wp_delete_term( $term->term_wp_id , $taxo_slug  );

				//delete term in Mosaic system
				$wpdb->delete( 
					$wpdb->prefix . 'cpt_taxonomy_terms', 
					[ 'slug' => $term->term_wp_slug ], 
					[ '%s' ] 
				);

			}
			
			
			
		}

		//delete taxonomy
		$wpdb->delete( 
			$wpdb->prefix . 'cpt_taxonomies', 
			[ 'tax_id' => $id  ], 
			[ '%d' ] 
		);

		die();
	}
    
	public function getCTPTaxo($slug)	{
		global $wpdb;
		$sql = 'SELECT * FROM ' . $wpdb->prefix . 'cpt_taxonomies  WHERE tax_cpt_slug  LIKE  "%'.$slug.'%";' ;	      
		return $wpdb->get_results( $sql);		
	}

	public function geTaxoWithSlug($slug)	{
		global $wpdb;
		$sql = 'SELECT * FROM ' . $wpdb->prefix . 'cpt_taxonomies  WHERE tax_slug  =  "'.$slug.'";' ;	 
		//echo $sql;     
		$res= $wpdb->get_results( $sql);		

		if ( !empty( $res ) ){
			foreach ( $res as $row ){				
				return $row;			
			}
		}	

	}

    public function getTaxpTerms ($slug)	{
		global $wpdb;
		$sql = 'SELECT * FROM ' . $wpdb->prefix . 'cpt_taxonomy_terms  WHERE term_taxo_slug  LIKE  "%'.$slug.'%";' ;	      
		return $wpdb->get_results( $sql);		
	}   	
}
?>
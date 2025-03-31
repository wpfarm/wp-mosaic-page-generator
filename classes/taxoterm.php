<?php
class  WpMosaicTaxo extends wpmpgCommon {
    var $ajax_prefix = 'wpmpg';
    var $errors = array();
	var $sucess_message = '';

	public function __construct(){
    }	
    
	public function getCTPTaxo($slug)	{
		global $wpdb;
		$sql = 'SELECT * FROM ' . $wpdb->prefix . 'cpt_taxonomies  WHERE tax_cpt_slug  LIKE  "%'.$slug.'%";' ;	      
		return $wpdb->get_results( $sql);		
	}

    public function getTaxpTerms ($slug)	{
		global $wpdb;
		$sql = 'SELECT * FROM ' . $wpdb->prefix . 'cpt_taxonomy_terms  WHERE term_taxo_slug  LIKE  "%'.$slug.'%";' ;	      
		return $wpdb->get_results( $sql);		
	}     
	
}
?>
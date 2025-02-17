<?php
class  WpMosaicCPT extends wpmpgCommon {
    var $ajax_prefix = 'wpmpg';
    var $errors = array();
	var $sucess_message = '';

	public function __construct(){
		$this->handle_init_a();       
    }	

    function handle_init_a() 	{
		
		if (isset($_POST['wpmpg_create_cpt'])) {
			$this->handle_creation();
		}
		
		if (isset($_POST['wpmpg_edit_cpt'])) {
			$this->handle_edition();
		}

		if (isset($_POST['wpmpg_create_cpt_cpf'])) {
			$this->handle_creation_cpf();
		}

		if (isset($_POST['wpmpg_edit_cpt_cpf'])) {
			$this->handle_update_cpf();
		}
		
	}

	function handle_update_cpf(){			
		global $wpmpg, $wpdb;	
		
		if(!isset($_POST['cpf_field_type']) || $_POST['cpf_field_type']==''){
			$this->errors[] = __('<strong>ERROR:</strong> Please set a type.','wp-mosaic-page-generator');

		}elseif(!isset($_POST['cpf_cpt_id']) || $_POST['cpf_cpt_id']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please set a post type id.','wp-mosaic-page-generator');
		
		}elseif(!isset($_POST['cpf_field_label']) || $_POST['cpf_field_label']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input a label.','wp-mosaic-page-generator');
			
		}elseif(!isset($_POST['cpf_field_name']) || $_POST['cpf_field_name']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input field name.','wp-mosaic-page-generator');	
			
		}else{

            $record = array(               
                'cpf_field_type' => $wpmpg->get_post_value('cpf_field_type'),
                'cpf_field_label' => $wpmpg->get_post_value('cpf_field_label'),
                'cpf_field_name' =>$wpmpg->get_post_value('cpf_field_name'),
                'cpf_field_default_value' => $wpmpg->get_post_value('cpf_field_default_value'));
			
			$data_where = array('cpf_id' => $wpmpg->get_post_value('cpf_id'));
			$wpdb->update( $wpdb->prefix .'cpt_fields',  $record, $data_where );
			$this->sucess_message = '<div class="wpmpg-ultra-success"><span><i class="fa fa-check"></i>'.__("The post field was successfully updated.",'wp-mosaic-page-generator').'</span></div>';
		}
	}

	function handle_creation_cpf(){	
		global $wpmpg, $wpdb;		
		
		if(!isset($_POST['cpf_field_type']) || $_POST['cpf_field_type']==''){
			$this->errors[] = __('<strong>ERROR:</strong> Please set a type.','wp-mosaic-page-generator');

		}elseif(!isset($_POST['cpf_cpt_id']) || $_POST['cpf_cpt_id']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please set a post type id.','wp-mosaic-page-generator');
		
		}elseif(!isset($_POST['cpf_field_label']) || $_POST['cpf_field_label']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input a label.','wp-mosaic-page-generator');
			
		}elseif(!isset($_POST['cpf_field_name']) || $_POST['cpf_field_name']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input field name.','wp-mosaic-page-generator');	
			
		}else{

            $new_record = array(
				'cpf_id' => NULL,
                'cpf_cpt_id' => $wpmpg->get_post_value('cpf_cpt_id'),
                'cpf_field_type' => $wpmpg->get_post_value('cpf_field_type'),
                'cpf_field_label' => $wpmpg->get_post_value('cpf_field_label'),
                'cpf_field_name' =>$wpmpg->get_post_value('cpf_field_name'),
                'cpf_field_default_value' => $wpmpg->get_post_value('cpf_field_default_value'));
			$wpdb->insert( $wpdb->prefix .'cpt_fields', $new_record, 
			array( '%d', '%s' , '%s' , '%s' , '%s' , '%s'));
			$this->sucess_message = '<div class="wpmpg-ultra-success"><span><i class="fa fa-check"></i>'.__("The post field was successfully created.",'wp-mosaic-page-generator').'</span></div>';
		}
	}

    function handle_creation(){		
		global $wpmpg, $wpdb;	
		if(!isset($_POST['cpt_name']) || $_POST['cpt_name']==''){
			$this->errors[] = __('<strong>ERROR:</strong> Please input Name.','wp-mosaic-page-generator');

		}elseif(!isset($_POST['cpt_unique_key']) || $_POST['cpt_unique_key']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input a unique custom post type slug.','wp-mosaic-page-generator');
		
		}elseif($wpmpg->cpt_exists($_POST['cpt_unique_key'])){
			
			$this->errors[] = __('<strong>ERROR:</strong> The Custom Post Type already exists!.','wp-mosaic-page-generator');
		
		}elseif(!isset($_POST['singular_name']) || $_POST['singular_name']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input singular name.','wp-mosaic-page-generator');
			
		}elseif(!isset($_POST['add_new']) || $_POST['add_new']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input add new label.','wp-mosaic-page-generator');

		}elseif(!isset($_POST['add_new_item']) || $_POST['add_new_item']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input add new item label.','wp-mosaic-page-generator');

		}elseif(!isset($_POST['edit_item']) || $_POST['edit_item']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input add edit item label.','wp-mosaic-page-generator');

		}elseif(!isset($_POST['new_item']) || $_POST['new_item']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input add new item label.','wp-mosaic-page-generator');
	
		}elseif(!isset($_POST['view_item']) || $_POST['view_item']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input add new item label.','wp-mosaic-page-generator');
		
		}elseif(!isset($_POST['search_items']) || $_POST['search_items']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input search items label.','wp-mosaic-page-generator');

		}elseif(!isset($_POST['not_found']) || $_POST['not_found']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input not found label.','wp-mosaic-page-generator');
		
		}elseif(!isset($_POST['not_found_in_trash']) || $_POST['not_found_in_trash']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input not found in trash label.','wp-mosaic-page-generator');		
			
		}else{

            $projects_labels = array(
                'name' => $wpmpg->get_post_value('cpt_name'),
                'singular_name' => $wpmpg->get_post_value('singular_name'),
                'add_new' => $wpmpg->get_post_value('add_new'),
                'add_new_item' =>$wpmpg->get_post_value('add_new_item'),
                'edit_item' => $wpmpg->get_post_value('edit_item'),
                'new_item' => $wpmpg->get_post_value('new_item'),
                'view_item' => $wpmpg->get_post_value('view_item'),
                'search_items' => $wpmpg->get_post_value('search_items'),
                'not_found' => $wpmpg->get_post_value('not_found'),
                'not_found_in_trash' => $wpmpg->get_post_value('not_found_in_trash'),
                'parent_item_colon' => '');								
			
			$new_record = array('cpt_id' => NULL,	
								'cpt_name' =>$wpmpg->get_post_value('cpt_name'),
								'cpt_unique_key' =>$wpmpg->get_post_value('cpt_unique_key'),								
								'cpt_properties' => json_encode( $projects_labels),								
									
								);	
								
																	
			$wpdb->insert( $wpdb->prefix .'cpt', $new_record, 
			array( '%d', '%s' , '%s' , '%s'));
			$this->sucess_message = '<div class="wpmpg-ultra-success"><span><i class="fa fa-check"></i>'.__("The post types was successfully created.",'wp-mosaic-page-generator').'</span></div>';
		}
	}

	function handle_edition(){		
		global $wpmpg, $wpdb;	
		
		if(!isset($_POST['cpt_name']) || $_POST['cpt_name']==''){
			$this->errors[] = __('<strong>ERROR:</strong> Please input Name.','wp-mosaic-page-generator');

		}elseif(!isset($_POST['cpt_unique_key']) || $_POST['cpt_unique_key']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input a unique custom post type slug.','wp-mosaic-page-generator');
		
		}elseif(!isset($_POST['singular_name']) || $_POST['singular_name']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input singular name.','wp-mosaic-page-generator');
			
		}elseif(!isset($_POST['add_new']) || $_POST['add_new']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input add new label.','wp-mosaic-page-generator');

		}elseif(!isset($_POST['add_new_item']) || $_POST['add_new_item']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input add new item label.','wp-mosaic-page-generator');

		}elseif(!isset($_POST['edit_item']) || $_POST['edit_item']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input add edit item label.','wp-mosaic-page-generator');

		}elseif(!isset($_POST['new_item']) || $_POST['new_item']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input add new item label.','wp-mosaic-page-generator');
	
		}elseif(!isset($_POST['view_item']) || $_POST['view_item']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input add new item label.','wp-mosaic-page-generator');
		
		}elseif(!isset($_POST['search_items']) || $_POST['search_items']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input search items label.','wp-mosaic-page-generator');

		}elseif(!isset($_POST['not_found']) || $_POST['not_found']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input not found label.','wp-mosaic-page-generator');
		
		}elseif(!isset($_POST['not_found_in_trash']) || $_POST['not_found_in_trash']==''){
			
			$this->errors[] = __('<strong>ERROR:</strong> Please input not found in trash label.','wp-mosaic-page-generator');		
			
		}else{

            $projects_labels = array(
                'name' => $wpmpg->get_post_value('cpt_name'),
                'singular_name' => $wpmpg->get_post_value('singular_name'),
                'add_new' => $wpmpg->get_post_value('add_new'),
                'add_new_item' =>$wpmpg->get_post_value('add_new_item'),
                'edit_item' => $wpmpg->get_post_value('edit_item'),
                'new_item' => $wpmpg->get_post_value('new_item'),
                'view_item' => $wpmpg->get_post_value('view_item'),
                'search_items' => $wpmpg->get_post_value('search_items'),
                'not_found' => $wpmpg->get_post_value('not_found'),
                'not_found_in_trash' => $wpmpg->get_post_value('not_found_in_trash'),
                'parent_item_colon' => '');			
		
			$new_record = array(	
								'cpt_name' =>$wpmpg->get_post_value('cpt_name'),
								'cpt_unique_key' =>$wpmpg->get_post_value('cpt_unique_key'),								
								'cpt_properties' => json_encode( $projects_labels),								
									
								);	

			$data_where = array('cpt_id' => $wpmpg->get_post_value('cpt_id'));
			$wpdb->update( $wpdb->prefix .'cpt', $new_record, $data_where);							
			$this->sucess_message = '<div class="wpmpg-ultra-success"><span><i class="fa fa-check"></i>'.__("The post was successfully updated.",'wp-mosaic-page-generator').'</span></div>';
		}
	}

	/*Get errors display*/
	function get_errors() {
		global $wpmpg;		
		$display = null;		
		if (isset($this->errors) && is_array($this->errors) && count($this->errors) >0)  {
		    $display .= '<div class="wpmpg-ultra-error">';		
			foreach($this->errors as $newError) {
				$display .= '<span class="wpmpg-error userscontrol-error-block"><i class="wpmpg-icon-remove"></i>'.$newError.'</span>';
			}
		$display .= '</div>';
		

		}
		return $display;
	}

	public function get_all ()	{
		global $wpdb;
		$sql = 'SELECT * FROM ' . $wpdb->prefix . 'cpt ' ;			
		return $wpdb->get_results( $sql);		
	}

	public function get_all_custom_fields ($cpt_id)	{
		global $wpdb;
		$sql = 'SELECT * FROM ' . $wpdb->prefix . 'cpt_fields WHERE cpf_cpt_id = "'.(int)$cpt_id.'"' ;		
		$sql .= " ORDER BY  cpf_field_sorting ";
		return $wpdb->get_results( $sql);		
	}

	public function get_one ($id)	{
		global $wpdb;
		$sql = ' SELECT * FROM ' . $wpdb->prefix . 'cpt ' ;			
		$sql .= ' WHERE cpt_id = "'.(int)$id.'"' ;	
				
		$res = $wpdb->get_results($sql);		
		if ( !empty( $res ) ){
			foreach ( $res as $row ){
				return $row;			
			}
		}	
		
	}

	public function delete_one_cpf ($id)	{
		global $wpdb;
		$sql = 'SELECT * FROM ' . $wpdb->prefix . 'cpt ' ;			
		$sql .= ' WHERE cpt_id = "'.(int)$id.'"' ;	
				
		$res = $wpdb->get_results($sql);		
		if ( !empty( $res ) ){
			foreach ( $res as $row ){
				return $row;			
			}
		}
	}

	public function get_one_cpf ($id)	{
		global $wpdb;
		$sql = 'SELECT * FROM ' . $wpdb->prefix . 'cpt_fields ' ;			
		$sql .= ' WHERE cpf_id = "'.(int)$id.'"' ;	
				
		$res = $wpdb->get_results($sql);		
		if ( !empty( $res ) ){
			foreach ( $res as $row ){
				return $row;			
			}
		}	
	}
}
?>
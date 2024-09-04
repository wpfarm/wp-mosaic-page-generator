<?php
class WpMosaicPageGenerator extends wpmpgCommon {
	
	var $wp_all_pages = false;
	public $classes_array = array();	
	var $notifications_email = array();
	var $wpmpg_default_options;	
	var $ajax_prefix = 'wpmpg';	
	var $allowed_inputs = array();	
	public $allowed_html;

	var $tblHeaders;	
	var $tblRows;			
	var $tblCombinedRows;
	public function __construct()	{			
			
		$this->slug = 'wpmpg';			
		$this->ini_module();	
		$this->update_default_option_ini();		
		$this->set_allowed_html();		
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$this->plugin_data = get_plugin_data( wpmpg_path . 'index.php', false, false);
		$this->version = $this->plugin_data['Version'];		
		add_action('admin_menu', array(&$this, 'add_menu'), 11);
		add_action('admin_head', array(&$this, 'admin_head'), 13 );
		add_action('admin_init', array(&$this, 'admin_init'), 15);			
		add_action('admin_enqueue_scripts', array(&$this, 'add_styles'), 12);
		add_action('add_meta_boxes', array($this, 'post_add_meta_box' ));	
		add_action('init', array($this, 'ini_group_modules_pro'));	
		add_action( 'wp_ajax_'.$this->ajax_prefix.'_upload_file',  array( $this, 'upload_file_parse' ));
		add_action( 'wp_ajax_'.$this->ajax_prefix.'_download_url_file',  array( $this, 'download_file_from_url' ));
		add_action( 'wp_ajax_'.$this->ajax_prefix.'_start_process',  array( $this, 'start_import_ajax' ));	
		add_action( 'wp_ajax_'.$this->ajax_prefix.'_delete_cpf',  array( $this, 'delete_cpf' ));
		add_action( 'wp_ajax_'.$this->ajax_prefix.'_delete_cpf_value',  array( $this, 'delete_cpf_value' ));

    }	


	function post_add_meta_box(){	
		
		add_meta_box('wpmosaic_customfields_data', 'WP Mosaic Custom Fields', array($this, 'getPostMetaFields'), null,  'advanced' ,  'high');
	}
	
	
	public function ini_module(){
		global $wpdb;		
		$query = '
			CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'cpt(
			  `cpt_id` int(11) NOT NULL AUTO_INCREMENT,				 			
			  `cpt_name` varchar(200) NOT NULL,		
			  `cpt_unique_key` varchar(100) NOT NULL,
			  `cpt_properties` text NOT NULL,	
			  `cpt_status` int(1) NOT NULL DEFAULT 1,		  			 	  
			  PRIMARY KEY (`cpt_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
			';
			
		$wpdb->query( $query );	

		$query = '
			CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'cpt_fields(
			  `cpf_id` int(11) NOT NULL AUTO_INCREMENT,	
			  `cpf_cpt_id` int(11) NOT NULL ,			 			
			  `cpf_field_type` int(2) NOT NULL DEFAULT 1,
			  `cpf_field_sorting` int(3) NOT NULL DEFAULT 1,	
			  `cpf_field_label` varchar(200) NOT NULL,		
			  `cpf_field_name` varchar(100) NOT NULL,
			  `cpf_field_default_value` varchar(100) NOT NULL,		  			 	  
			  PRIMARY KEY (`cpf_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
			';
			
		$wpdb->query( $query );	
	}

	// File upload handler:
	function upload_file_parse(){
						
		$site_url = site_url()."/";			
		// Check referer, die if no ajax:
		check_ajax_referer('photo-upload');		
		
		/// Upload file using Wordpress functions:
		$file = $_FILES['file'];			
						
		$info = pathinfo($file['name']);
		$real_name = $file['name'];
		$ext = $info['extension'];
		$ext=strtolower($ext);
	
		$upload_temp_folder = WPMPG_UPLOAD_FOLDER;		
				
		//$rand = $this->genRandomString(5);
		$rand_name = "floor_plan_temp_".$rand."_".session_id()."_".time();
		$rand_name = $real_name;		
			
		$upload_dir = wp_upload_dir(); 
		$path_pics =   $upload_dir['basedir'];		
					
		if($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif' || $ext == 'csv'){
				
				if(!is_dir($path_pics.'/'.$upload_temp_folder)) {
					wp_mkdir_p( $path_pics.'/'.$upload_temp_folder );							   
				}			
												
				$pathBig = $path_pics.'/'.$upload_temp_folder."/".$rand_name.".".$ext;							
							
				if (copy($file['tmp_name'], $pathBig)){		
					
					$new_avatar = $rand_name.".".$ext;				
				}				
					
		} // image type
				
		// Create response array:
		$uploadResponse = array('image' => $new_avatar);
				
		// Return response and exit:
		echo json_encode($uploadResponse);
		die();
	}

	// File upload handler:
	function download_from_url($url, $meta_slug){
						
		$image = file_get_contents($url);			
		$upload_temp_folder = WPMPG_UPLOAD_FOLDER;				

		//$rand_name = basename($url);
		$rand_name =$meta_slug;		

		$parsedUrl = parse_url($url);
		$pathInfo = pathinfo($parsedUrl['path']);

		$real_name = $pathInfo['basename'];
		$ext = $pathInfo['extension'];
		$ext=strtolower($ext);
			
		$upload_dir = wp_upload_dir(); 
		$path_pics =   $upload_dir['basedir'];		
					
		if($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif' ){
				
			if(!is_dir($path_pics.'/'.$upload_temp_folder)) {
				wp_mkdir_p( $path_pics.'/'.$upload_temp_folder );							   
			}
			
			//check if image name already exists/												
			//$pathBig = $path_pics.'/'.$upload_temp_folder."/".$rand_name;

			//if(file_exists($pathBig)){ //we need to rename it with image slug

				$pathBig = $path_pics.'/'.$upload_temp_folder."/".$meta_slug.'.'.$ext;
			//}		
			

			file_put_contents($pathBig, $image);
					
		} // image type
		return $pathBig;
	}

	function analize_url_to_download($url){

		$parsedUrl = parse_url($url);
		$pathInfo = pathinfo($parsedUrl['path']);

		$real_name = $pathInfo['basename'];
		$dir_name = $pathInfo['dirname'];
		$ext = $pathInfo['extension'];
		$ext=strtolower($ext);

		// Check if the URL is Google Sheet "example"
		if (strpos($url, "https://docs.google.com/spreadsheets") !== false) {

			$url = 'https://docs.google.com'.$dir_name.'/export?format=csv';
		} else {
			//echo "The URL does not contain the string 'tutor'.";
		}

		return $url;

	}

	function download_file_from_url(){

		$url = $_POST['link_to_download'] ?? '';
		$url = $this->analize_url_to_download($url);
						
		$image = file_get_contents($url);			
		$upload_temp_folder = WPMPG_UPLOAD_FOLDER;				

		$parsedUrl = parse_url($url);
		$pathInfo = pathinfo($parsedUrl['path']);
		
		$real_name = $pathInfo['basename'];
		$ext = $pathInfo['extension'];
		$ext='csv';
			
		$upload_dir = wp_upload_dir(); 
		$path_pics =   $upload_dir['basedir'];	
		
		$rand = $this->genRandomString(5);
		$rand_name = "import_".$rand."_".session_id()."_".time().'.'.$ext;
					
		if($ext == 'csv' ){
				
			if(!is_dir($path_pics.'/'.$upload_temp_folder)) {
				wp_mkdir_p( $path_pics.'/'.$upload_temp_folder );							   
			}			
												
			$pathBig = $path_pics.'/'.$upload_temp_folder."/".$rand_name;	
			file_put_contents($pathBig, $image);
					
		} // image type
		// Create response array:
		$uploadResponse = array('image' => $rand_name);
				
		// Return response and exit:
		echo json_encode($uploadResponse);
		die();
	}

	public function genRandomString($length = 5){		
		$characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWZYZ";	
		$real_string_legnth = strlen($characters) ;			
		$string="ID";	
		for ($p = 0; $p < $length; $p++){
			$string .= $characters[mt_rand(0, $real_string_legnth-1)];
		}	
		return strtolower($string);
	}

	public function get_all_post_types(){
		global $wpdb;
		$sql = 'SELECT * FROM ' . $wpdb->prefix . 'cpt ' ;			
		return $wpdb->get_results( $sql);	
	}

	public function delete_cpf(){
		global $wpdb;
		$cpf = $_POST['acc_id'] ?? '';
		if($cpf=='') echo 'error';
		$wpdb->delete( $wpdb->prefix . 'cpt', [ 'cpt_id'=>$cpf ], [ '%d' ] );
		echo 'deleted: '. $cpf;
		die();
	}

	public function delete_cpf_value(){
		global $wpdb;
		$cpf = $_POST['acc_id'] ?? '';
		if($cpf=='') echo 'error';
		$wpdb->delete( $wpdb->prefix . 'cpt_fields', [ 'cpf_id'=>$cpf ], [ '%d' ] );
		echo 'deleted: '. $cpf;
		die();
	}

	public function start_import_ajax(){
		global $wpdb;
		
		$file = $_POST['file_uploaded'] ?? '';	
	
		$this->import_from_url($file);	

		$headers = $this->tblHeaders;
		$rowsDATA = $this->tblRows;
		$rowsDOrigin = $this->tblRows;	
		$rowsCombined = $this->tblCombinedRows;		

		$only_metas = array_slice($headers, 4, count($headers), false); 

		$batch_from = $_POST['batch'] ?? 1;
		$last_row = $_POST['last'] ?? 0;				
		
		$cut_from = $last_row;
		$cut_to = $batch_from;

		if($cut_to > count($rowsDATA)){
			$cut_to = count($rowsDATA);
		}
		
		if($last_row >= count($rowsDATA)){
			$status_process = 0;			
		}else{
			$status_process = 1;
		}	

		$rowsDATASliced = array_slice($rowsDATA, $cut_from ,$cut_to, false); 
		$rowsCombined = array_slice($rowsCombined, $cut_from ,$cut_to, false); 

		$total_processed_rows = $last_row + $batch_from;
		$percent_a = round(($total_processed_rows * 100) / count($rowsDATA));	

		$act_update_new = $_POST['act_update_new'] ?? '';
		$wp_custom_post_type = $_POST['wp-custom-post-type'] ?? '';
		$fields_type_col = $this->getPostMetaFieldsCol($wp_custom_post_type);		

		$count = 0;
		$rowCount =1;

		$rowsDATA = $rowsDATASliced;			
		foreach ( $rowsDATA  as $data ) {		
			
			$post_type = $wp_custom_post_type;
			$post_id = $rowsDATA[$count][0];
			$post_title = $rowsDATA[$count][1];
			$post_slug = $rowsDATA[$count][2];
			//$post_desc = $rowsDATA[$count][3];	
			$meta_desc = $rowsDATA[$count][3];
			$post_desc ='';		
			$post_excerpt = '';		
			
			$rowCount++;			
			$meta_input = array();		

			//create post					
			$user_id = get_current_user_id();
				
			// Create post object
			$my_post = array(
				'post_title'    => $post_title,
				'post_content'  => $post_desc,
				'post_excerpt'  => $post_excerpt,
				//'guid'  => $post_slug,	
				'post_name'  => $post_slug,			
				'post_status'   => 'publish',
				'post_author'   => $user_id,
				'post_type'   => $post_type	,				
			
			);

			$post_exists = $this->the_slug_exists($post_slug, $post_type);	

			if(!$post_exists){

				$update_post = false;
				// Insert the post into the database
				$post_id = wp_insert_post( $my_post );	
						
				update_post_meta( $post_id, '_yoast_wpseo_title',$post_title );
				update_post_meta( $post_id, '_yoast_wpseo_metadesc',$meta_desc );
				update_post_meta( $post_id, '_yoast_wpseo_focuskw',$post_title );
				update_post_meta( $post_id, 'description_wpmosaic',$meta_desc );
				update_post_meta( $post_id, 'rank_math_description',$meta_desc );	
		
				$i = 4;			
				foreach ( $only_metas  as $meta_key ) {
					$val_import = $rowsDATA[$count][$i] ?? '';
					if(isset( $fields_type_col[$meta_key])) { //if there is a custom field for this post type

						$custom_field = $fields_type_col[$meta_key];

						if($custom_field['cpf_field_type']==1){ //text

							update_post_meta( $post_id, $meta_key,$val_import );

						}else{ //image		
							
							$img_val = '<img src="'.$val_import.'">';
							update_post_meta( $post_id, $meta_key,$img_val );

							$meta_alternative_text= $meta_key.'_alternative_text';
							$meta_title= $meta_key.'_title';
							$meta_slug= $meta_key.'_slug';
							$meta_description= $meta_key.'_description';

							$meta_text = $rowsCombined[$count][$meta_alternative_text] ?? '';
							$meta_slug = $rowsCombined[$count][$meta_slug] ?? '';
							$meta_title = $rowsCombined[$count][$meta_title] ?? '';
							$meta_description = $rowsCombined[$count][$meta_description] ?? '';						

							$img_path = $this->download_from_url($val_import, $meta_slug);



							$attach_id = $this->attach_image_to_project($img_path, $meta_title, $post_id, $user_id);
							set_post_thumbnail( $post_id, $attach_id );

							// Set the image Alt-Text
							update_post_meta( $attach_id, '_wp_attachment_image_alt', $meta_text );
							$my_image_meta = array(
								'ID'		=> $attach_id,			// Specify the image (ID) to be updated
								'post_title'	=> $meta_title,		// Set image Title to sanitized title
								'post_excerpt'	=> $meta_text,		// Set image Caption (Excerpt) to sanitized title
								'post_content'	=> $meta_description, // Set image Description (Content) to sanitized title
							);

							// Set the image meta (e.g. Title, Excerpt, Content)
							wp_update_post( $my_image_meta );

						}				
					
					}				
					
					$i++;
				}	

			}else{

				//$post_id = $post_exists[0]->ID;
				//$update_post = true;
				/*$my_post = array(
					'ID'		=> $post_id,	
					'post_title'    => $post_title,
					'post_content'  => $post_desc,
					'post_excerpt'  => $post_excerpt					
				
				);
				//wp_update_post( $my_post ); */

		    }	//enif if post exists
			$count++;
	    }	

		$response = array('status' => $status_process, 
						  'last' => $last_row, 
						  'percent_a' => $percent_a , 
						  'cut_from' => $cut_from  , 
						  'cut_to' => $cut_to , 
						  'total_rows' => count($rowsDOrigin),
						  'total_left' => count($rowsDATASliced),
						  'sliced_rows' => $rowsDATASliced);

		echo json_encode($response);

		die();
		
		
	
	}

	function the_slug_exists($post_name, $post_type) {
		global $wpdb;
		if($wpdb->get_row("SELECT post_name FROM ". $wpdb->prefix. "posts WHERE post_name = '" . $post_name . "' AND post_type = '" . $post_type . "'", 'ARRAY_A')) {
			return true;
		} else {
			return false;
		}
	}


	function get_post_id_by_slug( $slug, $post_type ) {
		global $wpdb;
		$sql = "SELECT ID FROM ". $wpdb->prefix. "posts where post_type='".$post_type."' AND post_name='".$slug."'";
		//echo "SQL : " . $sql ."<br>";
		return $wpdb->get_results($sql);
	}
	

	function attach_image_to_project($file_path, $file_name, $post_id  , $user_id){

		$mime_type = wp_get_image_mime($file_path);
		$attachment = array(
			'post_parent' =>$post_id,
			'post_author' =>$user_id,
			'post_mime_type' => $mime_type,
			'post_title' => $file_name,
			'post_content' => '',
			'post_status' => 'inherit'
		);
		
		$attach_id = wp_insert_attachment( $attachment, $file_path );				  
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
		wp_update_attachment_metadata( $attach_id, $attach_data );	

		return $attach_id;
		
	}

	public function build_data_table($file){
		global $wpdb;		

		$this->import_from_url($file);

		$headers = $this->tblHeaders;
		$rowsDATA = $this->tblRows;
		$rowsDOrigin = $this->tblRows;

		$act_update_new = $_POST['act_update_new'] ?? '';
		$wp_custom_post_type = $_POST['wp-custom-post-type'] ?? '';

		$html = ' <div class="msm-btn-steps-bar-steps"  >';

		$html = ' <div class="mspg-tbl-cont-header"  >';
			$html .='<h1><span>'.count($rowsDATA).'</span>'.__(' rows will be imported','image-notes-plus-WP'). '</h1>';
		$html .='</div>';

		$html .= ' <div class="mspg-tbl-cont msrm-panel" id="mspg-tbl-cont"  >';

		$count = 0;
		$rowCount =1;

		foreach ( $rowsDATA  as $data ) {		
			$html .='<table class="wp-list-table widefat  posts table-generic mspg-tbl-table">';
			$html .='<thead>';
			$html .='<tr id="wms-tbl-val-'.$count.'">';
				//build header
				$html .='<th class="wpmpg-field-tbl">'.__('Row','image-notes-plus-WP'). ' '.$rowCount.'</th>';
				$html .='<th id="wms-tbl-val-col-'.$count.'" class="wms-val-col">'.__('Value','image-notes-plus-WP'). '</th>';
			$html .='</tr>';
			$html .='</thead>';
			$html .='<tbody class="wpmosaic-import-tbl-body">	';

			$i = 0;			
			foreach ( $headers  as $key ) {

				$val_import = $rowsDATA[$count][$i] ?? 'N/A';
				$html .='<tr>';
					$html .='<td class="wpmpg-field-tbl">'.$key .'</td>';
					$html .='<td class="wpmpg-field-tbl-val">'.htmlentities($val_import).'</td>';
				$html .='</tr>';
				$i++;
			}			
			
			$rowCount++;
			$html .='</tbody>';
			$html .='</table>';
			$count++;
	    }

		$html .='</div>';
		$html .='<input type="hidden" id="act_update_new" name="act_update_new" value="'.$act_update_new.'">';
		$html .='<input type="hidden" id="wp-custom-post-type" name="wp-custom-post-type" value="'.$wp_custom_post_type.'">';
		$html .='<input type="hidden" id="wp-total-rows" name="wp-total-rows" value="'.count($rowsDATA).'">';
		
		$html .='<div class="wpm-progress-bar" id="wpm-progress-bar">';

		$html .='<div id="progressbar"><div class="progress-label">'.__('Loading...').'</div></div>
		<p class="msrm-stat-count">'. __('Imported') .': <span id="msrm-process-val">0</span> '.__('of') .' <span id="msrm-total-val">'.count($rowsDATA).'</span></p>';
	
		$html .='</div>';

		$html .='<div class="wpm-import-opt-bar" id="wpm-import-opt-bar">';
		   // $html .='<p>'.__('Batches').'</p>';
			$html .=''.__('Batches').': <input type="number" id="batch" name="batch" value="3">';

		
		$html .='</div>';

		$html .='<div class="wpm-import-results-block" id="wpm-import-results-block">';
		  
			$html .='<div class="wpm-import-opt-bar-msg" id="wpm-import-opt-bar-msg">';
			$html .='<h1>'.__('Success').'</h1>';
			$html .='<p><i class="fa fa-check sucess-check"></i></p>';
			$html .='<p>'.__('All the rows were imported.').'</p>';
			$html .= '<p><button name="wpmsaic-ok-finish-btn" id="wpmsaic-ok-finish-btn" class="btn mpg-download-link-btn btn-lg btn-primary btn-custom-primary" type="button"><span><i class="fa fa-thumbs-up"></i></span> '.__('OK','image-notes-plus-wp').' 	</button>	</p>';
			$html .='</div>';
		
		$html .='</div>';
		
		return $html;
	
	}

	public function getPostMetaFieldsCol($ptype)   {
		global $wpdb;		

		$sql =  'SELECT cpf.*, cpt.*  FROM ' . $wpdb->prefix . 'cpt_fields cpf  ' ;				
		$sql .= " RIGHT JOIN ". $wpdb->prefix."cpt  cpt  ON (cpt.cpt_id = cpf.cpf_cpt_id )";		
		$sql .= " WHERE cpt.cpt_id = cpf.cpf_cpt_id  AND  cpt.cpt_unique_key = %s   ";	
		$sql .= " ORDER BY  cpf.cpf_field_sorting ";	
		$sql = $wpdb->prepare($sql,array($ptype));
		$cptRows = $wpdb->get_results($sql );	

		$arrCol = array();

		foreach($cptRows as $cpt) {  			
			$arrCol[$cpt->cpf_field_name] = array('cpf_field_type' => $cpt->cpf_field_type, 'cpf_field_label' => $cpt->cpf_field_label);
		}		
		return $arrCol;

    }

	public function getPostMetaFields($oPost)   {

		global $wpdb;	
		
		$html = '';
			
        $iObjectId = $oPost->ID;			
		$oPost = get_post($iObjectId);
		$sObjectType = $oPost->post_type;			

		$sql =  'SELECT cpf.*, cpt.*  FROM ' . $wpdb->prefix . 'cpt_fields cpf  ' ;				
		$sql .= " RIGHT JOIN ". $wpdb->prefix."cpt  cpt  ON (cpt.cpt_id = cpf.cpf_cpt_id )";		
		$sql .= " WHERE cpt.cpt_id = cpf.cpf_cpt_id  AND  cpt.cpt_unique_key = %s   ";	
		$sql .= " ORDER BY  cpf.cpf_field_sorting ";	
		$sql = $wpdb->prepare($sql,array($sObjectType));


		$cptRows = $wpdb->get_results($sql );

		foreach($cptRows as $cpt) {  

			$key_collection = get_post_meta( $iObjectId, $cpt->cpf_field_name, true );
			$html .= '<div class="imnp-meta-cont">';
			if($cpt->cpf_field_type==1){
				$html .= '<p><strong>'.$cpt->cpf_field_label.'</strong></p>';
				$html .='<textarea rows="4" cols="40" style="width: 100%;" name="'.$cpt->cpf_field_name.'" id="'.$cpt->cpf_field_name.'" >'.$key_collection.'</textarea>';

			}else{

				

			}

			$html .= '</div>';	
		
		}	
		
		$html .= '<div class="imnp-meta-cont">';
		$html .= '<p><strong>'.__('Images').'</strong></p>';
		$custom = [ 'class' => 'my-class', 'alt' => 'alt text', 'title' => 'my title' ];

		$attachments = get_attached_media('image', $iObjectId);
		if (!empty($attachments)) {
			foreach ($attachments as $image) {
				$html .= wp_get_attachment_image($image->ID , 'medium', false, $custom);
			}
		} 

		$html .= '</div>';	
		echo $html;

    }

	public function cpt_exists($cpt){

		$res = false;
		
		$args = array(
			'public'   => true,
			'_builtin' => false,
		 );
	 
		 $output = 'names'; // names or objects, note names is the default
		 $operator = 'and'; // 'and' or 'or'
	 
		 $post_types = get_post_types( $args, 'objects' ); 		
	 
		 foreach ( $post_types  as $post_type ) {

			if($post_type->name==$cpt){

				return true;
			}			
		 }
				
		return 	  $res;				
	}

	
	public function get_all_c_postypes_list_box($id, $selected = null){

		$html  = '';

		$args = array(
			'public'   => true,
			'_builtin' => false,
		 );
	 
		 $output = 'names'; // names or objects, note names is the default
		 $operator = 'and'; // 'and' or 'or'
	 
		 $post_types = get_post_types( $args, 'objects' ); 

		 $html .= '<select name="'.$id.'" id="'.$id.'" class="form-control">';
	 
		 foreach ( $post_types  as $post_type ) {


			$sel = '';	

			$html .= '<option value="' . $post_type->name . '" '.$sel.' >' .$post_type->label . '</option>';
		 }

		 $html .= '</select>';		
				
		return 	  $html;	

			
	}

	public function import_from_url ($file){


		$upload_temp_folder = WPMPG_UPLOAD_FOLDER;
		$upload_dir = wp_upload_dir(); 
		$path_pics =   $upload_dir['basedir'];	
		$filePath = $path_pics.'/'.$upload_temp_folder."/".$file;


			// Parse the rows
		$rows = [];
		$handle = fopen($filePath, "r");
		while (($row = fgetcsv($handle)) !== false) {
			$rows[] = $row;
		}
		fclose($handle);
		// Remove the first one that contains headers
		$headers = array_shift($rows);

		$this->tblHeaders = $headers;
		$this->tblRows = $rows;

		//echo '<pre>';

		//print_r(array_slice($headers, 3, count($headers), false)); 

		//echo '</pre>';
	

		//echo '<pre>';

		//print_r($headers);

		//print_r($rows);

		//echo '</pre>';
		// Combine the headers with each following row
		$array = [];
		foreach ($rows as $row) {
			$array[] = array_combine($headers, $row);
		}

		$this->tblCombinedRows = $array;

		//echo '<pre>';

		//print_r($array);

		//echo '</pre>';
	//	var_dump($array);

	}

	function readCSV($url){
		$newfilename = "newfile";

			$file = fopen($url,"r");

			$output = fopen($newfilename.'.csv', 'wb');

			while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {
				fputcsv($output, $data);

				echo "data: ". print_r($data).'<br>';
			}

			fclose($file);
			fclose($output);


	}

	function get_uploader_section() {


		$html = '';


		$html .= '<div id="imagenotes-fileupload">';

		
		$html .= '</div>';
		$html .= '<div id="imagenotes-fileupload" class="uploader-button imnot-uploaderclass">';	

		$html .= '<h1 class=" mb-2 ">'.__('Upload a File','image-notes-plus-WP').'</h1>	';

		$html .= '<div class="bup-staff-right-avatar " >
					<div class="bup-avatar-drag-drop-sector"  id="bup-drag-avatar-section">														
						<div class="uu-upload-avatar-sect">

				<div id="filelist">Your browser does not have Flash, Silverlight or HTML5 support.</div>
				<br />

				<div id="container">
					<div class="rownoflexcenter">
					<button name="plupload-browse-button-avatar" id="pickfiles" class="msrm-btnupload-options mb-1 mt-1 mr-1 btn btn-lg btn-primary btn-custom-primary" type="link"><span><i class="fa fa-upload"></i></span> '.__('UPLOAD A FILE','image-notes-plus-wp').' 	</button>
					</div>
				</div>


					<br />
				

						</div>
					</div>
			</div>   

		</div>';

		$html .= '<div class="wpms-downn-url-cont" id="wpms-downn-url-cont">';
		$html .= '<h1 class=" mb-2 ">'.__('Import from URL','image-notes-plus-WP').'</h1>	';
		$html .= '<p><input name="file_url" class="file_url" id="file_url" value="" type="text"></p>';
		$html .= '<p><button name="wpmsaic-download-from-url-btn" id="wpmsaic-download-from-url-btn" class="btn mpg-download-link-btn btn-lg btn-primary btn-custom-primary" type="button"><span><i class="fa fa-download"></i></span> '.__('DOWNLOAD FILE FROM URL','image-notes-plus-wp').' 	</button>	</p>';
		$html .= '</div>';
		return $html;


	}

	function get_uploader_from_url() {


		$html = '';


		$html .= '<div id="wpmp-upload-from-url">';
		$html .= '<h1 class=" mb-2 ">'.__('Download a File','image-notes-plus-WP').'</h1>	';
		
		$html .= '</div>';
		

		return $html;


	}

	

	public function ini_group_modules_pro()	{
		//----------------------------------------------
		//----------register post type
		//----------------------------------------------		

		$cpt_registered = $this->get_all_post_types();		

		if (!empty($cpt_registered)){

			$supports = array(
				'title', // post title
				'editor', // post content
				'author', // post author
				'thumbnail', // featured images
				'excerpt', // post excerpt
				//'custom-fields', // custom fields
				'comments', // post comments
				'revisions', // post revisions
				'post-formats', // post formats
				);


			foreach($cpt_registered as $cpt) { 			
				$labels = json_decode($cpt->cpt_properties);	
				$projects_labels = array(
					'name' => $cpt->cpt_name ,
					'singular_name' => $labels->singular_name,
					'add_new' =>  $labels->add_new,
					'add_new_item' =>  $labels->add_new_item,
					'edit_item' => $labels->edit_item,
					'new_item' =>  $labels->new_item,
					'view_item' =>  $labels->view_item,
					'search_items' =>  $labels->search_items,
					'not_found' =>   $labels->not_found,
					'not_found_in_trash' =>  $labels->not_found_in_trash,
					'parent_item_colon' => ''
						
				);
				$project_args = array(
					'labels' => $projects_labels,
					'public' => true,
					'publicly_queryable' => true,
					'show_ui' => true,
					'query_var' => true,
					'rewrite' => true,
					'hierarchical' => false,
					'menu_position' => 5,
					'capability_type' => 'post',
					'supports' => $supports
					
				);
				
				register_post_type($cpt->cpt_unique_key, $project_args);	

			}
		}
					
	
	}
		// Add the data to the custom columns for the book post type:
	function custom_project_column( $column, $post_id ) {
		$date_format = get_option( 'date_format' ); 	
		//get post author
		$author_id = get_post_field( 'post_author', $post_id );
		switch ( $column ) {						
	
			case 'client_name' :			
				$first_name = get_the_author_meta( 'first_name', $author_id );
				echo $first_name;
				break;	
			case 'client_lname' :
				$client_lname = get_the_author_meta( 'last_name', $author_id );
				echo $client_lname;
				break;	
			case 'client_phone' :
				$client_phone = get_the_author_meta( 'client_phone', $author_id );
				echo $client_phone;
				break;					
			case 'client_email' :
				$author_obj = get_user_by('id', $author_id);
				$client_email = get_the_author_meta( 'client_email', $author_id );
				echo $author_obj->user_email;
				break;	
		}
	}

	function set_custom_edit_project_columns($columns) {
		unset( $columns['author'] );
		
		$columns['client_name'] = __( 'First Name', 'wp-quote-sp-calculator' );
		$columns['client_lname'] = __( 'Last Name', 'wp-quote-sp-calculator' );
		$columns['client_phone'] = __( 'Phone', 'wp-quote-sp-calculator' );
		$columns['client_email'] = __( 'E-mail', 'wp-quote-sp-calculator' );		
		$custom_col_order = array(
			'title' => $columns['title'],		
			'client_name' => $columns['client_name'],
			'client_lname' => $columns['client_lname'],
			'client_phone' => $columns['client_phone'],
			'client_email' => $columns['client_email']
			
		);
		return $custom_col_order;
	}
			
	

	function check_if_service_selected($item_selected){
		$options = $this->get_option('services');
		$services_array = $this->one_line_checkbox_on_window_fix($options);
		$i = 0;
		foreach($services_array as $item ){							
			$opt_services =  explode("|", $item);
		    if($i==$item_selected){
				return $opt_services[0].' '.$opt_services[1];
			}
			$i++;
		}
	}		

	/**
	 * This has been added to avoid the window server issues
	 */
	public function one_line_checkbox_on_window_fix($choices){		
		if($this->if_windows_server()) //is window
		{
			$loop = array();		
			$loop = explode(",", $choices);
		}else{ //not window
		
			$loop = array();		
			$loop = explode(PHP_EOL, $choices);	
		}			
		return $loop;
	}
	
	public function if_windows_server(){
		$os = PHP_OS;
		$os = strtolower($os);			
		$pos = strpos($os, "win");	
		
		if ($pos === false) {
			return false;
		} else {
			return true;
		}			
	}

	public function set_allowed_html(){
		global $allowedposttags;

		$allowed_html = wp_kses_allowed_html( 'post' );

		$allowed_html['select'] = array(
			'name' => array(),
			'id' => array(),
			'class' => array(),
			'style' => array()
		);

		$allowed_html['option'] = array(
			'name' => array(),
			'id' => array(),
			'class' => array(),
			'value' => array(),
			'selected' => array(),
			'style' => array()
		);

		$allowed_html['input'] = array(
			'name' => true,
			'id' => true,
			'class' => true,
			'value' => true,
			'selected' => true,
			'style' =>true
		);

		$allowed_html['table'] = array(
			'name' => true,
			'id' => true,
			'class' => true,			
			'style' => true
		);

		$allowed_html['td'] = array(
			'name' =>true,
			'id' => true,
			'class' => true,
			'style' => true
		);

		$allowed_html['tr'] = array(
			'name' => array(),
			'id' => array(),
			'class' => array(),
			
		);

		$allowed_atts = array(
			'align'      => array(),
			'span'      => array(),
			'checked'      => array(),
			'class'      => array(),
			'selected'      => array(),
			'type'       => array(),
			'id'         => array(),
			'dir'        => array(),
			'lang'       => array(),
			'style'      => array(),
			'display'      => array(),
			'xml:lang'   => array(),
			'src'        => array(),
			'alt'        => array(),
			'href'       => array(),
			'hidden'       => array(),
			'rel'        => array(),
			'rev'        => array(),
			'target'     => array(),
			'novalidate' => array(),
			'type'       => array(),
			'value'      => array(),
			'name'       => array(),
			'tabindex'   => array(),
			'action'     => array(),
			'method'     => array(),
			'for'        => array(),
			'width'      => array(),
			'height'     => array(),
			'data'       => array(),
			'title'      => array(),
			'userscontrol-data-date'      => array(),
			'userscontrol-data-timeslot'      => array(),
			'userscontrol-data-service-staff'      => array(),
			'userscontrol-max-capacity'      => array(),
			'userscontrol-max-available'      => array(),
			'data-nuve-rand-id'      => array(),
			'data-nuve-rand-key'      => array(),
			'data-location'      => array(),
			'data-cate-id'      => array(),
			'data-category-id'      => array(),
			'data-staff-id'      => array(),
			'data-staff_id'      => array(),
			'data-id'      => array(),
			'appointment-id'      => array(),
			'message-id'      => array(),			
			'>'      => array(),
			'userscontrol-staff-id'      => array(),				
			'service-id'      => array(),			
			'staff-id'      => array(),	
			'user-id'      => array(),	
			'staff_id'      => array(),		
			'widget-id'      => array(),
			'day-id'      => array(),
			'break-id'      => array(),	
			'category-id'      => array(),			
			'/option'      => array(),
			'label'      => array(),


			
		);



		$allowedposttags['button']     = $allowed_atts;
		$allowedposttags['form']     = $allowed_atts;
		$allowedposttags['label']    = $allowed_atts;
		$allowedposttags['input']    = $allowed_atts;
		$allowedposttags['hidden']    = $allowed_atts;
		$allowedposttags['textarea'] = $allowed_atts;
		$allowedposttags['iframe']   = $allowed_atts;
		$allowedposttags['script']   = $allowed_atts;
		$allowedposttags['style']    = $allowed_atts;
		$allowedposttags['display']    = $allowed_atts;	
		$allowedposttags['select']    = $allowed_atts;
		$allowedposttags['option']    = $allowed_atts;
		$allowedposttags['optgroup']    = $allowed_atts;
		$allowedposttags['strong']   = $allowed_atts;
		$allowedposttags['small']    = $allowed_atts;
		$allowedposttags['table']    = $allowed_atts;
		$allowedposttags['span']     = $allowed_atts;
		$allowedposttags['abbr']     = $allowed_atts;
		$allowedposttags['code']     = $allowed_atts;
		$allowedposttags['pre']      = $allowed_atts;
		$allowedposttags['div']      = $allowed_atts;
		$allowedposttags['img']      = $allowed_atts;
		$allowedposttags['h1']       = $allowed_atts;
		$allowedposttags['h2']       = $allowed_atts;
		$allowedposttags['h3']       = $allowed_atts;
		$allowedposttags['h4']       = $allowed_atts;
		$allowedposttags['h5']       = $allowed_atts;
		$allowedposttags['h6']       = $allowed_atts;
		$allowedposttags['ol']       = $allowed_atts;
		$allowedposttags['ul']       = $allowed_atts;
		$allowedposttags['li']       = $allowed_atts;
		$allowedposttags['em']       = $allowed_atts;
		$allowedposttags['hr']       = $allowed_atts;
		$allowedposttags['br']       = $allowed_atts;
		$allowedposttags['tr']       = $allowed_atts;
		$allowedposttags['td']       = $allowed_atts;
		$allowedposttags['p']        = $allowed_atts;
		$allowedposttags['a']        = $allowed_atts;
		$allowedposttags['b']        = $allowed_atts;
		$allowedposttags['i']        = $allowed_atts;
		$allowedposttags['>']        = $allowed_atts;

		$this->allowed_html = $allowedposttags;

	}
	

	function add_styles(){
	   global $wp_locale, $wpmpg , $pagenow; 		
	   wp_enqueue_script( 'jquery-ui-core' ); 
	   wp_register_style('wpmpg_fontawesome', wpmpg_url.'admin/css/font-awesome/css/font-awesome.min.css');
	   wp_enqueue_style('wpmpg_fontawesome');
	   wp_register_style('wpmpg_admin', wpmpg_url.'admin/css/admin.css' , array(), '1.0.6', false);
	   wp_enqueue_style('wpmpg_admin');

	   wp_register_style('wpmpg_jqueryui', wpmpg_url.'admin/css/jquery-ui.css');
	   wp_enqueue_style('wpmpg_jqueryui');

	   wp_register_script( 'wpmpg_front_uploader', wpmpg_url.'vendors/plupload/js/plupload.full.min.js',array('jquery'),  null);
	   wp_enqueue_script('wpmpg_front_uploader');

	   wp_register_script( 'wpmpg_admin', wpmpg_url.'admin/scripts/admin.js', array( 
		'jquery','jquery-ui-core', 'jquery-ui-progressbar'), '1.0.7' );
	   wp_enqueue_script( 'wpmpg_admin' );		  
   }      


	function admin_head(){
		$screen = get_current_screen();
		$slug = $this->slug;		
	}

	/*Post value*/
	function get_post_value($meta) {			
		if (isset($_POST[$meta]) ) {
			return sanitize_text_field($_POST[$meta]);
		}
	}
	
	function ini_plugin(){
		
		$is_admin = is_admin() && ! defined( 'DOING_AJAX' );
		
		/* Add hooks */
		if ( ! $is_admin  ) {			
			$this->create_actions();
		}
		
	}	
	
	public function update_default_option_ini () {
		$this->options = get_option('wpmpg_options');
		if (!get_option('wpmpg_options')) {
			update_option('wpmpg_options', $this->wpmpg_default_options );
		}
	}		
	
	function admin_init() {
		
		$this->tabs = array(
		    'main' => __('New Import','wp-mosaic-page-generator'),	
			'cpt' => __('Manage Custom Post Types','wp-mosaic-page-generator')	,
			'help' => __('Help','wp-mosaic-page-generator')		
		);		
		
		$this->default_tab = 'main';		
	}
	
	function add_menu() {
		global $wpmpg;			
		$menu_label = __('Mosaic Page Generator','wp-mosaic-page-generator');		
		add_menu_page( __('Mosaic Page Generator','wp-mosaic-page-generator'), $menu_label, 'manage_options', $this->slug, array(&$this, 'admin_page'), wpmpg_url .'admin/images/small_logo_16x16.png', '159.140');
		do_action('wpmpg_admin_menu_hook');
	}	

	function admin_tabs( $current = null ) {
		
		global $wpmpgcomplement, $wpmpg_custom_fields;

		$custom_badge = '';
		
			$tabs = $this->tabs;
			$links = array();
			if ( isset ( $_GET['tab'] ) ) {
				$current = $_GET['tab'];
			} else {
				$current = $this->default_tab;
			}
			foreach( $tabs as $tab => $name ) :			
				if ( $tab == $current ) :
					$links[] = "<a class='nav-tab nav-tab-active ".$custom_badge."' href='?page=".$this->slug."&tab=$tab'><span class='plaidplugin-adm-tab-legend'>".$name."</span></a>";
				else :
					$links[] = "<a class='nav-tab ".$custom_badge."' href='?page=".$this->slug."&tab=$tab'><span class='plaidplugin-adm-tab-legend'>".$name."</span></a>";
				endif;
			endforeach;
			foreach ( $links as $link )
				echo $link;
	}
	
	/* set a global option */
	function wpmpg_set_option($option, $newvalue){
		$settings = get_option('wpmpg_options');
		if($settings==''){
			$settings = array();
		}
		$settings[$option] = $newvalue;		
		update_option('wpmpg_options', $settings);
	}	
		
	function get_option($option) {
		$settings = get_option('wpmpg_options');
		if (isset($settings[$option])) {
			if(is_array($settings[$option])){
				return $settings[$option];			
			}else{				
				return stripslashes($settings[$option]);
			}
			
		}else{			
		    return '';
		}		    
	}	
		
	function initial_setup() {
		
		global $wpmpg, $wpdb;		
		$inisetup   = get_option('wpmpg_ini_setup');
		if (!$inisetup){						
			update_option('wpmpg_ini_setup', true);
		}		
	}
	
		
	function include_tab_content() {
		
		global $wpmpg, $wpdb ;		
		$screen = get_current_screen();		
		if( strstr($screen->id, $this->slug ) ) 
		{
			if ( isset ( $_GET['tab'] ) ) 
			{
				$tab = $_GET['tab'];
				
			} else {
				
				$tab = $this->default_tab;
			}

			require_once (wpmpg_path.'admin/tabs/'.$tab.'.php');
		}
	}
	
		// update settings
    function update_settings(){
		foreach($_POST as $key => $value){
            if ($key != 'submit'){
				$this->wpmpg_set_option($key, $value) ;  								
            }
        }         
        $this->options = get_option('wpmpg_options');
        echo '<div class="updated"><p><strong>'.__('Settings saved.','wp-mosaic-page-generator').'</strong></p></div>';
    }
	
	public function get_special_checks($tab) {
		$special_with_check = array();			
		return  $special_with_check ;
	}	
	
	
	function admin_page() 
	{
		

		
		
		if (isset($_POST['wpmpg_update_settings']) ) {
            $this->update_settings();
        }
		
				
		
		
			
	?>
	
		<div class="wrap <?php echo $this->slug; ?>-admin"> 
        
       
            
                <h2 class="nav-tab-wrapper"><?php $this->admin_tabs(); ?>               
                
                 
                
                </h2>  
  
            

			<div class="<?php echo $this->slug; ?>-admin-contain">    
            
               
			
				<?php 		
				
				
					$this->include_tab_content(); 
				
				
				?>
				
				<div class="clear"></div>
				
			</div>
			
		</div>

	<?php }
	
	
}
?>
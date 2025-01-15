<?php
use RankMath\Helper;
use RankMath\Helpers\Param;
use RankMath\Traits\Hooker;
use RankMath\Paper\Paper;
use RankMath\Admin\Metabox\Screen;
use RankMath\Schema\DB;
class WpMosaicPageGenerator extends wpmpgCommon {	
	var $wp_all_pages = false;
	public $classes_array = array();	
	var $notifications_email = array();
	var $wpmpg_default_options;	
	var $ajax_prefix = 'wpmpg';	
	var $allowed_inputs = array();	
	var $mCustomPostType;
	var $mVersion = '1.93';
	public $allowed_html;
	var $tblHeaders;	
	var $tblRows;			
	var $tblCombinedRows;
	var $slug;
	var $plugin_data;
	var $version;
	/**
	 * Screen object.
	 *
	 * @var object
	 */
	public $update_score;

	/**
	 *  Prefix for the enqueue handles.
	 */
	const PREFIX = 'rank-math-';

	public function __construct()	{		

		// Hook the filter during class initialization
		add_filter('rank_math/recalculate_scores_batch_size',array(&$this, 'set_batch_size'));
		
		// Ensure WordPress function for checking plugins is available
		if (!function_exists('is_plugin_active')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if (is_plugin_active('seo-by-rank-math/rank-math.php')) {
			$this->update_score = \RankMath\Tools\Update_Score::get();			
		}
		
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
		add_action('add_meta_boxes', array($this, 'post_add_meta_box' ));	
		add_action('init', array($this, 'ini_group_modules_pro'));	
		add_action( 'wp_ajax_'.$this->ajax_prefix.'_upload_file',  array( $this, 'upload_file_parse' ));
		add_action( 'wp_ajax_'.$this->ajax_prefix.'_download_url_file',  array( $this, 'download_file_from_url' ));
		add_action( 'wp_ajax_'.$this->ajax_prefix.'_start_process',  array( $this, 'start_import_ajax' ));	
		add_action( 'wp_ajax_'.$this->ajax_prefix.'_delete_cpf',  array( $this, 'delete_cpf' ));
		add_action( 'wp_ajax_'.$this->ajax_prefix.'_delete_cpf_value',  array( $this, 'delete_cpf_value' ));
		add_action( 'wp_ajax_'.$this->ajax_prefix.'_start_score_rebuild',  array( $this, 'get_post_data_for_analysis' ));
		add_action('save_post',  array( &$this, 'update_cpt_details' ), 94);

		if (is_plugin_active('seo-by-rank-math/rank-math.php')) {
			add_action('admin_enqueue_scripts', array(&$this, 'enqueue'), 12);

			
		}		

		add_action('admin_enqueue_scripts', array(&$this, 'add_styles'), 14);
    }

	/**
	 * Enqueue scripts & add JSON data needed to update the SEO score on existing posts.
	 */
	public function enqueue($hook) {		
		$scripts = [
			'lodash'             => '',
			'wp-data'            => '',
			'wp-core-data'       => '',
			'wp-compose'         => '',
			'wp-components'      => '',
			'wp-element'         => '',
			'wp-block-editor'    => '',
			'rank-math-analyzer' => rank_math()->plugin_url() . 'assets/admin/js/analyzer.js',
			'rank-math-status'    => rank_math()->plugin_url() . 'includes/modules/status/assets/js/status.js',
		];

		foreach ( $scripts as $handle => $src ) {
			wp_enqueue_script( $handle, $src, [], rank_math()->version, true );
		}
				
		// Load only on specific admin pages
		if ('toplevel_page_wpmpg' === $hook) {	

			//$this->update_score->batch_size= 50;

			$this->update_score->enqueue();
			$js     = rank_math()->plugin_url() . 'assets/admin/js/';
			$css    = rank_math()->plugin_url() . 'assets/admin/css/';

			wp_enqueue_script('wp-editor'); // Required for editor-related scripts
			wp_enqueue_script('wp-data');   // Used by Gutenberg and Rank Math

			// Scripts.
			wp_enqueue_script( self::PREFIX . 'dashboard', $js . 'dashboard.js', [ 'jquery', 'clipboard', 'lodash', 'wp-components', 'wp-element' ], rank_math()->version, true );
		    wp_enqueue_script( 'clipboard', rank_math()->plugin_url() . 'assets/vendor/clipboard.min.js', [], rank_math()->version, true );
			
			if ( ! wp_script_is( 'lodash', 'registered' ) ) {
				wp_register_script( 'lodash', rank_math()->plugin_url() . 'assets/vendor/lodash.js', [], rank_math()->version );
				wp_add_inline_script( 'lodash', 'window.lodash = _.noConflict();' );
			}
			
			wp_enqueue_script('rank-math-common-js', plugins_url('seo-by-rank-math/assets/admin/js/common.js'), ['jquery'], null, true);
			wp_enqueue_script('rank-math-app-js', plugins_url('seo-by-rank-math/assets/admin/js/rank-math-app.js'), ['jquery'], null, true);
            wp_enqueue_script('rank-math-schema-js', plugins_url('seo-by-rank-math/includes/modules/schema/assets/js/schema-gutenberg.js'), ['jquery'], null, true);
		}	
	}

	function getAllPostCount(){
		$args = array(
			'post_type'      => 'any',   // Include all public post types
			'posts_per_page' => -1,      // Fetch all posts
			'fields'         => 'ids',  // Fetch only IDs for efficiency
		);		
		$query = new WP_Query($args);
		$total_posts = $query->found_posts; // Total posts across all post types
		wp_reset_postdata();
		return $total_posts;	
	}

	/**
	 * Enqueque scripts common for all builders.
	 */
	private function enqueue_commons() {
		wp_register_style( 'rank-math-editor', rank_math()->plugin_url() . 'assets/admin/css/gutenberg.css', [ 'rank-math-common' ], rank_math()->version );
		wp_register_script( 'rank-math-analyzer', rank_math()->plugin_url() . 'assets/admin/js/analyzer.js', [ 'lodash', 'wp-autop', 'wp-wordcount' ], rank_math()->version, true );
	}

	/**
     * Set custom batch size for recalculating scores.
     *
     * @param int $batch_size The default batch size.
     * @return int The modified batch size.
     */
    public function set_batch_size($batch_size) {
        return 50; // Set your desired batch size, e.g., 50
    }

	/**
	 * Get post types.
	 *
	 * @return array
	 */
	private function get_post_types() {
		$post_types = get_post_types( [ 'public' => true ] );
		if ( isset( $post_types['attachment'] ) ) {
			unset( $post_types['attachment'] );
		}
		return array_keys( $post_types );
	}

	/**
	 * Modal to show the Update SEO Score progress.
	 *
	 * @return array
	 */
	public function footer_modal() {
		
		?>
		<div class="rank-math-modal rank-math-modal-update-score">
			<div class="rank-math-modal-content">
				<div class="rank-math-modal-header">
					<h3><?php esc_html_e( 'Recalculating SEO Scores', 'wp-mosaic-page-generato' ); ?></h3>
					<p><?php esc_html_e( 'This process may take a while. Please keep this window open until the process is complete.', 'wp-mosaic-page-generato' ); ?></p>
				</div>
				<div class="rank-math-modal-body">
					<div class="count">
						<?php esc_html_e( 'Calculated:', 'wp-mosaic-page-generato' ); ?> <span class="update-posts-done">0</span> / <span class="update-posts-total"><?php echo esc_html( $this->find() ); ?></span>
					</div>
					<div class="progress-bar">
						<span></span>
					</div>

					<div class="rank-math-modal-footer hidden">
						<p>
							<?php esc_html_e( 'The SEO Scores have been recalculated successfully!', 'wp-mosaic-page-generato' ); ?>
						</p>
						<button class="button button-large rank-math-modal-close"><?php esc_html_e( 'Close', 'wp-mosaic-page-generato' ); ?></button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	
	function add_styles(){
		global $wp_locale, $wpmpg , $pagenow; 		
		wp_enqueue_script( 'jquery-ui-core' ); 
		wp_register_style('wpmpg_fontawesome', wpmpg_url.'admin/css/font-awesome/css/font-awesome.min.css');
		wp_enqueue_style('wpmpg_fontawesome');
		wp_register_style('wpmpg_admin', wpmpg_url.'admin/css/admin.css' , array(), '1.0.6', false);		wp_enqueue_style('wpmpg_admin');
 
		wp_register_style('wpmpg_jqueryui', wpmpg_url.'admin/css/jquery-ui.css');
		wp_enqueue_style('wpmpg_jqueryui'); 
		wp_register_script( 'wpmpg_front_uploader', wpmpg_url.'vendors/plupload/js/plupload.full.min.js',array('jquery'),  null);
		wp_enqueue_script('wpmpg_front_uploader'); 
		wp_register_script( 'wpmpg_admin', wpmpg_url.'admin/scripts/admin.js', array( 
		 'jquery','jquery-ui-core', 'jquery-ui-progressbar'), $this->mVersion, true );
		wp_enqueue_script( 'wpmpg_admin' );		  
	}      
 
	
	function update_cpt_details( $post_id ){
			
		// If this is just a revision, don't send the email.
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave($post_id) )
			return;
				 
		 $post = get_post($post_id);
        if($post->post_status == 'trash' ){
                return $post_id;
        }

		$post_type = $post->post_type;

		$cptRows = $this->getAllCustomFieldsByType($post_type);
		foreach($cptRows as $cpt) {
			
			$meta = $cpt->cpf_field_name ;
			$dd_t_value = $_POST[$meta];
		    update_post_meta( $post_id, $meta,$dd_t_value);
		}			
			
	}	

	function getAllCustomFieldsByType($cp_type)   {
		global $wpdb;			
		$html = '';
		$sql =  'SELECT cpf.*, cpt.*  FROM ' . $wpdb->prefix . 'cpt_fields cpf  ' ;				
		$sql .= " RIGHT JOIN ". $wpdb->prefix."cpt  cpt  ON (cpt.cpt_id = cpf.cpf_cpt_id )";		
		$sql .= " WHERE cpt.cpt_id = cpf.cpf_cpt_id    ";	
		$sql .= " ORDER BY  cpf.cpf_field_sorting ";	
		$sql = $wpdb->prepare($sql);
		//echo $sql;
		$cptRows = $wpdb->get_results($sql );
		return $cptRows ;
	
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

		$query = '
			CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'cpt_credits(
			  `credit_id` int(11) NOT NULL AUTO_INCREMENT,	
			  `credit_page_id` int(11) NOT NULL ,				 
					  			 	  
			  PRIMARY KEY (`credit_id`)
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
					
		if($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'webp' || $ext == 'gif' || $ext == 'csv'){
				
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
		$wpdb->delete( $wpdb->prefix . 'cpt_fields', [ 'cpf_cpt_id'=>$cpf ], [ '%d' ] );
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

		$postIDArray = array();
		$headers = $this->tblHeaders;
		$rowsDATA = $this->tblRows;
		$rowsDOrigin = $this->tblRows;	
		$rowsCombined = $this->tblCombinedRows;		

		$only_metas = array_slice($headers, 6, count($headers), false); 

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
		
		$fields_type_col = $this->getPostMetaFieldsColNew($only_metas);	
		$this->setCPT($rowsDATASliced, $fields_type_col);	

		$count = 0;
		$rowCount =1;

		$rowsDATA = $rowsDATASliced;			
		foreach ( $rowsDATA  as $data ) {					
			$post_type = $rowsDATA[$count][0];
			$post_title = $rowsDATA[$count][1];
			$post_slug = $rowsDATA[$count][2];
 			$rank_math_title = $rowsDATA[$count][3];            
			$meta_desc = $rowsDATA[$count][4];   
			$keyword = $rowsDATA[$count][5];   
			$post_desc ='';		
			$post_excerpt = '';		
			
			$rowCount++;			
			$meta_input = array();		

			//get user id					
			$user_id = get_current_user_id();
				
			// Create post object
			$my_post = array(
				'post_title'    => $post_title,
				'post_content'  => $post_desc,
				'post_excerpt'  => $post_excerpt,			
				'post_name'  => $post_slug,			
				'post_status'   => 'publish',
				'post_author'   => $user_id,
				'post_type'   => $post_type	,			
			
			);

			$post_exists = $this->the_slug_exists($post_slug, $post_type);	

			if(!$post_exists){
				// Insert the post into the database
				$update_post = false;
				$post_id = wp_insert_post( $my_post );				

			}else{
				$post = $this->get_post_id_by_slug($post_slug, $post_type);
				$post_id = $post[0]->ID;
				$update_post = true;
				$my_post = array(
					'ID'		=> $post_id,	
					'post_title'    => $post_title,
					'post_content'  => $post_desc,
					'post_excerpt'  => $post_excerpt					
				
				);
				wp_update_post( $my_post ); 
			}					
						
			update_post_meta( $post_id, '_yoast_wpseo_title',$post_title );
			update_post_meta( $post_id, '_yoast_wpseo_metadesc',$meta_desc );
			update_post_meta( $post_id, '_yoast_wpseo_focuskw',$post_title );
			update_post_meta( $post_id, 'description_wpmosaic',$meta_desc );  
			
			//rank math data
			update_post_meta( $post_id, 'rank_math_title',$rank_math_title );            
			update_post_meta( $post_id, 'rank_math_description',$meta_desc );	
			update_post_meta( $post_id, 'rank_math_focus_keyword',$keyword );	

			$postIDArray[] = $post_id;

			//$this->get_or_save_rank_math_seo_score($post_id);
		
			$i = 6;	// custom meta fields starts
			foreach ( $only_metas  as $meta_key ) {

				$val_import = $rowsDATA[$count][$i] ?? '';
				
				if(isset( $fields_type_col[$meta_key])) { //if there is a custom field for this post type				

					$custom_field = $fields_type_col[$meta_key];
					if($custom_field['cpf_field_type']==1){ //text					

						update_post_meta( $post_id, $meta_key,$val_import );

					}else{ //image	
							
						$meta_alternative_text = $rowsCombined[$count][$meta_alternative_text] ?? ''; // Already defined above
						
						$meta_alternative_text= $meta_key.'_alternative_text';
						$meta_title= $meta_key.'_title';
						$meta_slug= $meta_key.'_slug';
						$meta_description= $meta_key.'_description';

						$meta_text = $rowsCombined[$count][$meta_alternative_text] ?? '';
						$meta_slug = $rowsCombined[$count][$meta_slug] ?? '';
						$meta_title = $rowsCombined[$count][$meta_title] ?? '';
						$meta_description = $rowsCombined[$count][$meta_description] ?? '';									
						
						//check if image already exists

						//echo "<BR>IMAGE TO DOWNLOAD: " . $val_import ."<BR>";
						$img_path = $this->download_from_url($val_import, $meta_slug, $post_id, $meta_key);
						//echo "Flag 56 ";
						$attach_id = $this->attach_image_to_project($img_path, $meta_title, $post_id, $user_id);

						if($attach_id!=''){

							$thumb_url = wp_get_attachment_url($attach_id);
							// Update this line to include the alt attribute
						    $img_val = '<img class="test" alt="'.esc_attr($meta_text).'" src="'.$thumb_url.'">';                       
						    update_post_meta( $post_id, $meta_key,$img_val );

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
				}				
					
				$i++;

		    }	//end foreach
			$count++;

	    }	// end for each data

		$response = array('status' => $status_process, 
						  'last' => $last_row, 
						  'percent_a' => $percent_a , 
						  'cut_from' => $cut_from  , 
						  'cut_to' => $cut_to , 
						  'total_rows' => count($rowsDOrigin),
						  'total_left' => count($rowsDATASliced),
						  'sliced_rows' => $rowsDATASliced,
						  'postIDS' => $postIDArray);

		echo json_encode($response);
		die();				
	
	}

	function get_post_data_for_analysis() {
		if (!isset($_POST['post_id']) || !is_numeric($_POST['post_id'])) {
			wp_send_json_error(['message' => 'Invalid post ID']);
		}
	
		$post_id = intval($_POST['post_id']);
		$post = get_post($post_id);
	
		if (!$post) {
			wp_send_json_error(['message' => 'Post not found']);
		}
	
		// Prepare post data
		$post_data = [
			'title'   => $post->post_title,
			'content' => $post->post_content,
			'excerpt' => $post->post_excerpt,
			'meta'    => get_post_meta($post_id),
		];
	
		wp_send_json_success($post_data);
	}

	function generate_post_score(){

		//step 1 get new post IDS
		$url = wpmpg_url.'wp-json/rankmath/v1/toolsAction';

		//make the call
		$data = json_encode([
			'action' => 'update_seo_score',
			'args[update_all_scores]' => '1',
		]);

		$res = $this->make_post_request($url, $data);		

		//update scores by using the RankMath API
	}

	function get_or_save_rank_math_seo_score($post_id) {

		// Ensure WordPress function for checking plugins is available
		if (!function_exists('is_plugin_active')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Check if the score already exists
		$seo_score = get_post_meta($post_id, 'rank_math_seo_score', true);

		// Check if the Rank Math plugin is active
		if (is_plugin_active('seo-by-rank-math/rank-math.php')) {	
			
			// If not, calculate and save it
			if (empty($seo_score) && function_exists('rank_math')) {

				$body = json_encode([
					'key1' => 'value1',
					'key2' => 'value2',
				]);

				//$this->make_post_request($url, $data)

				//$this->generate_post_score();

				   // Trigger the SEO Analyzer for the given post ID
				 //  $analyzer = rank_math()->modules->analysis->analyze_object($post_id);

				   //print_r( $analyzer);

			

				// Trigger the Rank Math analysis for the post

				//rank_math()->update_post_score($post_id);
				//rank_math()->meta->analyze_post($post_id);

				

				 // Retrieve the calculated score from post meta
				$seo_score = get_post_meta($post_id, 'rank_math_seo_score', true);

				
				//$seo_score = rank_math()->modules->analysis->get_seo_score($post_id);
				//update_post_meta($post_id, 'rank_math_seo_score', $seo_score);

				echo "SEO score Calculated: ". $seo_score;
			}
		}
	
		return $seo_score;
	}

	/**
	 * Function to make a POST request.
	 *
	 * @param string $url The endpoint URL.
	 * @param array $data The data to send in the POST body.
	 * @param array $headers Optional. Headers for the request.
	 * @return mixed Response body on success, or WP_Error on failure.
	 */
	function make_post_request($url, $data, $headers = []) {
		// Set default headers if none are provided
		$default_headers = [
			'Content-Type' => 'application/json',
		];

		// Merge default and custom headers
		$headers = wp_parse_args($headers, $default_headers);

		// Prepare the request arguments
		$args = [
			'method'  => 'POST',
			'body'    => json_encode($data),
			'headers' => $headers,
			'timeout' => 45, // Optional timeout
		];

		// Send the POST request
		$response = wp_remote_post($url, $args);

		// Handle errors
		if (is_wp_error($response)) {
			return $response; // Return error object
		}

		// Retrieve and return the response body
		return wp_remote_retrieve_body($response);
	}

	// File upload handler:
	function download_from_url($url, $meta_slug,  $post_id, $meta_key){
						
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
					
		if($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'webp' || $ext == 'gif' ){
				
			if(!is_dir($path_pics.'/'.$upload_temp_folder)) {
				wp_mkdir_p( $path_pics.'/'.$upload_temp_folder );							   
			}				

			$pathBig = $path_pics.'/'.$upload_temp_folder."/".$meta_slug.'.'.$ext;		
			if(file_exists($pathBig)){ //we need to rename it with image slug		
				
				wp_delete_file($pathBig);

				//find in post_meta by metavalue
				$file_name_meta = $upload_temp_folder."/".$meta_slug.'.'.$ext;

				global $wpdb;
				$sql = "Select post_id, meta_key,  meta_value from ". $wpdb->postmeta  ." where meta_value = '".$file_name_meta."' ";
                $results = $wpdb->get_results($sql);
				if ( !empty( $results ) ){					
					foreach ( $results as $item ){
						$attacment_id = $item->post_id;						
						wp_delete_post($attacment_id,true) ;
						delete_post_meta( $attacment_id, $meta_key );									
					
					}	
				}
				
				
			}			

			file_put_contents($pathBig, $image);
					
		} // image type
		return $pathBig;
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

	function the_slug_exists($post_name, $post_type) {
		global $wpdb;
		if($wpdb->get_row("SELECT post_name FROM ". $wpdb->prefix. "posts WHERE post_name = '" . $post_name . "' AND post_type = '" . $post_type . "'", 'ARRAY_A')) {
			return true;
		} else {
			return false;
		}
	}

	function the_field_exists($post_name, $post_id) {
		global $wpdb;
		if($wpdb->get_row("SELECT cpf_cpt_id, cpf_field_name FROM ". $wpdb->prefix. "cpt_fields 
		                   WHERE cpf_cpt_id = '" . $post_id . "' AND cpf_field_name = '" . $post_name . "'", 'ARRAY_A')) {
			return true;
		} else {
			return false;
		}
	}


	function get_post_id_by_slug( $slug, $post_type ) {
		global $wpdb;
		$sql = "SELECT ID FROM ". $wpdb->prefix. "posts where post_type='".$post_type."' AND post_name='".$slug."'";
		return $wpdb->get_results($sql);
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

		$html = ' 
        
        <div class="row item first uploader">
        <div class="col col-9 info mid">
        <div class="row label">'.count($rowsDATA).' posts will be imported</div>
        <div class="row description">Press start to begin the import process</div>
        </div>
        <div class="col col-3 click">
            <div class=" msm-btn-steps-bar-steps" id="msm-btn-steps-bar-steps"  >
                <button class="msrm-btnupload-options mb-1 mt-1 mr-1 btn btn-lg btn-primary btn-custom-primary" type="button" id="wmp-btn-start-import-submit">Start Import</button>
            </div>   
        </div>
        </div>
        
       ';   

		$html .= ' <div class="row items mspg-tbl-cont msrm-panel" id="mspg-tbl-cont"  >';

		$count = 0;
		$rowCount =1;

		foreach ( $rowsDATA  as $data ) {		
			$html .='<table class="wp-list-table widefat  posts table-generic mspg-tbl-table">';
			$html .='<thead>';
			$html .='<tr id="wms-tbl-val-'.$count.'">';
				//build header
				$html .='<th class="wpmpg-field-tbl">'.__('Row','image-notes-plus-WP'). ' '.$rowCount.'</th>';
				$html .='<th id="wms-tbl-val-col-'.$count.'" class="wms-val-col">'.__('Ready','image-notes-plus-WP'). '</th>';
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
		
		$html .='<div class="row progress wpm-progress-bar" id="wpm-progress-bar">';

		$html .='
        <div id="progressbar" class="row progress-contain">
        <div class="row labels">
        <div class="col col-6 ">
             <div class="msrm-stat-count">'. __('Imported') .': <span id="msrm-process-val">0</span> '.__('of') .' <span id="msrm-total-val">'.count($rowsDATA).'</span></div>
        </div>
        <div class="col col-6 finish">
           <div class="progress-label">'.__('Loading...').'</div>
        </div>
        </div>
		</div>';
	
		$html .='
        </div>';

		$html .='<div class="row batch wpm-import-opt-bar" id="wpm-import-opt-bar">';
		   // $html .='<p>'.__('Batches').'</p>';
			$html .='<span>'.__('Batches').'</span>: <input type="number" id="batch" name="batch" value="3">';

		
		$html .='</div>';

		$html .='<div class="row complete wpm-import-results-block" id="wpm-import-results-block">';

			$html .='</div>';
		
		$html .='</div>';
		
		return $html;
	
	}

	public function getPostMetaFieldsColNew( $only_metas)   {
			
		$arrCol = array();		
		
		$img_metadata = array('slug');
		foreach ( $only_metas  as $meta_key ) {			

			if (strpos($meta_key, 'custom_field') !== false) {
				$cpf_field_type = 1;
			}else{ //image	

				if (strpos($meta_key, 'slug') !== false) { //is slug
					$cpf_field_type = 1;
				}elseif(strpos($meta_key, 'alternative_text') !== false){ 
					$cpf_field_type = 1;
				}elseif(strpos($meta_key, 'title') !== false){ 
					$cpf_field_type = 1;
				}elseif(strpos($meta_key, 'description') !== false){ 
					$cpf_field_type = 1;

				}else{

					$cpf_field_type = 0;	//it's the image itself
				}				
			}	

			if($this->is_custom_field($meta_key)){
				$arrCol[$meta_key] = array('cpf_field_type' =>$cpf_field_type);
			}
			
			
		}	//end foreach
				
		return $arrCol;
    }

	//check if custom field
	public function is_custom_field( $meta_key)   {	

		if (strpos($meta_key, 'custom_field') !== false) {			
			return true;

		}elseif(strpos($meta_key, 'custom_image') !== false){ //image	

			return true;
	
		}else{

			return false;

		}		
	
    }

	public function get_field_type( $meta_key)   {	

		if (strpos($meta_key, 'custom_field') !== false) {
			$cpf_field_type = 1;
		}else{ //image	

			if (strpos($meta_key, 'slug') !== false) { //is slug
				$cpf_field_type = 1;
			}elseif(strpos($meta_key, 'alternative_text') !== false){ 
				$cpf_field_type = 1;
			}elseif(strpos($meta_key, 'title') !== false){ 
				$cpf_field_type = 1;
			}elseif(strpos($meta_key, 'description') !== false){ 
				$cpf_field_type = 1;

			}else{

				$cpf_field_type = 0;	//it's the image itself
			}				
		}					
		return $cpf_field_type;
    }

	public function setCPT($rowsDATA, $fields)   {
		global $wpdb;		
		$arrCol = array();	

		$count = 0;
		foreach ( $rowsDATA  as $data ) {					
			$post_type = strtolower($rowsDATA[$count][0]);

			//check if CPT exists
			if (!$this->cpt_exists_in_db($post_type)){
				$projects_labels = array(
					'name' => $post_type,
					'singular_name' => ucfirst($post_type),
					'add_new' => __('Add new'),
					'add_new_item' =>__('Add new item'),
					'edit_item' => __('Edit item'),
					'new_item' => __('New item'),
					'view_item' => __('View item'),
					'search_items' => __('Search items'),
					'not_found' =>__('Not found'),
					'not_found_in_trash' => __('Not found in trash'),
					'parent_item_colon' => '');
							
				//we can create the membership				
				$new_record = array('cpt_id' => NULL,	
									'cpt_name' =>ucfirst($post_type),
									'cpt_unique_key' =>$post_type,								
									'cpt_properties' => json_encode( $projects_labels),								
										
									);	
													
																		
				$wpdb->insert( $wpdb->prefix .'cpt', $new_record, 
				array( '%d', '%s' , '%s' , '%s'));

				// Get the last inserted ID
				$post_type_id = $wpdb->insert_id;	
								
				
			}else{		
				//update custom fields
				$post_type_id = $this->mCustomPostType;
			}

			//lest create the custom fields for this CPT
			foreach ( $fields  as $meta_key => $value ) {
				$field_name = $meta_key;
				$field_type = $this->get_field_type($field_name);
	
				if(!$this->the_field_exists($field_name, $post_type_id)){		
					//let's create the field
					$new_record = array(
						'cpf_id' => NULL,
						'cpf_cpt_id' => $post_type_id,
						'cpf_field_type' => $field_type,
						'cpf_field_label' =>$field_name,
						'cpf_field_name' =>$field_name,
						'cpf_field_default_value' =>0);						
					
					$wpdb->insert( $wpdb->prefix .'cpt_fields', $new_record, 
					array( '%d', '%s' , '%s' , '%s' , '%s' , '%s'));
				}
			}		
			
			$count++;

	    }	// end for each data
		
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


	public function getAllCustomFields()   {
		global $wpdb;			
		$html = '';
		$sql =  'SELECT cpf.*, cpt.*  FROM ' . $wpdb->prefix . 'cpt_fields cpf  ' ;				
		$sql .= " RIGHT JOIN ". $wpdb->prefix."cpt  cpt  ON (cpt.cpt_id = cpf.cpf_cpt_id )";		
		$sql .= " WHERE cpt.cpt_id = cpf.cpf_cpt_id    ";	
		$sql .= " ORDER BY  cpf.cpf_field_sorting ";	
		$sql = $wpdb->prepare($sql);
		//echo $sql;
		$cptRows = $wpdb->get_results($sql );
		return $cptRows ;

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

	public function cpt_exists_in_db($cpt){
		global $wpdb;
		$res = false;
		$sql = ' SELECT * FROM ' . $wpdb->prefix . 'cpt ' ;			
		$sql .= ' WHERE cpt_unique_key = "'.$cpt.'"' ;	
				
		$res = $wpdb->get_results($sql);		
		if ( !empty( $res ) ){
			foreach ( $res as $row ){
				$this->mCustomPostType = $row->cpt_id;
				return true;			
			}
		}		
		
		return 	  $res;				
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


		// Combine the headers with each following row
		$array = [];
		foreach ($rows as $row) {
			$array[] = array_combine($headers, $row);
		}

		$this->tblCombinedRows = $array;

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
		$html .= '<div id="imagenotes-fileupload" class="item first uploader-button imnot-uploaderclass">';	

		$html .= '
                <div class="col col-6 info">
                    <div class="label">'.__('Import posts via file','image-notes-plus-WP').'</div>
                    <div class="description">Upload a CSV spreadsheet to start importing</div>
                </div>';

		$html .= '<div class="col col-6 click"><div class="bup-staff-right-avatar " >
					<div class="bup-avatar-drag-drop-sector"  id="bup-drag-avatar-section">														
						<div class="uu-upload-avatar-sect">

                            <div id="filelist"></div>


                            <div id="container">
                                <div class="rownoflexcenter">
                                <button name="plupload-browse-button-avatar" id="pickfiles" class="msrm-btnupload-options mb-1 mt-1 mr-1 btn btn-lg btn-primary btn-custom-primary" type="link"> '.__('Import via File','image-notes-plus-wp').' 	</button>
                                </div>
                            </div>
				

						</div>
					</div>
			</div>   

		</div></div>';

		$html .= '
        
        <div class="item wpms-downn-url-cont" id="wpms-downn-url-cont">';
        
		$html .= '
        
        <div class="col col-6 info">
            <div class="label">'.__('Import posts via URL','image-notes-plus-WP').'</div>
            <div class="description">Import directly from Google Sheets</div>
        </div>
        
        ';
		$html .= '
        <div class="col col-6 click">
        
        <div class="row in">
        <div class="col col-9">
        <input name="file_url" class="file_url" id="file_url" value="" type="text" placeholder="https://sheets.google.com/a290s...">
        </div>';
        
		$html .= '
        <div class="col col-3">
        <button name="wpmsaic-download-from-url-btn" id="wpmsaic-download-from-url-btn" class="btn mpg-download-link-btn btn-lg btn-primary btn-custom-primary" type="button"> '.__('Start','image-notes-plus-wp').'</button>
        </div>
        </div>
        ';
        
		$html .= '
      ';
        
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
		$menu_label = __('Mosaic Pages','wp-mosaic-page-generator');		
		add_menu_page( __('Mosaic Pages','wp-mosaic-page-generator'), $menu_label, 'manage_options', $this->slug, array(&$this, 'admin_page'), wpmpg_url .'admin/images/small_logo_16x16.png', '159.140');
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
	
	
	function admin_page() 	{	
		
		if (isset($_POST['wpmpg_update_settings']) ) {
            $this->update_settings();
        }
		
			
	?>	

		<div class="wrap <?php echo $this->slug; ?>-admin"> 

            
<div class="<?php echo $this->slug; ?>-header">
    
    <img src="<?php echo plugins_url( 'admin/images/direction-logo.png', dirname( __FILE__ ) ); ?>" alt="Logo">
    <span class="name">
        <span class="version">
        <?php
            if ( ! function_exists( 'get_plugin_data' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            }

            $plugin_file_path = plugin_dir_path( __DIR__ ) . 'index.php'; 
            
            $plugin_data = get_plugin_data( $plugin_file_path );
            echo 'Plugin Version: ' . $plugin_data['Version'];
            ?>
        </span>
        Page Generator</span>
</div>      
            
                <h2 class="nav-tab-wrapper"><?php $this->admin_tabs(); ?>  </h2>  
  
            

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
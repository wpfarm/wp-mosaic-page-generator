<?php
global $wpmpg;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$step = $_POST['step'] ?? 1;
$is_submited = $_POST['is_submited'] ?? 0;
$file_uploaded = $_POST['file_uploaded'] ?? '';
?>

<div class="wpmpg-welcome-panel">              
		<form method="post" id="wmp-form">		

		<input type="hidden" id="file_uploaded" name="file_uploaded" value="<?php echo $file_uploaded ;?>">
		<input type="hidden" id="is_submited" name="is_submited" value="1">
		<input type="hidden" id="step" name="step" value="2">
		<input type="hidden" id="last_row" name="last_row" value="0">
		
        <div class=" wp-upload-cont-opt ">

		        <?php
				if($step==1){
				?>

                <div class="msrm-panel wp-upload-opt">

                        <?php echo $wpmpg->get_uploader_section();?>  
                    
                    <div class="row in">                  

                        <div class="col col-9 btm click">
                            <div class="msm-btn-steps-bar" id="msm-btn-steps-bar">
                                <button class="msrm-btnupload-options mb-1 mt-1 mr-1 btn btn-lg btn-primary btn-custom-primary" type="button" id="wmp-btn-submit"> <?php _e('Next', 'wp-mosaic-page-generator'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
                <div class="row item">
                    <div class="col col-9 info mid">
                        <div class="row label">Custom Post Types</div>
                        <div class="row description">Here you can manage your custom post types.</div>
                    </div>
                    <div class="col col-3 click">
                        <a class="outline" href="<?php echo site_url('/wp-admin/admin.php?page=wpmpg&tab=cpt');?>"><img src="<?php echo plugins_url( 'images/icon-settings.svg', dirname( __FILE__ ) ); ?>"></a>
                    </div>
                </div>

				<?php
				}elseif($step==2 && $is_submited==1){ //file submited 

					$total_posts = $wpmpg->getAllPostCount();
				?>

					<div class="msrm-panel wp-upload-opt">	
						<?php echo $wpmpg->build_data_table($_POST['file_uploaded']); ?>
						
						<div class="rmcalculator btn_update_score_ww">					

							<a href="#" class="button button-large button-link-delete tools-action " id="rmcalculator"  data-action="update_seo_score" data-confirm="false">Recalculate Scores</a>
						
							<div class="update_all_scores"><label><input type="checkbox" name="update_all_scores" id="update_all_scores" value="1" checked="checked"> Include posts/pages where the score is already set</label></div>
						</div>	

						<div class="rank-math-modal rank-math-modal-update-score wpmg-seoscore">
							<div class="rank-math-modal-content">
								<div class="rank-math-modal-header">
									<h3><?php esc_html_e( 'Recalculating SEO Scores', 'wp-mosaic-page-generator' ); ?></h3>
									<p><?php esc_html_e( 'This process may take a while. Please keep this window open until the process is complete.', 'wp-mosaic-page-generator' ); ?></p>
								</div>
								<div class="rank-math-modal-body">

								    <?php if (is_plugin_active('seo-by-rank-math/rank-math.php')) {?>
										<div class="count">
										</div>
									<?php }	?>
									<div class="progress-bar">
										<span></span>
									</div>
									<div class="rank-math-modal-footer hidden">
										<p>
											<?php esc_html_e('The SEO Scores have been recalculated successfully!', 'wp-mosaic-page-generator' ); ?>
										</p>
										
									</div>
								</div>
							</div>
						</div>
					</div>	
					
					
					<?php
				}elseif($step==3 && $is_submited==1){ //file submited 
				?>


					<div class="msrm-panel wp-upload-opt">				   

						<?php //echo $wpmpg->start_import(); ?> 	
						
						
					</div>	

				

				<?php
				}
				?>




        </div>

		</form>
        


   
</div>


<script type='text/javascript'>

var uploader = new plupload.Uploader({
	runtimes : 'html5,flash,silverlight,html4',
	browse_button : 'pickfiles', // you can pass an id...
	container: document.getElementById('container'), // ... or DOM Element itself
	url : ajaxurl  ,
	flash_swf_url : '../js/Moxie.swf',
	multi_selection:false,
	silverlight_xap_url : '../js/Moxie.xap',

	// Additional parameters:
	multipart_params   : {
			 _ajax_nonce : '<?php echo wp_create_nonce('photo-upload') ?>',
			 action     : 'wpmpg_upload_file' // The AJAX action name
					 
                    },

	filters : {
		max_file_size : '20mb',
		mime_types: [
			{title : "Image files", extensions : "png,webp,jpg,csv"}
		]
	},

	init: {

		PostInit: function() {
			document.getElementById('filelist').innerHTML = '';

			//document.getElementById('uploadfiles').onclick = function() {
			//	uploader.start();
			//	return false;
			//};
		},

		FilesAdded: function(up, files) {
			plupload.each(files, function(file) {
				document.getElementById('filelist').innerHTML += '<div id="' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b></div>';
			});		
      
      jQuery('#pickfiles').prop('disabled', true);
      jQuery('#pickfiles').html('<span><i class="fa fa-upload"></i></span> Please wait ...');
			uploader.start();
			return false;
		},

		FileUploaded: function(up, file, response) {
			var obj = jQuery.parseJSON(response.response);
			var img_name = obj.image;			
            
			$("#file_uploaded").val(img_name);
			$("#msrm-imp-options").slideDown(400);	
			$("#msm-btn-steps-bar").slideDown(400);			
			
			
			//$("#image_note_plus_form").submit();

            //upload completed
            // window.location.href = '?conf=ok&key=' + file_key;

		},

		UploadProgress: function(up, file) {
			document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = '<span>' + file.percent + "%</span>";
		},

		Error: function(up, err) {
			document.getElementById('console').appendChild(document.createTextNode("\nError #" + err.code + ": " + err.message));
		}
	}
});

uploader.init();



</script>



     

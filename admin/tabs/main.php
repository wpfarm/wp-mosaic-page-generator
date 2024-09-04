<?php
global $wpmpg;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$url = 'https://docs.google.com/spreadsheets/d/1tmZY-m4dHjxyNhCQ4ki5wiFr3bzkWrTQEz-KbNAZhNA/edit?usp=sharing';
$url = 'https://docs.google.com/spreadsheets/d/1tmZY-m4dHjxyNhCQ4ki5wiFr3bzkWrTQEz-KbNAZhNA/export?format=csv';

//$wpmpg->import_from_url($url);

$step = $_POST['step'] ?? 1;
$is_submited = $_POST['is_submited'] ?? 0;
$file_uploaded = $_POST['file_uploaded'] ?? '';

?>

<div class="wpmpg-welcome-panel">
        <h1 class="wpquotecalc-extended">WP Mosaic Page Generator</h1>
              
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
                        
                        <div class="msrm-imp-options" id="msrm-imp-options">

                            <div class="msrm-imp-options-sel wpm-upload-imp-op-bx" selected-opt="act_opt_create" id="act_opt_create_box">
							    <p>  <?php _e('SELECT POST TYPE', 'wp-mosaic-page-generator'); ?> </p>
								<p> <?php echo $wpmpg->get_all_c_postypes_list_box('wp-custom-post-type');?>  </p>

                            </div>

                                                  

                        </div>

						<div class=" msm-btn-steps-bar" id="msm-btn-steps-bar"  >

							<button class="msrm-btnupload-options mb-1 mt-1 mr-1 btn btn-lg btn-primary btn-custom-primary" type="button" id="wmp-btn-submit"><span><i class="fa fa-arrow-right"></i></span> <?php _e('NEXT STEP', 'wp-mosaic-page-generator'); ?>	</button>

						</div>
                
                </div>		

                


				<?php
				}elseif($step==2 && $is_submited==1){ //file submited 
				?>


					<div class="msrm-panel wp-upload-opt">				   

						<?php echo $wpmpg->build_data_table($_POST['file_uploaded']); ?> 	
						
						<div class=" msm-btn-steps-bar-steps" id="msm-btn-steps-bar-steps"  >
							<button class="msrm-btnupload-options mb-1 mt-1 mr-1 btn btn-lg btn-primary btn-custom-primary" type="button" id="wmp-btn-back-to-step1"><span><i class="fa fa-arrow-left"></i></span> <?php _e('BACK TO STEP 1', 'wp-mosaic-page-generator'); ?>	</button>
							<button class="msrm-btnupload-options mb-1 mt-1 mr-1 btn btn-lg btn-primary btn-custom-primary" type="button" id="wmp-btn-start-import-submit"><span><i class="fa fa-arrow-right"></i></span> <?php _e('CLICK TO START', 'wp-mosaic-page-generator'); ?>	</button>

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
			{title : "Image files", extensions : "png,jpg,csv"}
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



     

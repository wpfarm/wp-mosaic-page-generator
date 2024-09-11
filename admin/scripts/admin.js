var $ = jQuery;
let rtime = 4000;
let mmrunningstatus = 1;

let time = 0;
let interval;
jQuery(document).ready(function($) {

    "use strict"; 
    
    $(document).on("click", ".act_way_to_upload", function() {
      
        var selected_step =  $(this).attr("selected-opt");  
        var _sel_val =$(this).val();       
                 
    }); 
    
    $(document).on("click", "#wmp-btn-submit", function() {     
                  
        $("#wmp-form").submit();               
    });

    $(document).on("click", "#wmp-btn-start-import-submit", function() {  
        
        $("#step").val('3');                  
        //$("#wmp-form").submit();     
        
        ms_build_links_array();
    });

    $(document).on("click", ".wpmpg-int-delete-acc", function(e) {
        e.preventDefault();	 
        var doIt = false;
        var acc_id = jQuery(this).attr("acc-id"); 
        doIt=confirm("Are you totally sure?");
		if(doIt){                    
            jQuery.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {"action": "wpmpg_delete_cpf",
                           "acc_id": acc_id},
                    success: function(data){	
                        jQuery("#acc-row-"+ acc_id).slideUp();
                    }
            }); 
        }
        e.preventDefault();         
    });

    $(document).on("click", ".wpmpg-int-delete-acc-val", function(e) {
        e.preventDefault();	 
        var doIt = false;
        var acc_id = jQuery(this).attr("acc-id"); 
        doIt=confirm("Are you totally sure?");
		if(doIt){                    
            jQuery.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {"action": "wpmpg_delete_cpf_value",
                           "acc_id": acc_id},
                    success: function(data){	
                        jQuery("#acc-row-"+ acc_id).slideUp();
                    }
            }); 
        }
        e.preventDefault();         
    });   


    
    
    
    
 $(document).on("click", "#wpmsaic-download-from-url-btn", function() {   
        
    $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {"action": "wpmpg_download_url_file", 
               "link_to_download": $("#file_url").val()
        },                       
        success: function(data){
            var res = jQuery.parseJSON(data);	                

            $("#file_uploaded").val(res.image);
            $("#msrm-imp-options").slideDown(400, function() {
                // Check if the #msrm-imp-options container is set to display:block
                if ($(this).css('display') === 'block') {
                    // Add the "on" class after the container is displayed
                    $("#wpmsaic-download-from-url-btn").addClass('on');
                }
            });	
            $("#msm-btn-steps-bar").slideDown(400);	                  
        }
    });      
});
    
    
    
    
    
    

	
});


function ms_build_links_array (){ 

      mmrunningstatus =1;
    var progressbar = $( "#progressbar" ),
        progressLabel = $( ".progress-label" );
  
    $("#msearch_res").html('');
    $("#mm-title-status").html('Processing ... please wait.');
    $("#cancel-replace-process").html('CANCEL');    
    $("#wmp-btn-start-import-submit" ).prop( "disabled", true );
    $("#wmp-btn-back-to-step1" ).prop( "disabled", true );      

    hide_values_table('wpmosaic-import-tbl-body');
    hide_values_column('wms-val-col');
   
    progressbar.progressbar({
        value: false,
        change: function() {
          progressLabel.text( progressbar.progressbar( "value" ) + "%" );
        },
        complete: function() {
          progressLabel.html( "<span class='icon-check'>Complete</span>" );
        }
    });

    
    $("#wpm-import-opt-bar").hide();  

    $("#wpm-progress-bar").slideDown(400);   
    
    let index = 0;
    let total_rows = $("#wp-total-rows ").val();
    let status_process = 1;
    let last_row = 0;
    
    const loop = setInterval(() => {

        console.log('Total Rows: ' + total_rows);        
  
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {"action": "wpmpg_start_process", 
                    "file_uploaded": $("#file_uploaded").val(),                  
                    "last": $("#last_row").val(),
                    "batch":$("#batch").val(),
                    "wp-custom-post-type":$("#wp-custom-post-type").val(),
                    
                    },                       
                  success: function(data){

                    var res =jQuery.parseJSON(data);	
                    var from_row =  parseFloat($("#last_row").val()) + parseFloat($("#batch").val());
                    last_row = from_row;

                    $("#last_row").val(from_row);

                    var processed_rows_flag =  parseFloat($("#last_row").val() ) +  parseFloat($("#batch").val());
                    
                    if(  processed_rows_flag > parseFloat(total_rows)){
                      //  console.log('Loop finishd: BB' + " cut from : " + processed_rows_flag + "Total rows " + res.total_rows); 
                       // clearInterval(loop) ;
                        //$("#msm-btn-steps-bar-steps").hide(); 
                        //$("#wpm-import-results-block").show();                    


                    }

                    var total_p =  parseFloat(res.cut_to) + parseFloat(res.last);
                    $("#msrm-process-val").html(total_p);                   
                    mark_as_processed(total_p);                  
                    var percent_a = res.percent_a;
                    progressbar.progressbar( "value",  percent_a );   

                    if(  percent_a >= 100){
                        console.log('Loop finishd: BB' + " cut from : " + processed_rows_flag + "Total rows " + res.total_rows); 
                        clearInterval(loop) ;
                        $("#msm-btn-steps-bar-steps").hide(); 
                        $("#wpm-import-results-block").show();                    


                    }
                    
                    


                      
                  }
        });       
        
    }, rtime);	
  
}

function hide_values_table (field_values_clase){
    var checkbox_value = "";
    var i = 0;
    $("."+field_values_clase).each(function () {	    
     
        $(this).addClass('wpm-hide_body-table'); 
        
    });   
}

function hide_values_column (field_values_clase){
    var checkbox_value = "";
    var i = 0;
    $("."+field_values_clase).each(function () {	   
     
        $(this).html('Pending ...'); 

        
        
    });   
}

function mark_as_processed (to_val){ 
    var i = 0;
    while (i <= to_val) {
        $("#wms-tbl-val-"+i).addClass('wpm-processed-row');   
        $("#wms-tbl-val-col-"+i).html('<span class="icon-check"><span class="text">Complete</span></span>');     
        i++;
    }  
}


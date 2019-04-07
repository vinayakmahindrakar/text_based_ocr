jQuery('document').ready(function() {
  jQuery.ajax({            
	url: "ocr.php?action=default", 
	type: "GET",          
	dataType: "json", 
		success: function( data ) { 
			var width = leastSquareRoot(data.length);
			console.log(data.length);
			var result = createRowsAndCols(data, width)
			$("#canvas").html(result);
		},
		error: function(jqXHR, data ) {        
			alert ('Ajax request Failed.');    
		}
	});
	
	$("#do_ocr").click(function(){
        var str_filename = jQuery('#filename').val();
		jQuery('#filename_x').val("Please wait...");
		
		jQuery.ajax({         
		url: "ocr.php?action=ocr&filename="+str_filename, 
		type: "GET",          
		dataType: "text", 
			success: function( response ) {		
				if(response=='yes'){
					jQuery('#filename_x').val("OCR Text Output Is Ready. Click on Search Button.");
					
					jQuery('#filename_x').css({ 'color': 'green'});
					jQuery('#search_text').removeAttr('disabled');
					jQuery('#open_img').removeAttr('disabled');
				} else {
					jQuery('#filename_x').val("Failed ! Do OCR Again...");
					jQuery('#filename_x').css({ 'color': 'red'});
				}
			},
			error: function(jqXHR, data ) {        
				alert ('Ajax request Failed.');    
			}
		});
    });
	
	$("#search_text").click(function(){
        var str_filename = jQuery('#filename').val();
		jQuery('#filename_x').val("Please wait...");
		
		jQuery.ajax({         
		url: "ocr.php?action=search&filename="+str_filename, 
		type: "GET",          
		dataType: "text", 
			success: function( response ) {
				jQuery('#filename_x').val("OCR Activity Completed");	
				$("#output").html(response);
			},
			error: function(jqXHR, data ) {        
				alert ('Ajax request Failed.');    
			}
		});
    });
	
	$("#open_img").click(function(){
        var str_filename = jQuery('#filename').val();
        var win = window.open('input/'+str_filename+'.jpg', '_blank');
		if (win) {
			win.focus();
		} else {
			alert('Please allow popups for this website');
		}
    });
});

function leastSquareRoot (n)
{
    // maybe use ceil if you want a wider rectangle vs a taller one 
    // when a square is not possible
    var sr = Math.sqrt(n);
    return Math.floor(sr);
}

function createRowsAndCols (images, width)
{
    var result = "<table>";
    var lsr = leastSquareRoot(images.length);    
    for (i = 0; i < images.length; i++)
    {
        if (i % width == 0)
        {
            result += "<tr>";
        }
        
        result += "<td><img onclick=\"load_image(this)\" id=\"inv_" + i + "\" class=\"cls_img\" title=\"" + images[i] + "\" src=\"http://localhost/ocr_test/input/" + images[i] + "\"></td>\n";
        
        if (i % width == width - 1 || i == images.length - 1)
        {
            result += "</tr>";
        }
    }
    
    result += "</table>";
    return result;
}

function load_image(imj_obj)
{
	jQuery('#filename').val('');
	jQuery('.cls_img').css('border', "solid 1px blue");	
	jQuery('#search_text').attr("disabled", "disabled");
	
	var sel_image_id = imj_obj.id;
	jQuery('#'+sel_image_id).css('border', "solid 2px blue");
	
	var arr_filename = jQuery('#'+sel_image_id).attr('title');
	
	var str_filename = arr_filename.split(".")[0];
	jQuery('#filename').val(str_filename);
	
	jQuery.ajax({         
	url: "ocr.php?action=checkfile&filename="+str_filename, 
	type: "GET",          
	dataType: "text", 
		success: function( response ) {	
			jQuery('#open_img').removeAttr('disabled');
			if(response=='yes'){
				jQuery('#filename_x').val("OCR Text Output Is Ready. Click on Search Button.");				
				jQuery('#filename_x').css({ 'color': 'green'});
				jQuery('#search_text').removeAttr('disabled');
			} else {
				jQuery('#filename_x').val("Do OCR First");
				jQuery('#filename_x').css({ 'color': 'red'});
			}
		},
		error: function(jqXHR, data ) {        
			alert ('Ajax request Failed.');    
		}
	});
}
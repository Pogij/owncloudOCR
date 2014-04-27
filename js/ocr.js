OC.ImageOcr = {
		
			/**
			 * On load app gets server info (which tesseract languages are installed).
			 */
			initialize: function() {
				var ocrUrl = OC.linkTo('images_ocr', 'ajax/getServerInfo.php');
				$.ajax({
					type: 'GET',
					url: ocrUrl,
					dataType: 'json',
					context: document.body
					}).done(function() {
						if (arguments[2] != undefined) {
							if (arguments[2].responseJSON.status == "success") {
								OC.ImageOcr.languages = arguments[2].responseJSON.languages;
							}
						}
					});
		    },
		
		    
		    /**
		     * Image file OCR reading is selected.
		     */
		    registerImg: function(filename) {
		    	OC.ImageOcr.register(filename, 'image');
		    },
		    
		    
		    /**
		     * PDF file OCR reading is selected.
		     */
		    registerPdf: function(filename) {
		    	OC.ImageOcr.register(filename, 'pdf');
		    },
		    
		    
		    /**
		     * Handles events.
		     */
		    register:function(fileName, filetype) {
		    	var createDropDown = true;
				// Check if drop down is already visible for a different file.
				if (($('#dropdown').length > 0) ) {
					//if ( $('#dropdown').hasClass('drop-versions') && file == $('#dropdown').data('file')) {
					if ( $('#dropdown').hasClass('drop-versions')) {
						createDropDown = false;
					}
					$('#dropdown').remove();
					$('tr').removeClass('mouseOver');
				}

				if(createDropDown === true) {
					OC.ImageOcr.showDropDown(fileName, filetype);
				}
		        
		    },
		    
		    
		    /**
		     * Opens drop down menu.
		     */
		    showDropDown:function(filename, filetype) {
		    	
		    	var html = '<div id="dropdown" class="drop drop-versions">';
		    	
	    		html += "<table style='width: 100%'>" +
	    				"<tr>" +
	    				"<td>" +
	    				t('images_ocr', 'Select language') + ":" +
	    				"</td>" +
	    				"<td>" +
	    				"<select id='ocrLanguageSelect'>"
	    		for (var i = 0 ; i < this.languages.length ; i++) {
	    			html += "<option value='" + this.languages[i] + "'>" + this.languages[i] + "</option>";
	    		}
	    		html += "</select>" +
	    				"</td>" +
	    				"<td>" +
	    				"<img id='ocrLoading' src='" + OC.imagePath('images_ocr', 'loading.gif') + "'  style='visibility:hidden; height:25px; width=25px;' >" +
	    				"</td>" +
	    				"<td>" +
	    				"<button id='odfReading' type='button'>" + t('images_ocr', 'Perform OCR&#x00A;reading') + "</button>" +
	    				"</td>" +
	    				"</tr>" +
	    				"</table>";
	    		
	    		if (filetype == "image") {
	    			// For images there is option to perform OCR reading in seperate view.
	    			var ocrUrl = OC.linkTo('images_ocr', 'ocr.php') + '?path=' + encodeURIComponent($('#dir').val()).replace(/%2F/g, '/')+'/' + encodeURIComponent(filename);
	    			html += "<hr class='ocrLine' width='90%'>";
		    		html += "<a id='previewLink' href='" + ocrUrl + "'>" + t('images_ocr', 'OCR reading with preview') + "</a>";
	    		}
		    	
		    	html += '</div>';
		    	
		    	if (filename) {
		    		fileEl = FileList.findFileEl(filename);
		    		fileEl.addClass('mouseOver');
		    		$(html).appendTo(fileEl.find('td.filename'));
		    	} else {
		    		$(html).appendTo($('thead .share'));
		    	}
		    	
		    	$("#odfReading").click(function() {
		    		OC.ImageOcr.executeReading(filename, filetype);
		    	});
		    },
		    
		    
		    /**
		     * Closes drop down menu.
		     */
		    hideDropDown:function(filename) {
		    	$('#dropdown').remove();
		    	fileEl = FileList.findFileEl(filename);
	    		fileEl.removeClass('mouseOver');
		    },
		    
		    
		    /**
		     * Function sends server request to execute printing.
		     */
		    executeReading:function(filename, filetype) {
				var lan = $('#ocrLanguageSelect').val();
				document.getElementById("ocrLoading").style.visibility = "visible";
				$.ajax({
		            type: "GET",
		            url: OC.filePath('images_ocr','ajax','ocrReading.php'),
		            data: "image=" + OC.get('dir').value + '/' + filename + "&language=" + lan + "&filetype=" + filetype + "&save=1",
		            dataType: 'json',
		            success: function(response, status) {
		            	if (response.success == "error") {
		            		var alertText = t('images_ocr', response.message);
		            		alertText = alertText.replace("<br />", "\n");
		            		alert(alertText);
		            	}
		            	OC.ImageOcr.hideDropDown(filename);
		            
		            	FileList.reload();
		            }
		        });
		    }
};

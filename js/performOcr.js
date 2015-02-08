$(document).ready(function() {
		
    $('#ocrButton').click(
        function() {
            var img = $('#ocrimage').attr('alt');
            var lan = $('#language').val();
            $.ajax({
                type: "GET",
                url: OC.filePath('images_ocr','ajax','ocrReading.php'),
                data: "image=" + img + "&language=" + lan,
                success: function(response, status) {
                    var result = jQuery.parseJSON(response);
                    if (result.success === "error") {
                        $('#errorplace').html(result.message);
                    } else {
                        $("#saveButton").attr("disabled", false);
                        $("#filename").attr("disabled", false);
                        $("#textvalue").attr("disabled", false).focus();
                        $("#textvalue").val(result.filedata);
                        $("#filename").val(result.filename);
                    }
                }
            });
    });
    
    
    $('#saveButton').live("click", function() {
    	var textval = $("#textvalue").val();
    	var filename = $("#filename").val();
    	var img = $('#ocrimage').attr('alt');
    	if (filename.length > 0) {
            $.ajax({
                type: "POST",
                url: OC.filePath('images_ocr','ajax','ocrSaving.php'),
                data: "text=" + textval + "&filename=" + filename + "&image=" + img,
                dataType: "json",
                success: function(response, status) {
                    if (response.success === "success") {
                        alert(t('images_ocr', response.message));
                    }
                }
            });
    	}
    });
    
    
    var imagePath = $('#imagepath').attr('value');
    if (imagePath !== '') {
    	var path = OC.filePath('images_ocr', 'ajax', 'viewImage.php') + '?img=' + encodeURIComponent(imagePath);
        $('#ocrimage').attr('src', path);
    }
    
});
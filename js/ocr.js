var ImageOcr = ImageOcr || {};


(function(window, $, exports, undefined) {
    /**
     * On load app gets server info (which tesseract languages are installed).
     */
    exports.initialize = function() {
        var ocrUrl = OC.linkTo('images_ocr', 'ajax/getServerInfo.php');
        $.ajax({
            type: 'GET',
            url: ocrUrl,
            dataType: 'json',
            context: document.body
            }).done(function() {
                if (arguments[2] !== undefined) {
                    if (arguments[2].responseJSON.status === "success") {
                        ImageOcr.languages = arguments[2].responseJSON.languages;
                    }
                }
            });
    },


    /**
     * Image file OCR reading is selected.
     * @param filename
     */
    exports.registerImg = function(filename) {
        ImageOcr.register(filename, 'image');
    },


    /**
     * PDF file OCR reading is selected.
     * @param filename
     */
    exports.registerPdf = function(filename) {
        ImageOcr.register(filename, 'pdf');
    },


    /**
     * Handles events.
     * @param filename
     * @param filetype
     */
    exports.register = function(filename, filetype) {
        var createDropDown = true;
        // Check if drop down is already visible for a different file.
        if (($('#dropdown').length > 0)) {
            //if ( $('#dropdown').hasClass('drop-versions') && file == $('#dropdown').data('file')) {
            if ( $('#dropdown').hasClass('drop-versions')) {
                createDropDown = false;
            }
            $('#dropdown').remove();
            $('tr').removeClass('mouseOver');
        }

        if(createDropDown === true) {
            ImageOcr.showDropDown(filename, filetype);
        }

    },


    /**
     * Opens drop down menu.
     * @param filename
     * @param filetype
     */
    exports.showDropDown = function(filename, filetype) {

        var html = '<div id="dropdown" class="drop drop-versions">';

        html += "<table style='width: 100%'>" +
                "<tr>" +
                "<td>" +
                t('images_ocr', 'Select language') + ":" +
                "</td>" +
                "<td>" +
                "<select id='ocrLanguageSelect'>";
        
        if (this.languages !== undefined) {
            for (var i = 0 ; i < this.languages.length ; i++) {
                html += "<option value='" + this.languages[i] + "'>" + this.languages[i] + "</option>";
            }
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

        if (filetype === "image") {
            // For images there is option to perform OCR reading in seperate view.
            var ocrUrl = OC.linkTo('images_ocr', 'index.php') + '?path=' + encodeURIComponent($('#dir').val()).replace(/%2F/g, '/') + '/' + encodeURIComponent(filename);
            html += "<hr class='ocrLine' width='90%'>";
            html += "<a title='' id='previewLink' href='" + ocrUrl + "'>" + t('images_ocr', 'OCR reading with preview') + "</a>";
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
            ImageOcr.executeReading(filename, filetype);
        });
    },


    /**
     * Closes drop down menu.
     * @param filename
     */
    exports.hideDropDown = function(filename) {
        $('#dropdown').remove();
        fileEl = FileList.findFileEl(filename);
        fileEl.removeClass('mouseOver');
    },


    /**
     * Function sends server request to execute printing.
     * @param filename
     * @param filetype
     */
    exports.executeReading = function(filename, filetype) {
        var lan = $('#ocrLanguageSelect').val();
        document.getElementById("ocrLoading").style.visibility = "visible";
        $.ajax({
            type: "GET",
            url: OC.filePath('images_ocr','ajax','ocrReading.php'),
            data: "image=" + OC.get('dir').value + '/' + filename + "&language=" + lan + "&filetype=" + filetype + "&save=1",
            dataType: 'json',
            success: function(response, status) {
                if (response.success === "error") {
                    var alertText = t('images_ocr', response.message);
                    alertText = alertText.replace("<br />", "\n");
                    alert(alertText);
                }
                ImageOcr.hideDropDown(filename);

                FileList.reload();
            }
        });
    }
}) (window, jQuery, ImageOcr);

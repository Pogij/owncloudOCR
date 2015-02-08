$(document).ready(function() {
    
    if (typeof OCA !== 'undefined' 
        && typeof OCA.Files !== 'undefined'
        && typeof OCA.Files.fileActions !== 'undefined') {
    
        OCA.Files.fileActions.register(
            'image',
            t('images_ocr', 'OCR Read'),
            OC.PERMISSION_UPDATE,
            OC.imagePath('images_ocr', 'imageOcr.svg'),
            OC.ImageOcr.registerImg
        );
        OCA.Files.fileActions.register(
            'application/pdf',
            t('images_ocr', 'OCR Read'),
            OC.PERMISSION_UPDATE,
            OC.imagePath('images_ocr', 'imageOcr.svg'),
            OC.ImageOcr.registerPdf
        );
    }

    OC.ImageOcr.initialize();
});
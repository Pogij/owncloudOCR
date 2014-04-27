$(document).ready(function() {
	if (typeof FileActions !== 'undefined') {
		FileActions.register(
			'image',
			t('images_ocr', 'OCR Read'),
			OC.PERMISSION_UPDATE,
			OC.imagePath('images_ocr', 'imageOcr.svg'),
			OC.ImageOcr.registerImg
		);
	}
	
	OC.ImageOcr.initialize();
});
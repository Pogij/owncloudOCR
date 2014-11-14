<?php

OCP\Util::addscript('images_ocr','performOcr');

if (isset($_['message'])) {
	echo $_['message'];
} else {
?>

<?php //$_['appNavigation']->printPage(); ?>

<div id="ocrcontent">
	<div id="controls">
		<?php print_unescaped($_['breadcrumb']); ?>
	</div>

	<div id="ocr">
	
		<div id="image">
			<?php
			$version = OC_Util::getVersion();
			
			$path = "";
			if ($version[0] >= 7) {
				$path = \OC\Files\Filesystem::getPath($_['path']);
				$img = trim($fullPath . $_['path']);
				echo '<img id="ocrimage" class="ocrimage" src="" alt="'.$img.'" />';
				echo '<input type="hidden" id="imagepath" value="'.$img.'" />';
			} elseif ($version[0] >= 5) {
				$path = \OC_Filesystem::normalizePath($_['path']);
				echo '<img id="ocrimage" class="ocrimage" src="'.\OC::$WEBROOT.'/index.php/?app=images_ocr&getfile=ajax%2FviewImage.php?img='.$path.'" alt="'.$path.'" />';
			} else {
				$path = \OC_Files::normalizePath($_['path']);
				echo '<img id="ocrimage" class="ocrimage" src="'.\OC::$WEBROOT.'/?app=images_ocr&getfile=ajax%2FviewImage.php?img='.$path.'" alt="'.$path.'" />';
			}
			?>
		</div>
		
		<div id="action" class="middle">
			<?php echo $l->t('Select tesseract language:'); ?> 
			<select id="language">
				<?php
				foreach($_['languages'] as $language) {
					if ($language == "eng") {
						print("<option value=\"$language\" selected=\"selected\">$language</option>");
					} else {
						print("<option value=\"$language\">$language</option>");
					}
				}
				?>
			</select>
			<br />
			<input type="button" id="ocrButton" name="Perform OCR" value="<?php echo $l->t('Perform OCR&#x00A;reading'); ?>" />
		</div>
		
		<div id="ocrtext">
		
			<div id="errorplace"></div>
			<textarea id="textvalue" disabled="disabled">
			</textarea>
			<br />
			<?php echo $l->t('Filename:'); ?> <input type="text" id="filename" maxlength="256" value="" disabled="disabled" />.txt
			<br />
			<input type="button" id="saveButton" name="Save" value="<?php echo $l->t('Save text'); ?>" disabled="disabled" />
			
		</div>
	
	</div>
</div>
<?php 
} //ends else

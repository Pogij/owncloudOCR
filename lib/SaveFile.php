<?php

/**
 * 
 * @param unknown $filename - Name of the file
 * @param unknown $folder - Position of the file
 * @param unknown $text - File content
 */
function saveTextFile($filename, $folder, $text) {
	$file = $folder . $filename . ".txt";
	
	$target = OC_Filesystem::normalizePath($file);
	
	// If file with the same name already exists.
	$count = 1;
	while (OC_Filesystem::file_exists($target) == true) {
		$file = $folder . $filename . "(" . $count . ").txt";
		$target = OC_Filesystem::normalizePath($file);
		$count = $count + 1;
	}
	
	OC_Filesystem::file_put_contents($target, $text);
}


/**
 * 
 * @param unknown $filename - Name of the file
 * @param unknown $folder - Position of the file
 * @param unknown $fileSource - File in temporary folder which will be copied as OCR-ed file.
 */
function savePdfFile($filename, $folder, $fileSource) {
	$file = $folder . $filename . ".pdf";
		
	$target = OC_Filesystem::normalizePath($file);
	
	// If file with the same name already exists.
	$count = 1;
	while (OC_Filesystem::file_exists($target) == true) {
		$file = $folder . $filename . "(" . $count . ").pdf";
		$target = OC_Filesystem::normalizePath($file);
		$count = $count + 1;
	}
		
	OC_Filesystem::fromTmpFile($fileSource, $target);
}
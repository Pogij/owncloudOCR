<?php

/**
* ownCloud - Performs reading of given image.
*
* @author Matevž Pogačar
* @copyright 2012 Matevž Pogačar
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Lesser General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/


OCP\JSON::checkAppEnabled('images_ocr');
$user = \OC_User::getUser();

require_once 'apps/images_ocr/lib/SaveFile.php';



/* READ GET VALUES. */
$image = "";
if (isset($_GET['image'])) {
	$image = $_GET['image'];
} else {
	return;
}

$language = "";
if (isset($_GET['language'])) {
	if ($_GET['language'] == '' or $_GET['language'] == 'null') {
		$language = null;
	} else {
		$language = $_GET['language'];
	}
} else {
	$language = null;
}

$filetype = "";
if (isset($_GET['filetype'])) {
	if ($_GET['filetype'] == '' or $_GET['filetype'] == 'null') {
		$filetype = "image";
	} else {
		$filetype = $_GET['filetype'];
	}
} else {
	$filetype = "image";
}

$save = "";
if (isset($_GET['save'])) {
	if ($_GET['save'] === '1') {
		$save = true;
	} else {
		$save = false;
	}
} else {
	$save = false;
}





/* GET IMAGES NAME AND LOCATION. */
$folderBreak = strrpos($image, "/");
if ($folderBreak !== false) {
	$namestart = strrpos($image, "/") + 1;
} else {
	$namestart = 0;
}
$nameend = strrpos($image, ".");
$filename = "";

if ($nameend > $namestart && $namestart >= 0) {
	$filename = substr($image, $namestart, $nameend - $namestart);
}

// Creatis temporary file in /tmp folder.
$tmpfile = \OC_Filesystem::toTmpFile($image);


try {

	//We define command that will be executed.
	//In case selected file is image.
	if ($filetype == "image") {
	
		$command = 'tesseract ' . $tmpfile . ' ' . $tmpfile;
		if ($language != null) {
			if (strlen($language) > 0) {
				$command = $command . ' -l ' . $language;
			}
		}
	
	} else {
	//Otherwise pdf reading.
		exec('pwd', $_out, $success);
		$command = $_out[0] . '/apps/images_ocr/lib/OCRmyPDF/OCRmyPDF.sh';
		if (strlen($language) > 0) {
			$command = $command . ' -l ' . $language;
		}
		$ocredPdfFile = substr($tmpfile, 0, strrpos($tmpfile, "/") + 1) . 'tmp_' . substr($tmpfile, strrpos($tmpfile, "/") + 1); 
		$command .= ' ' . $tmpfile . ' ' . $ocredPdfFile;
		 
	}
	
	
	/*READING EXECUTION.*/
	if (!stristr(PHP_OS, 'WIN')) {
		/* NON WINDOWS OS SERVER. */
	
		/*Executes system command tesseract, which performs OCR reading.*/
		exec($command, $_out, $success);
	
		if ($success > 0) {
			throw new Exception();
		}
			
		if ($filetype == "image") {
			$filedata = file_get_contents($tmpfile.".txt");
			
			/*Removes temporary file.*/
			exec('rm ' . $tmpfile . ".txt");
		}
		exec('rm ' . $tmpfile);
	
	} else {
		/* WINDOWS OS SERVER. */
			
		$descriptors = array(
				0 => array("pipe", "r"),
				1 => array("pipe", "w"),
				2 => array("pipe", "w")
		);
		$cwd = $pathtess;
			
		$process = proc_open($command, $descriptors, $pipes, $cwd);
			
		$success = 0;
		if(is_resource($process)) {
			fclose($pipes[0]);
			fclose($pipes[1]);
			fclose($pipes[2]);
			$success = proc_close($process);
		}
			
		if ($success > 0) {
			throw new Exception();
		}
			
		$filedata = file_get_contents($tmpfile . ".txt");
	
		exec('cmd /c del ' . $tmpfile . '.txt');
			
	}
	
	
	/*IF SAVE PARAMETER WAS NOT SENT FROM BROWSER THE READ DATA IS SENT BACK.*/
	if ($save === false) {
		$array = array("success"=>"success", "filedata"=>$filedata, "filename"=>$filename);
		echo json_encode($array);
		return;
		
	/*OTHERWISE FILE IS SAVED.*/
	} else {
		if ($folderBreak != false) {
			$foldernameend = strrpos($image, "/") + 1;
		} else {
			$foldernameend = 0;
		}
		
		$folder = substr($image, 0, $foldernameend);
		
		if ($filetype == "image") {
			saveTextFile($filename, $folder, $filedata);
			
		} else {
			savePdfFile($filename, $folder, $ocredPdfFile);
			exec('rm ' . $ocredPdfFile);
		}
	}
	
	$array = array("success" => "success");
	echo json_encode($array);
	
} catch (Exception $e) {
	if (isset($_out[1]) && $_out[1] == "Page 0001: Page already contains font data !!!") {
		$errorstring = "PDF file alredy contains font data!";
		exec('rm ' . $tmpfile);
	} else {
		$errorstring = "OCR reading was not performed!<br />Check if server has installed Tesseract OCR.";
	}
	
	if (stristr(PHP_OS, 'WIN')) {
		$errorstring .= "<br />Check if IIS user (IUSR, IIS_IUSR) has granted rights for \'C:\Windows\Temp\\' folder.";
	}
	$array = array("success" => "error", "message" => $errorstring);
	echo json_encode($array);
}



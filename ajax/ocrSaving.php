<?php
OCP\JSON::checkLoggedIn();

OCP\JSON::checkAppEnabled('images_ocr');

require_once 'apps/images_ocr/lib/SaveFile.php';


/* READ POST-ED VALUES. */
$image = "";
if (isset($_POST['image'])) {
	$image = $_POST['image'];
} else {
	return;
}

$filename = "";
if (isset($_POST['filename'])) {
	$filename = $_POST['filename'];
} else {
	$namestart = strrpos($image, "/") + 1;
	$nameend = strrpos($image, ".");
	$filename = "";
	if ($nameend > $namestart and $namestart >= 0) {
		$filename = substr($image, $namestart, $nameend - $namestart);
	}
}

$text = "";
if (isset($_POST['text'])) {
	$text = $_POST['text'];
}

/* PERFORM SAVING. */
if (strlen($filename) > 0) {

	$foldernameend = strrpos($image, "/") + 1;
	$folder = substr($image, 0, $foldernameend);
	
	saveTextFile($filename, $folder, $text);
}

$array = array("success" => "success", "message"=>"File successfully saved.");
echo json_encode($array);
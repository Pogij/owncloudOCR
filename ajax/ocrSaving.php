<?php
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('images_ocr');

/* READ POST-ED VALUES. */
$image = filter_input(INPUT_POST, "image");
if ($image === null) {
    return;
}

$filename = filter_input(INPUT_POST, "filename");
if ($filename === null) {
    $namestart = strrpos($image, "/") + 1;
    $nameend = strrpos($image, ".");
    $filename = "";
    if ($nameend > $namestart and $namestart >= 0) {
        $filename = substr($image, $namestart, $nameend - $namestart);
    }
}

$text = filter_input(INPUT_POST, 'text');
if ($text === null) {
    $text = '';
}

/* PERFORM SAVING. */
if (strlen($filename) > 0) {

    $foldernameend = strrpos($image, "/") + 1;
    $folder = substr($image, 0, $foldernameend);

    SaveFile::saveTextFile($filename, $folder, $text);
}

$array = array("success" => "success",
               "message"=>"File successfully saved.");
echo json_encode($array);

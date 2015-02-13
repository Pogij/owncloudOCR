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

OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('images_ocr');
$user = \OC_User::getUser();

/* READ GET VALUES. */
$image = filter_input(INPUT_GET, "image");
if ($image === null) {
    return;
}

$language = filter_input(INPUT_GET, "language");
if ($language === null || $language == '' || $language == 'null') {
    $language = null;
}

$filetype = filter_input(INPUT_GET, "filetype");
if ($filetype === null || $filetype == '' || $filetype == 'null') {
    $filetype = "image";
}

$save = filter_input(INPUT_GET, "save");
if ($save !== null && $save === '1') {
    $save = true;
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
$tmpfile = OC\Files\Filesystem::toTmpFile($image);


try {

    $tesseract = new Tesseract($tmpfile, $filetype, $language);
    $filedata = $tesseract->executeReading();


    /*IF SAVE PARAMETER WAS NOT SENT FROM BROWSER THE READ DATA IS SENT BACK.*/
    if ($save === false) {
        $array = array("success" => "success",
                        "filedata" => $filedata,
                        "filename" => $filename);
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
            $success = SaveFile::saveTextFile($filename, $folder, $filedata);

        } else {
            $success = SaveFile::savePdfFile($filename, $folder, $filedata);
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

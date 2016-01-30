<?php
/**
* ownCloud
*
* @author Matevž Pogačar
* @copyright 2014 Matevž Pogačar <matevz.pogacar@gmail.com>
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
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*/

OC::$CLASSPATH['Languages'] = 'apps/images_ocr/lib/Languages.php';
OC::$CLASSPATH['Helper'] = 'apps/images_ocr/lib/Helper.php';
OC::$CLASSPATH['SaveFile'] = 'apps/images_ocr/lib/SaveFile.php';
OC::$CLASSPATH['Tesseract'] = 'apps/images_ocr/lib/Tesseract.php';

if (!defined('MAX_DEPTH_CHECK')) {
    define('MAX_DEPTH_CHECK', 6);
}

// In Unix system serches for tessdata location and creates link to that directory in apps/images_ocr folder.
if (stristr(PHP_OS, 'WIN')) {
	
    $pdfSupport = false;
	
} else {
	
    exec('tesseract -v 2>&1', $result);
    $versioning = explode(' ', $result[0]);
    if ($versioning[0] == "tesseract") {
        $versions = explode('.', $versioning[1]);
        
        $pdfSupport = false;
        if (intval($versions[0]) >= 3) {
            if (intval($versions[0]) > 3) {
                $pdfSupport = true;
            } elseif (intval($versions[1]) > 2) {
                $pdfSupport = true;
            } elseif (intval($versions[1]) == 2 && isset($versions[2]) && $versions[2] >= 1) {
                $pdfSupport = true;
            }
        }
    } else {
        $pdfSupport = false;
    }

    $config = \OC::$server->getConfig();
    $versionUpdated = false;
    $currentOwncloudVersionArray = OC_Util::getVersion();
    $lastOwncloudVersion = $config->getAppValue('images_ocr', 'last_owncloud_version');
    $lastOwncloudVersionArray = explode('_', $lastOwncloudVersion);
    if (!is_null($lastOwncloudVersion)) {
        foreach ($currentOwncloudVersionArray as $id => $versionNumberAtId) {
            if ($versionNumberAtId > $lastOwncloudVersionArray[$id]) {
                $versionUpdated = true;
                break;
            }
        }
    }
    if (is_null($lastOwncloudVersion) || $versionUpdated == true) {
        $versionUpdated = true;
        $config->setAppValue('images_ocr', 'last_owncloud_version', implode('_', $currentOwncloudVersionArray));
        exec('rm -f apps/images_ocr/tess');
    }


    if (!file_exists('apps/images_ocr/tess')) {
        $depth = 1;

        while ($depth < MAX_DEPTH_CHECK) {
            $depth += 1;
            exec('find / 2>/dev/null -maxdepth '.$depth.' -name "tessdata" -type d', $result);
            if ($result != null && strlen($result[0]) > 0) {
                exec('ln -s '.$result[0].' apps/images_ocr/tess');
                break;
            }
        }
    }
}

OCP\Util::addStyle('images_ocr', 'ocrmenu');

OCP\Util::addScript('images_ocr', 'ocr');

if ($pdfSupport == true) {
    OCP\Util::addScript('images_ocr', 'ocrPdfSupport');
} else {
    OCP\Util::addScript('images_ocr', 'ocrNoPdfSupport');
}


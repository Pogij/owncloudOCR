<?php
/**
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
 */

namespace OCA\Images_Ocr;

class Languages {
	
    /**
     * Get languages supported by installed tesseract.
     * @return string[]
     */
    public static function getLanguages() {
        
        $result = null;
        $success = 0;
        
        if (stristr(PHP_OS, 'WIN')) {
            /* WINDOWS OS SERVER. */
            exec('cmd /c tesseract.exe --list-langs', $result, $success);
            $success = 1;
        } else {
            /* NON WINDOWS OS SERVER. */
            exec('tesseract --list-langs 2>&1', $result, $success);
        }

        // Newer version of tesseract (3.02.02) has option to print list of available languages' trained data.
        if ($success == 0 && count($result) != "Array[0]") {
            if (is_array($result)) {
                $traineddata = $result;
            } else {
                $traineddata = explode(' ', $result);
            }

            $tds = array();
            foreach ($traineddata as $td) {
                $tdname = trim($td);
                if (strlen($tdname) == 3) {
                    array_push($tds, $tdname);
                }
            }
            /* If older version of tesseract is installed existing traineddata files are manually searched
             * (tested in ubuntu with tesseract installed from repositories and windows with tesseract installed).
             */
        } else {
            if (stristr(PHP_OS, 'WIN')) {
                /* WINDOWS OS SERVER. */
                exec('cmd /c where tesseract.exe', $tloc, $res);
                $tessdata_location = strstr($tloc[0], 'tesseract.exe', true)."tessdata\\";
            } else {
                /* NON WINDOWS OS SERVER. */
                if (file_exists('apps/images_ocr/tess')) {
                    $tessdata_location = "apps/images_ocr/tess";
                } else {
                    // Ubuntu Linux tesseract languages data (if writing to a app folder is disabled than the tessdata will still be found on Ubuntu server).
                    $tessdata_location = "/usr/share/tesseract-ocr/tessdata/";
                }
            }

            $dir = dir($tessdata_location);
            $tds = array();
            while (($entry = $dir->read()) !== false) {
                if (strpos($entry, '.traineddata') > 0) {
                    $tdname = strstr($entry, '.traineddata', true);
                    if ($tdname !== "*") {
                        array_push($tds, $tdname);
                    }
                }
            }
            $dir->close();
        }

        return $tds;

    }
}
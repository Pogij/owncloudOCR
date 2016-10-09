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

use \OC\Files\Filesystem;

class SaveFile {
    
    /**
     * Function designed to save ocr content to txt file.
     * 
     * @param string $filename - Name of the file
     * @param string $folder - Position of the file
     * @param string $text - File content
     */
    public static function saveTextFile($filename, $folder, $text) {
        $success = true;
        try {
            $target = self::executeSaveFile($filename, $folder, 'txt');
            \OC\Files\Filesystem::file_put_contents($target, $text);
        } catch (Exception $e) {
            $success = false;
        }
        
        return $success;
    }


    /**
     * Function designed to save ocr content to pdf file.
     * 
     * @param string $filename - Name of the file
     * @param string $folder - Position of the file
     * @param string $fileSource - File in temporary folder which will be copied as OCR-ed file.
     */
    public static function savePdfFile($filename, $folder, $fileSource) {
        $success = true;
        try {
            $target = self::executeSaveFile($filename, $folder, 'pdf');
            Filesystem::fromTmpFile($fileSource, $target);
        } catch (Exception $e) {
            $success = false;
        }
        
        return $success;
    }
    
    
    /**
     * Function designed to save ocr content to pdf file.
     * 
     * @param string $filename - Name of the file
     * @param string $folder - Position of the file
     * @param string $extension - File extension
     * @return string - target file location with name
     */
    private static function executeSaveFile($filename, $folder, $extension) {
        
        $target =Filesystem::normalizePath($folder . $filename . '.' . $extension);

        // If file with the same name already exists.
        $count = 1;
        while (Filesystem::file_exists($target) == true) {
            $file = $folder . $filename . '(' . $count . ').' . $extension;
            $target = Filesystem::normalizePath($file);
            $count = $count + 1;
            if ($count > 1000) {
                throw new Exception('Unable to find accessible file location');
            }
        }
        
        return $target;
    }

}
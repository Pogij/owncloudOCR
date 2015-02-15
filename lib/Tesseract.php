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

class Tesseract {
    
    private $tmpfile;
    private $filetype;
    private $language;
    private $ocredFile;
    
    
    /**
     * 
     * @param string $tmpfile - Location of temporary file
     * @param string $filetype - Wheter image of pdf is being processed
     * @param string $language - Language attribute for tesseract command
     */
    public function __construct($tmpfile, $filetype, $language) {
        $this->tmpfile = $tmpfile;
        $this->filetype = $filetype;
        $availableLanguages = Languages::getLanguages();
        if (in_array($language, $availableLanguages)) {
            $this->language = $language;
        } else {
            $this->language = null;
        }
        $this->ocredFile = null;
    }
    
    
    /**
     * Function that controls execution of tesseract
     * @return string
     */
    public function executeReading() {
        $command = $this->buildCommand();
        return $this->executeTesseract($command);
    }


    /**
     * Builds tesseract command
     * @return string
     */
    private function buildCommand() {
        //We define command that will be executed.
        //In case selected file is image.
        
        $languageOption = '';
        if ($this->language != null && strlen($this->language) > 0) {
            $languageOption = ' -l ' . $this->language;
        }
        
        if ($this->filetype == "image") {
            $command = 'tesseract ' . $this->tmpfile . ' ' . $this->tmpfile . $languageOption;
        } else {
            //Otherwise pdf reading.
            $this->ocredFile = substr($this->tmpfile, 0, strrpos($this->tmpfile, "/") + 1) . 'tmp_' . substr($this->tmpfile, strrpos($this->tmpfile, "/") + 1);
            $command = getcwd() . '/apps/images_ocr/lib/OCRmyPDF/OCRmyPDF.sh' . $languageOption . ' ' . $this->tmpfile . ' ' . $this->ocredFile;
        }
        
        return $command;
    }


    /**
     * Executes tesseract command
     * @param string $command
     * @return string
     * @throws Exception
     */
    private function executeTesseract($command) {
        
        $filedata = '';
        
        /*READING EXECUTION.*/
        if (!stristr(PHP_OS, 'WIN')) {
            /* NON WINDOWS OS SERVER. */
            $filedata = $this->executeTesseractLin($command);
        } else {
            /* WINDOWS OS SERVER. */
            $filedata = $this->executeTesseractWin($command);
        }
        
        return $filedata;
    }
    
    
    /**
     * Execute tesseract command on Windows server
     * @param string $command
     * @return string
     * @throws Exception
     */
    private function executeTesseractWin($command) {
        
        $success = 0;
        
        $descriptors = array(
                            0 => array("pipe", "r"),
                            1 => array("pipe", "w"),
                            2 => array("pipe", "w")
                        );
        
        $cwd = $pathtess;

        $process = proc_open($command, $descriptors, $pipes, $cwd);

        if(is_resource($process)) {
            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $success = proc_close($process);
        }

        if ($success > 0) {
            throw new Exception();
        }

        $txtFilename = $this->tmpfile . ".txt";
        $filedata = file_get_contents($txtFilename);

        exec('cmd /c del ' . $txtFilename);

        return $filedata;
    }
    
    
    /**
     * Execute tesseract command on Linux,BSD server
     * @param string $command
     * @return string
     * @throws Exception
     */
    private function executeTesseractLin($command) {
        
        $success = 0;
        
        /*Executes system command tesseract, which performs OCR reading.*/
        exec($command, $_out, $success);

        if (isset($_out[1]) && $_out[1] == "Page 0001: Page already contains font data !!!") {
            throw new Exception('PDF file alredy contains font data!', 101);
            exec('rm ' . $this->tmpfile);
        } elseif ($success > 0) {
            throw new Exception('Tesseract reading was not executed successfully', 102);
        }

        if ($this->filetype == "image") {
            $txtFilename = $this->tmpfile . ".txt";
            $filedata = file_get_contents($txtFilename);

            /*Removes temporary file.*/
            exec('rm ' . $txtFilename);
        } else {
            /** If PDF file reading was performed function returns ocr-ed file name with location. */
            $filedata = $this->ocredFile;
        }

        exec('rm ' . $this->tmpfile);
        
        
        
        return $filedata;
    }
    
}


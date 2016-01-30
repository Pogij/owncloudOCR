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

/** @var $this OC\Route\Router */

$this->create('images_ocr_ocr', '/')
	->actionInclude('images_ocr/index.php');
$this->create('images_ocr_getServerInfo', 'ajax/getServerInfo.php')
        ->actionInclude('images_ocr/ajax/getServerInfo.php');
$this->create('images_ocr_ocrReading', 'ajax/ocrReading.php')
        ->actionInclude('images_ocr/ajax/ocrReading.php');
$this->create('images_ocr_ocrSaving', 'ajax/ocrSaving.php')
        ->actionInclude('images_ocr/ajax/ocrSaving.php');
$this->create('images_ocr_viewImage', 'ajax/viewImage.php')
        ->actionInclude('images_ocr/ajax/viewImage.php');

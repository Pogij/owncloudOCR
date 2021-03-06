<?php
/**
* ownCloud - Performs reading of given image.
*
* @author Matevž Pogačar
* @copyright 2014 Matevž Pogačar
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

use OCA\Images_Ocr\Languages;
OCP\JSON::checkLoggedIn();
OCP\JSON::checkAppEnabled('images_ocr');

$availableLanguages = Languages::getLanguages();

\OCP\JSON::success(array('languages' => $availableLanguages));


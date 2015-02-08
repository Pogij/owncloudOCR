<?php

/**
 * ownCloud - gallery application
 *
 * @author Ike Devolder
 * @copyright 2012 Ike Devolder
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

$img = filter_input(INPUT_GET, 'img');

$view_file = OC\Files\Filesystem::getLocalFile($img);

if (file_exists($view_file)) {
    $image = new OC_Image($view_file);
    OCP\Response::enableCaching(3600 * 24); // 24 hour
    $image->show();
}

<?php
/**
 * ownCloud - OCR App
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

try {
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('images_ocr');
} catch (Exception $e) {
    $x = 1;
}
OCP\Util::addStyle('images_ocr','ocr');
OCP\Util::addStyle('files','files');

$path = filter_input(INPUT_GET, "path");
if ($path === null) {
    $tmpl->assign('message', 'No path specified');
    $tmpl->printPage();
    exit();
}


$dir = \OC\Files\Filesystem::normalizePath(substr($path, 0, strrpos($path, '/')));
if (!\OC\Files\Filesystem::is_dir($dir . '/')) {
    header("HTTP/1.0 404 Not Found");
    exit();
}

$ocVersion = OC_Util::getVersion();

if ($ocVersion[0] >= 7) {
    $breadcrumb = Helper::makeBreadcrumb($dir);
    $homedir = '/';
} else {
    $breadcrumb = \OCA\Files\Helper::makeBreadcrumb($dir);
    $homedir = '';
}

$breadcrumbNav = new OCP\Template('images_ocr', 'part.breadcrumb', '');
$breadcrumbNav->assign('breadcrumb', $breadcrumb);
$breadcrumbNav->assign('baseURL', OCP\Util::linkTo('files', 'index.php') . '?dir=' . $homedir);

$data['breadcrumb'] = $breadcrumbNav->fetchPage();
$data['permissions'] = $permissions;

$tmpl = new OCP\Template('images_ocr', 'ocr', 'user');

$tmpl->assign('path', $path);
$tmpl->assign('breadcrumb', $breadcrumbNav->fetchPage());

$tds = Languages::getLanguages();

$tmpl->assign('languages', $tds);

$tmpl->printPage();


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

OCP\User::checkLoggedIn();

OCP\Util::addStyle('images_ocr','ocr');

require_once 'apps/images_ocr/lib/Languages.php';

$dir = isset( $_GET['path'] ) ? $_GET['path'] : '';
$lastdir = strrpos($dir, '/');
$dir = substr($dir, 0, $lastdir);
$dir = \OC\Files\Filesystem::normalizePath($dir);
if (!\OC\Files\Filesystem::is_dir($dir . '/')) {
	header("HTTP/1.0 404 Not Found");
	exit();
}

$ocVersion = OC_Util::getVersion();

if ($ocVersion[0] >= 7) {
	require_once 'apps/images_ocr/lib/Helper.php';
	$breadcrumb = makeBreadcrumb($dir);
	$homedir = '/';
} else {
	$breadcrumb = \OCA\Files\Helper::makeBreadcrumb($dir);
	$homedir = '';
}

$breadcrumbNav = new OCP\Template('images_ocr', 'part.breadcrumb', '');
$breadcrumbNav->assign('breadcrumb', $breadcrumb);
$breadcrumbNav->assign('baseURL', OCP\Util::linkTo('files', 'index.php') . '?dir='.$homedir);

$data['breadcrumb'] = $breadcrumbNav->fetchPage();
$data['permissions'] = $permissions;

$tmpl = new OCP\Template('images_ocr', 'ocr', 'user');

if (isset($_GET['path'])) {

	$path = $_GET['path'];
	$path = $path;
	$tmpl->assign('path', $path);
	$tmpl->assign('breadcrumb', $breadcrumbNav->fetchPage());
	
	$result = "";
	$success = "";
	$tds = getLanguages();
	
	$tmpl->assign('languages', $tds);
	$tmpl->assign('appNavigation', $nav);
	
	//$tess = new OCA_OcrImages\Tesseract();	//If you want to create class from a library.
	
}else{
	$tmpl->assign('message', 'No path specified');
}

$tmpl->printPage();





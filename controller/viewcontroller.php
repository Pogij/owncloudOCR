<?php
/**
 * @author Lukas Reschke <lukas@owncloud.com>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 *
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Images_Ocr\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCA\Images_Ocr\Helper;
use OCA\Images_Ocr\Languages;


/**
 * Class ViewController
 *
 * @package OCA\Files\Controller
 */
class ViewController extends Controller {

	public function __construct() {
	}

        
        /**
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 *
	 * @return null
	 */
        public function index() {
            
            \OCP\Util::addStyle('images_ocr', 'ocr');
            
            $params = array();
            
            $path = filter_input(INPUT_GET, "path");
            if ($path === null) {
                $params['message'] = 'No path specified';
                $tmpl->printPage();
                exit();
            }

            $dir = \OC\Files\Filesystem::normalizePath(substr($path, 0, strrpos($path, '/')));
            if (!\OC\Files\Filesystem::is_dir($dir . '/')) {
                header("HTTP/1.0 404 Not Found");
                exit();
            }
            
            $breadcrumb = Helper::makeBreadcrumb($dir);
            $homedir = '/';

            //$nav = new \OCP\Template('images_ocr', 'imagepreview', '');
            //$breadcrumbNav = new OCP\Template('images_ocr', 'part.breadcrumb', '');
            //$breadcrumbNav->assign('breadcrumb', $breadcrumb);
            //$breadcrumbNav->assign('baseURL', OCP\Util::linkTo('files', 'index.php') . '?dir=' . $homedir);

            //$params['breadcrumb'] = $breadcrumbNav->fetchPage();
            //$params['permissions'] = $permissions;


            $params['path'] = $path;
            //$params['breadcrumb'] = $breadcrumbNav->fetchPage();
            $params['languages'] = Languages::getLanguages();
            
            $response = new TemplateResponse(
                    'images_ocr',
                    'index',
                    $params
            );

            return $response;
        }

}

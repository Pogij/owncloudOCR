<?php
/**
 * ownCloud - OCR App
 * Taken from version 6 ajax frontend app
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


class Helper {
    /**
     * Splits the given path into a breadcrumb structure.
     * @param string $dir path to process
     * @return array where each entry is a hash of the absolute
     * directory path and its name
     */

    public static function makeBreadcrumb($dir) {
        $breadcrumb = array();
        $pathtohere = '';
        foreach (explode('/', $dir) as $i) {
            if ($i !== '') {
                $pathtohere .= '/' . $i;
                $breadcrumb[] = array('dir' => $pathtohere, 'name' => $i);
            }
        }
        return $breadcrumb;
    }
}
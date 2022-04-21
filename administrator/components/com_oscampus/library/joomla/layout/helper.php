<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2018-2021 Joomlashack.com. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of OSCampus.
 *
 * OSCampus is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * OSCampus is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSCampus.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die();

abstract class OscampusLayoutHelper extends JLayoutHelper
{
    /**
     * Not particularly happy about this. But it's the simplest way to insert
     * an override path into the middle of the path list
     *
     * @param string $layoutFile
     * @param null   $displayData
     * @param string $basePath
     * @param null   $options
     *
     * @return string
     */
    public static function render($layoutFile, $displayData = null, $basePath = '', $options = null)
    {
        $basePath = empty($basePath) ? static::$defaultBasePath : $basePath;

        // Make sure we send null to FileLayout if no path set
        $basePath = empty($basePath) ? null : $basePath;
        $layout   = new OscampusLayoutFile($layoutFile, $basePath, $options);

        return $layout->render($displayData);
    }
}

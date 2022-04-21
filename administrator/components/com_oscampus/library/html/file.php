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

abstract class OscFile
{
    /**
     * @param string $fileName
     *
     * @return string
     */
    public static function icon($fileName)
    {
        // Extract file format
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'pdf':
                $icon = 'fa-file-pdf-o';
                break;

            case 'html':
                $icon = 'fa-file-code-o';
                break;

            case 'css':
            case 'js':
            case 'php':
                $icon = 'fa-file';
                break;

            case 'zip':
                $icon = 'fa-file-zip-o';
                break;

            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
                $icon = 'fa-file-image-o';
                break;

            default:
                $icon = 'fa-paperclip';
                break;
        }

        return sprintf('<i class="fa %s"></i>', $icon);
    }
}

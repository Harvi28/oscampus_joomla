<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2021 Joomlashack.com. All rights reserved
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

use Joomla\Registry\Registry as Registry;

use Oscampus\Module\Search;

defined('_JEXEC') or die();

/**
 * @var JApplicationSite $app
 * @var string[]         $attribs
 * @var bool[]           $chrome
 * @var string           $content
 * @var JLanguage        $lang
 * @var object           $module
 * @var Registry         $params
 * @var string           $path
 * @var mixed            $scope
 * @var string           $template
 */

require_once __DIR__ . '/include.php';

$view = new Search($params, $module);
$view->output();

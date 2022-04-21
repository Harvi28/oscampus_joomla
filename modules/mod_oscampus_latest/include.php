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

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();

if (!defined('OSCAMPUS_LOADED')) {
    JLoader::import('include', JPATH_ADMINISTRATOR . '/components/com_oscampus');
}

if (!defined('OSCAMPUS_LOADED')) {
    throw new Exception(Text::_('MOD_OSCAMPUS_LATEST_ERROR_INSTALL_OSCAMPUS'), 500);
}

Oscampus\AutoLoader::register('Oscampus', __DIR__ . '/oscampus');

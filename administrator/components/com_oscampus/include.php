<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2015-2021 Joomlashack.com. All rights reserved
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

use Joomla\CMS\HTML\HTMLHelper;
use Oscampus\AutoLoader;

defined('_JEXEC') or die();

if (!defined('OSCAMPUS_LOADED')) {
    define('OSCAMPUS_LOADED', 1);
    define('OSCAMPUS_ADMIN', JPATH_ADMINISTRATOR . '/components/com_oscampus');
    define('OSCAMPUS_SITE', JPATH_SITE . '/components/com_oscampus');
    define('OSCAMPUS_MEDIA', JPATH_SITE . '/media/com_oscampus');
    define('OSCAMPUS_LIBRARY', OSCAMPUS_ADMIN . '/library');

    // Include vendor dependencies
    require_once OSCAMPUS_ADMIN . '/vendor/autoload.php';

    // Setup autoload libraries
    require_once OSCAMPUS_LIBRARY . '/oscampus/AutoLoader.php';
    AutoLoader::register('Oscampus', OSCAMPUS_LIBRARY . '/oscampus');
    AutoLoader::registerCamelBase('Oscampus', OSCAMPUS_LIBRARY . '/joomla');

    // Any additional helper paths
    OscampusTable::addIncludePath(OSCAMPUS_ADMIN . '/tables');
    HTMLHelper::addIncludePath(OSCAMPUS_LIBRARY . '/html');
    OscampusHelper::loadOptionLanguage('com_oscampus', OSCAMPUS_ADMIN, OSCAMPUS_SITE);

    // Application specific loads
    switch (OscampusFactory::getApplication()->getName()) {
        case 'site':
            OscampusModel::addIncludePath(OSCAMPUS_SITE . '/models');
            break;

        case 'administrator':
            OscampusModel::addIncludePath(OSCAMPUS_ADMIN . '/models');

            // Alledia Framework
            if (!defined('ALLEDIA_FRAMEWORK_LOADED')) {
                $allediaFrameworkPath = JPATH_SITE . '/libraries/allediaframework/include.php';

                if (file_exists($allediaFrameworkPath)) {
                    require_once $allediaFrameworkPath;
                } else {
                    OscampusFactory::getApplication()
                        ->enqueueMessage(JText::_('COM_OSCAMPUS_ERROR_FRAMEWORK_NOT_FOUND'), 'error');
                }
            }

            break;
    }
}

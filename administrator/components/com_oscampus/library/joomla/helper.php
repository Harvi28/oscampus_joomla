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

use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Utility\Utility;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();

abstract class OscampusHelper
{
    /**
     * get component information
     *
     * @return Registry
     */
    public static function getInfo(): Registry
    {
        $info = new Registry();
        $path = OSCAMPUS_ADMIN . '/oscampus.xml';
        if (is_file($path)) {
            $xml = simplexml_load_file($path);

            foreach ($xml->children() as $e) {
                if (!$e->children()) {
                    $info->set($e->getName(), (string)$e);
                }
            }
        }
        return $info;
    }

    /**
     * Render the modules in a position
     *
     * @param string $position
     * @param mixed  $attribs
     *
     * @return string
     */
    public static function renderModule(string $position, $attribs = []): string
    {
        $results = ModuleHelper::getModules($position);
        $content = '';

        if (is_string($attribs)) {
            $attribs = Utility::parseAttributes($attribs);
        }

        $defaults = ['style' => 'xhtml'];
        $attribs  = array_merge($defaults, $attribs);

        ob_start();
        foreach ($results as $result) {
            $content .= ModuleHelper::renderModule($result, $attribs);
        }
        ob_end_clean();

        return $content;
    }

    /**
     * Make sure the appropriate component language files are loaded
     *
     * @param string $option
     * @param string $adminPath
     * @param string $sitePath
     *
     * @return void
     * @throws Exception
     */
    public static function loadOptionLanguage(string $option, string $adminPath, string $sitePath)
    {
        $app = OscampusFactory::getApplication();
        if ($app->input->getCmd('option') != $option) {
            switch (OscampusFactory::getApplication()->getName()) {
                case 'administrator':
                    OscampusFactory::getLanguage()->load($option, $adminPath);
                    break;

                case 'site':
                    OscampusFactory::getLanguage()->load($option, $sitePath);
                    break;
            }
        }
    }

    /**
     * Check a potentially local, relative url and make sure
     * it is properly formed
     *
     * @param ?string $url
     *
     * @return string
     */
    public static function normalizeUrl(?string $url): string
    {
        if ($url) {
            if (!preg_match('#https?://#', $url)) {
                $root = Uri::root(true);

                return rtrim($root, '/') . '/' . ltrim($url, '/');
            }
        }

        return '';
    }

    /**
     * Administrator alerts
     *
     * @return string[]
     */
    public static function getAlerts(): array
    {
        $messages = [];
        if (static::certificateOverrides()) {
            $messages[] = Text::_('COM_OSCAMPUS_WARNING_CERTIFICATE_OVERRIDES');
        }

        return $messages;
    }

    /**
     * @return bool
     */
    public static function certificateOverrides(): bool
    {
        $templateFiles = Folder::files(JPATH_SITE . '/templates', '.', true, true);
        foreach ($templateFiles as $templateFile) {
            if (strpos($templateFile, 'html/com_oscampus/certificate/default.php') !== false) {
                return true;
            }
        }

        return false;
    }
}

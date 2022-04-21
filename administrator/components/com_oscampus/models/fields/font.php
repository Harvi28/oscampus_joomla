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

if (!defined('OSCAMPUS_LOADED')) {
    $path = JPATH_ADMINISTRATOR . '/components/com_oscampus/include.php';
    if (is_file($path)) {
        require_once $path;
    }
}
JFormHelper::loadFieldClass('List');

class OscampusFormFieldFont extends JFormFieldList
{
    protected function getOptions()
    {
        $options = [];
        if (defined('OSCAMPUS_LOADED')) {
            $fonts    = OscampusFactory::getContainer()->theme->fontFamilies;
            $language = OscampusFactory::getLanguage();

            foreach ($fonts as $key => $font) {
                $text      = 'COM_OSCAMPUS_OPTION_FONT_' . $key;
                $options[] = JHtml::_(
                    'select.option',
                    $key,
                    $language->hasKey($text) ? JText::_($text) : $font['font']
                );
            }
        }

        return array_merge(parent::getOptions(), $options);
    }
}

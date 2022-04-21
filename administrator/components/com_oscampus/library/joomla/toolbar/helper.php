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

defined('_JEXEC') or die();

abstract class OscampusToolbarHelper extends JToolbarHelper
{
    /**
     * Add a custom button with standard OSCampus styles
     *
     * @param string $task
     * @param string $icon
     * @param string $iconOver
     * @param string $alt
     * @param bool   $listSelect
     * @param string $iconColor
     *
     * @return void
     */
    public static function custom(
        $task = '',
        $icon = '',
        $iconOver = '',
        $alt = '',
        $listSelect = true,
        $iconColor = '#333'
    ) {
        $img = JHtml::_('image', "com_oscampus/icon-32-{$icon}.png", null, null, true, true);
        if ($img) {
            $doc = OscampusFactory::getDocument();

            $doc->addStyleDeclaration(".icon-{$icon}:before { color: {$iconColor}; }");
        }
        parent::custom($task, $icon, $iconOver, $alt, $listSelect);
    }

    /**
     * Add the batch button
     *
     * @param string $title
     * @param string $layout
     *
     * @return void
     */
    public static function batch($title = '', $layout = 'joomla.toolbar.batch')
    {
        // Instantiate a new JLayoutFile instance and render the batch button
        $layout = new JLayoutFile($layout);

        $title = $title ?: JText::_('JTOOLBAR_BATCH');

        $bar  = JToolbar::getInstance('toolbar');
        $html = $layout->render(array('title' => $title));
        $bar->appendButton('Custom', $html, 'batch');
    }
}

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

abstract class OscampusViewAdminTools extends OscampusViewAdmin
{
    /**
     * @param string $sub
     * @param string $icon
     *
     * @return void
     * @throws Exception
     */
    protected function setTitle($sub = null, $icon = 'oscampus')
    {
        $name = strtoupper($this->getName());

        parent::setTitle('COM_OSCAMPUS_SUBMENU_' . $name, $icon);
    }

    /**
     * @inheritDoc
     */
    protected function setSubmenu($text = 'COM_OSCAMPUS_SUBMENU_ADMIN_SPACER')
    {
        $app  = OscampusFactory::getApplication();
        $hide = $app->input->getBool('hidemainmenu', false);

        $this->setVariable('show_sidebar', !$hide);

        JHtmlSidebar::addEntry(Text::_('JTOOLBAR_BACK'), 'index.php?option=com_oscampus');

        $spacer = sprintf(
            '<span class="osc-sidebar-spacer">%s</span>',
            Text::_($text)
        );
        JHtmlSidebar::addEntry($spacer);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function addSubmenuItem($name, $view, $active)
    {
        $layout = $view;
        $view   = $this->getName();

        $link = sprintf('index.php?option=com_oscampus&view=%s&layout=%s', $view, $layout);

        JHtmlSidebar::addEntry(Text::_($name), $link, $active);
    }
}

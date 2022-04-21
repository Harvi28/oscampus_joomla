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

use Alledia\Framework\Joomla\Extension\Licensed;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();

abstract class OscampusViewAdmin extends OscampusViewTwig
{
    /**
     * @var string
     */
    protected $option = null;

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function setup()
    {
        parent::setup();

        $this->option = OscampusFactory::getApplication()->input->getCmd('option', 'com_oscampus');

        $this->setTitle();
        $this->setToolbar();
        $this->setSubmenu();

        $this->setVariable('params', OscampusFactory::getContainer()->params);
    }

    /**
     * Default admin screen title
     *
     * @param ?string $sub
     * @param ?string $icon
     *
     * @return void
     */
    protected function setTitle($sub = null, $icon = 'oscampus')
    {
        $img = HTMLHelper::_('image', "com_oscampus/icon-48-{$icon}.png", null, null, true, true);
        if ($img) {
            $doc = OscampusFactory::getDocument();
            $doc->addStyleDeclaration(".icon-{$icon}:before { content: url({$img}); }");
        }

        $title = Text::_('COM_OSCAMPUS');
        if ($sub) {
            $title .= ': ' . Text::_($sub);
        }

        OscampusToolbarHelper::title($title, $icon);
    }

    /**
     * Set the admin screen toolbar buttons
     *
     * @return void
     */
    protected function setToolbar()
    {
        $user = OscampusFactory::getUser();

        $admin = $user->authorise('core.admin', 'com_oscampus');
        if ($admin) {
            OscampusToolbarHelper::preferences('com_oscampus');
        }
    }

    /**
     * Add a new submenu
     *
     * @param string $name   The submenu's text
     * @param string $view   The submenu's view
     * @param bool   $active True if the item is active, false otherwise.
     *
     * @return void
     */
    protected function addSubmenuItem($name, $view, $active)
    {
        $link = 'index.php?option=com_oscampus&view=' . $view;

        JHtmlSidebar::addEntry(Text::_($name), $link, $active);
    }

    /**
     * Set the submenu items
     *
     * @return void
     * @throws Exception
     */
    protected function setSubmenu()
    {
        $app    = OscampusFactory::getApplication();
        $user   = OscampusFactory::getUser();
        $params = OscampusComponentHelper::getParams();

        $hide = $app->input->getBool('hidemainmenu', false);
        if (!$hide) {
            $this->addSubmenuItem('COM_OSCAMPUS_SUBMENU_COURSES', 'courses', $this->_name == 'courses');
            $this->addSubmenuItem('COM_OSCAMPUS_LESSONS', 'lessons', $this->_name == 'lessons');
            $this->addSubmenuItem('COM_OSCAMPUS_SUBMENU_PATHWAYS', 'pathways', $this->_name == 'pathways');
            $this->addSubmenuItem('COM_OSCAMPUS_SUBMENU_TAGS', 'tags', $this->_name == 'tags');
            $this->addSubmenuItem('COM_OSCAMPUS_SUBMENU_TEACHERS', 'teachers', $this->_name == 'teachers');

            if ($params->get('certificates.enabled', 1)) {
                $this->addSubmenuItem(
                    'COM_OSCAMPUS_SUBMENU_CERTIFICATES',
                    'certificates',
                    $this->_name == 'certificates'
                );
            }

            if ($user->authorise('core.tools', 'com_oscampus')) {
                $spacer = sprintf(
                    '<span class="osc-sidebar-spacer">%s</span>',
                    Text::_('COM_OSCAMPUS_SUBMENU_ADMIN_SPACER')
                );

                JHtmlSidebar::addEntry($spacer);
                $this->addSubmenuItem('COM_OSCAMPUS_SUBMENU_UTILITIES', 'utilities', $this->_name == 'utilities');
                $this->addSubmenuItem('COM_OSCAMPUS_SUBMENU_STATISTICS', 'statistics', $this->_name == 'statistics');
            }
        }

        $this->setVariable('show_sidebar', !$hide);
    }

    /**
     * @inheritDoc
     */
    protected function displayFooter()
    {
        $extension = new Licensed('OSCampus', 'component');
        $result    = $extension->getFooterMarkup();

        return parent::displayFooter() . $result;
    }
}

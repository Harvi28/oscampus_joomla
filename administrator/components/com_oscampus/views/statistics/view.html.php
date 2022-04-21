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

defined('_JEXEC') or die();

class OscampusViewStatistics extends OscampusViewAdminTools
{
    /**
     * @inheritDoc
     */
    protected function setup()
    {
        $report = $this->getLayout();
        if ($report == 'default') {
            $report = 'lessons.popular';
        }

        $this->setLayout($report);
        $this->setVariable('report', $report);

        parent::setup();
    }

    /**
     * @inheritDoc
     */
    protected function setSubmenu($text = 'COM_OSCAMPUS_SUBMENU_STATISTICS')
    {
        parent::setSubmenu($text);

        $targetLayout = $this->setLayout('default');

        $this->addSubmenuItem(
            'COM_OSCAMPUS_STATISTICS_SELECT_TOP_LESSONS',
            'lessons.popular',
            $targetLayout == 'lessons.popular'
        );
        $this->addSubmenuItem(
            'COM_OSCAMPUS_STATISTICS_SELECT_VIEWED',
            'lessons.viewed',
            $targetLayout == 'lessons.viewed'
        );
        $this->addSubmenuItem('COM_OSCAMPUS_SUBMENU_STATISTICS_COURSES', 'courses', $targetLayout == 'courses');
        $this->addSubmenuItem('COM_OSCAMPUS_SUBMENU_STATISTICS_STUDENTS', 'students', $targetLayout == 'students');
    }
}

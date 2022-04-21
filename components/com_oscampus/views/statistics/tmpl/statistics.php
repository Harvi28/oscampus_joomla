<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2017-2021 Joomlashack.com. All rights reserved
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

$selected = OscampusFactory::getApplication()->input->getCmd('fullname') ?: 'statistics.welcome';

$reports = array(
    JHtml::_('select.option', '', JText::_('COM_OSCAMPUS_STATISTICS_SELECT_WELCOME')),
    JHtml::_('select.option', 'lessons.popular', JText::_('COM_OSCAMPUS_STATISTICS_SELECT_TOP_LESSONS')),
    JHtml::_('select.option', 'lessons.viewed', JText::_('COM_OSCAMPUS_STATISTICS_SELECT_VIEWED'))
);
?>
    <div class="clearfix"></div>
    <div class="osc-section">
        <div class="block3">
            <?php
            echo JHtml::_(
                'select.genericlist',
                $reports,
                'fullname',
                'onchange="jQuery.Oscampus.statistics.load(\'statistics\');"',
                'value',
                'text',
                $selected
            );
            ?>
        </div>
    </div>
<?php
list($layout, $tpl) = explode('.', $selected);

echo $this->loadDefaultTemplate($tpl, $layout);

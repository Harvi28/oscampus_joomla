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

$days = $this->model->getState('filter.days');

?>
<h3><?php echo JText::sprintf('COM_OSCAMPUS_STATISTICS_LESSONS_POPULAR_HEADING', $days); ?></h3>
<div class="osc-table">
    <div class="osc-section osc-row-heading osc-hide-tablet">
        <div class="block2 osc-text-center-desktop"><?php echo JText::_('COM_OSCAMPUS_VIEWS'); ?></div>
        <div class="block4"><i class="fa fa-play"></i> <?php echo JText::_('COM_OSCAMPUS_LESSON'); ?></div>
        <div class="block3"><i class="fa fa-bars"></i> <?php echo JText::_('COM_OSCAMPUS_COURSE'); ?></div>
        <div class="block3"><?php echo JText::_('COM_OSCAMPUS_LAST_VISIT'); ?></div>
    </div>
    <?php
    foreach ($this->items as $i => $lesson) :
        ?>
        <div class="osc-section <?php echo ($i % 2) ? 'osc-row-two' : 'osc-row-one'; ?>">
            <div class="block2 osc-text-center-desktop">
                <?php echo $lesson->visits; ?>
                <span class="osc-show-inline-mobile"><?php echo JText::_('COM_OSCAMPUS_VIEWS'); ?></span>
            </div>
            <div class="block4"><?php echo $lesson->lesson_title; ?></div>
            <div class="block3"><?php echo $lesson->course_title; ?></div>
            <div class="block3"><?php echo $lesson->latest_visit; ?></div>
        </div>
        <?php
    endforeach;
    ?>
</div>


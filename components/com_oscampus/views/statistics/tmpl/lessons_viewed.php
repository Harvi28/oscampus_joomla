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

$months = $this->model->getState('filter.months');

?>
<h3><?php echo JText::sprintf('COM_OSCAMPUS_STATISTICS_LESSONS_VIEW_HEADING', $months); ?></h3>
<div class="osc-table">
    <div class="osc-section osc-row-heading osc-hide-tablet">
        <div class="block2"><?php echo JText::_('COM_OSCAMPUS_WEEK'); ?></div>
        <div class="block2 osc-text-center-desktop"><?php echo JText::_('COM_OSCAMPUS_VIDEOS'); ?></div>
        <div class="block1 osc-text-center-desktop"><?php echo JText::_('COM_OSCAMPUS_QUIZZES'); ?></div>
        <div class="block1 osc-text-center-desktop"><?php echo JText::_('COM_OSCAMPUS_TEXT'); ?></div>
        <div class="block1 osc-text-center-desktop"><?php echo JText::_('COM_OSCAMPUS_OTHER'); ?></div>
        <div class="block1 osc-text-center-desktop"><?php echo JText::_('COM_OSCAMPUS_TOTAL'); ?></div>
        <div class="block1 osc-text-center-desktop"><?php echo JText::_('COM_OSCAMPUS_USERS'); ?></div>
    </div>
    <?php
    foreach ($this->items as $i => $views) :
        $otherTotal = $views->total - $views->videos - $views->quizzes - $views->text;
        ?>
        <div class="osc-section <?php echo ($i % 2) ? 'osc-row-two' : 'osc-row-one'; ?>">
            <div class="block2">
                <?php echo $views->weekstart; ?>
            </div>
            <div class="block2 osc-text-center-desktop">
                <?php echo number_format($views->videos); ?>
                <span class="osc-show-inline-mobile"><?php echo JText::_('COM_OSCAMPUS_VIDEOS'); ?></span>

            </div>
            <div class="block1 osc-text-center-desktop">
                <?php echo number_format($views->quizzes); ?>
                <span class="osc-show-inline-mobile"><?php echo JText::_('COM_OSCAMPUS_QUIZZES'); ?></span>
            </div>
            <div class="block1 osc-text-center-desktop">
                <?php echo number_format($views->text); ?>
                <span class="osc-show-inline-mobile"><?php echo JText::_('COM_OSCAMPUS_TEXT'); ?></span>
            </div>
            <div class="block1 osc-text-center-desktop">
                <?php echo number_format($otherTotal); ?>
                <span class="osc-show-inline-mobile"><?php echo JText::_('COM_OSCAMPUS_OTHER'); ?></span>
            </div>
            <div class="block1 osc-text-center-desktop">
                <?php echo number_format($views->total); ?>
                <span class="osc-show-inline-mobile"><?php echo JText::_('COM_OSCAMPUS_TOTAL'); ?></span>
            </div>
            <div class="block1 osc-text-center-desktop">
                <?php echo number_format($views->users); ?>
                <span class="osc-show-inline-mobile"><?php echo JText::_('COM_OSCAMPUS_USERS'); ?></span>
            </div>
        </div>
        <?php
    endforeach;
    ?>
</div>


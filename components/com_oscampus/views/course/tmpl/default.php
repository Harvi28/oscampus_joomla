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
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();

HTMLHelper::_('osc.tabs', '.osc-course-tabs div');
?>
<div class="<?php echo $this->getPageClass('osc-container oscampus-course'); ?>" id="oscampus">
    <div class="page-header">
        <h1><?php echo $this->course->title; ?></h1>
    </div>

    <div class="osc-course-details">
        <div class="osc-section">
            <div class="block4 osc-course-image">
                <?php echo HTMLHelper::_('image', $this->course->image, $this->course->title); ?>
            </div>
            <div class="block8 osc-course-description">
                <div class="osc-course-info">
                    <?php
                    if ($this->teacher) :
                        ?>
                        <strong><?php echo Text::_('COM_OSCAMPUS_COURSE_LABEL_TEACHER'); ?></strong>
                        <?php echo $this->teacher->name; ?>
                        <br>
                    <?php endif; ?>

                    <strong><?php echo Text::_('COM_OSCAMPUS_COURSE_LABEL_RELEASED'); ?></strong>
                    <?php echo date('F j, Y', strtotime($this->course->publish_up)); ?>
                    <br>

                    <?php
                    if ($this->course->length) :
                        ?>
                        <strong><?php echo Text::_('COM_OSCAMPUS_COURSE_LABEL_LENGTH'); ?></strong>
                        <?php echo HTMLHelper::_('osc.duration', $this->course->length); ?>
                        <br>
                    <?php endif; ?>

                    <strong><?php echo Text::_('COM_OSCAMPUS_COURSE_LABEL_LEVEL'); ?></strong>
                    <?php echo Text::_('COM_OSCAMPUS_DIFFICULTY_' . $this->course->difficulty); ?>
                    <br>

                    <strong><?php echo Text::_('COM_OSCAMPUS_COURSE_LABEL_CERTIFICATE'); ?></strong>
                    <?php echo HTMLHelper::_('osc.course.requirements', $this->lessons); ?>

                    <?php if ($this->course->tags) : ?>
                        <br>
                        <strong><?php echo Text::_('COM_OSCAMPUS_COURSE_LABEL_TAGS'); ?></strong>
                        <?php
                        echo join(', ', $this->course->tags);
                    endif;
                    ?>
                </div>
            </div>
        </div>
    </div>
    <!-- .osc-section -->

    <div class="osc-section osc-course-tabs">
        <div data-content="#content-content" class="block2">
            <?php echo Text::_('COM_OSCAMPUS_COURSE_TAB_TOC'); ?>
        </div>
        <div data-content="#content-description" class="block2 osc-tab-disabled">
            <?php echo Text::_('COM_OSCAMPUS_COURSE_TAB_DESCRIPTION'); ?>
        </div>
        <?php
        if ($this->files) :
            ?>
            <div data-content="#content-files" class="block2 osc-tab-disabled">
                <?php echo Text::_('COM_OSCAMPUS_COURSE_TAB_EXERCISE_FILES'); ?>
            </div>
        <?php endif;

        if ($this->teacher) :
            ?>
            <div data-content="#content-teacher" class="block2 osc-tab-disabled">
                <?php echo Text::_('COM_OSCAMPUS_COURSE_TAB_TEACHER'); ?>
            </div>
        <?php endif; ?>
    </div>
    <!-- .osc-course-tabs -->

    <?php
    echo $this->loadTemplate('content');
    echo $this->loadTemplate('description');

    if ($this->files) :
        echo $this->loadTemplate('files');
    endif;

    if ($this->teacher) :
        echo $this->loadTemplate('teacher');
    endif;
    ?>
</div>

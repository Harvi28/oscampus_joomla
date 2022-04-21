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

/**
 * @var JLayoutFile $this
 * @var object      $displayData
 * @var string      $layoutOutput
 * @var string      $path
 */

$item    = $displayData;
$link    = JRoute::_(JHtml::_('osc.course.link', $item, null, null, true));
$image   = JHtml::_('image', $item->image, $item->title);
$options = $this->getOptions();

if ($options->get('image', true)) :
    ?>
    <div class="osc-section osc-course-item">
        <div class="block4 osc-course-image">
            <?php echo JHtml::_('link', $link, $image); ?>
        </div>
        <div class="block8 osc-course-description">
            <h2><?php echo JHtml::_('link', $link, $item->title); ?></h2>
            <?php echo $item->introtext ?: $item->description; ?>
        </div>
    </div>
    <?php
endif;
?>
<!-- .osc-section -->

<div class="osc-section osc-course-list">
    <div class="block9">
        <?php
        if ($options->get('tags', true) && $item->tags) :
            ?>
            <span class="osc-label">
                <i class="fa fa-tag"></i>
                <?php echo $item->tags; ?>
            </span>
            <?php
        endif;

        if ($options->get('lessonCount', true)) :
            ?>
            <span class="osc-label">
                <i class="fa fa-list"></i>
                <?php echo JText::plural('COM_OSCAMPUS_COURSE_LESSON_COUNT', $item->lesson_count); ?>
            </span>
            <?php
        endif;

        if ($item->length && $options->get('length', true)) :
            ?>
            <span class="osc-label">
                <i class="fa fa-clock-o"></i>
                <?php echo JHtml::_('osc.duration', $item->length) ?>
            </span>
            <?php
        endif;

        if ($options->get('released', false)) :
            ?>
            <span class="osc-label">
                <i class="fa fa-calendar"></i>
                <?php echo date('F j, Y', strtotime($item->publish_up)); ?>
          </span>
            <?php
        endif;

        if ($options->get('teacher', false)) :
            ?>
            <span class="osc-label">
                <i class="fa fa-user"></i>
                <?php echo $item->teacher; ?>
            </span>
            <?php
        endif;

        if ($options->get('difficulty', true)) :
            ?>
            <span class="osc-label">
                <i class="fa fa-signal"></i>
                <?php echo JHtml::_('osc.course.difficulty', $item->difficulty); ?>
            </span>
            <?php
        endif;

        ?>
    </div>
    <div class="block3 osc-course-start">
        <?php echo JHtml::_('osc.course.startbutton', $item); ?>
    </div>
</div>
<!-- .osc-section -->

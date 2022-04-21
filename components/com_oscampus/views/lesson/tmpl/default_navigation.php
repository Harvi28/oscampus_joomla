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

$courseId = $this->model->getState('course.id');
if ($this->lesson->previous->id) :
    $previousLink = JHtml::_('osc.lesson.link', $this->lesson->previous, null, null, true);
endif;
if ($this->lesson->next->id) :
    $nextLink = JHtml::_('osc.lesson.link', $this->lesson->next, null, null, true);
endif;

JHtml::_('osc.lesson.navigation', $this->lesson);

?>
<div class="osc-btn-group osc-lesson-navigation" id="course-navigation">
    <?php
    echo JHtml::_(
        'link',
        JHtml::_('osc.link.course', $courseId, null, null, true),
        sprintf(
            '<i class="fa fa-bars"></i> <span class="osc-hide-tablet">%s</span>',
            JText::_('COM_OSCAMPUS_HOME')
        ),
        'class="osc-btn"'
    );

    if (!empty($previousLink)) :
        echo JHtml::_(
            'link',
            $previousLink,
            sprintf(
                '<i class="fa fa-chevron-left"></i> <span class="osc-hide-tablet">%s</span>',
                JText::_('COM_OSCAMPUS_PREVIOUS')
            ),
            'class="osc-btn" id="prevbut"'
        );
    endif;

    if (!empty($nextLink)) :
        echo JHtml::_(
            'link',
            $nextLink,
            sprintf(
                '<span class="osc-hide-tablet">%s</span> <i class="fa fa-chevron-right"></i>',
                JText::_('COM_OSCAMPUS_NEXT')
            ),
            'class="osc-btn" id="nextbut"'
        );
    endif;

    echo $this->lesson->getDownloadButton('class="osc-btn"');
    ?>
</div>

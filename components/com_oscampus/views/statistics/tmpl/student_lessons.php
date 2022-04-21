<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2018-2021 Joomlashack.com. All rights reserved
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

$container = OscampusFactory::getContainer();

$student     = OscampusFactory::getUser($this->model->getState('user.id'));
$course      = $container->course->get($this->model->getState('course.id'));
$certificate = $container->certificate->getCertificates($student->id, $course->id);
$showAll     = $this->getParams()->get('student.allLessons', true);
?>
<button id="course-list" class="osc-btn">
    <i class="fa fa-chevron-left"></i>
    <?php echo Text::_('COM_OSCAMPUS_LIST_BACK'); ?>
</button>
<div class="page-header">
    <h1><?php echo sprintf('%s (%s)', $student->name, $student->username); ?></h1>
    <h2><?php echo $course->title; ?></h2>
    <?php
    if ($certificate) :
        ?>
        <div class="block12">
            <?php echo HTMLHelper::_('osc.certificate.icon', $certificate); ?>
            <?php echo Text::sprintf('COM_OSCAMPUS_CERTIFICATE_EARNED_LABEL', $certificate->date_earned); ?>
        </div>
    <?php
    endif;
    ?>
</div>

<div class="osc-table">
    <div class="osc-section osc-row-heading osc-hide-tablet">
        <div class="block4">
            <?php echo Text::_('COM_OSCAMPUS_LESSON'); ?>
        </div>
        <div class="block4">
            <?php echo Text::_('COM_OSCAMPUS_FIRST_VISIT'); ?>
            <br/>
            <?php echo Text::_('COM_OSCAMPUS_LAST_VISIT'); ?>
        </div>
        <div class="block2">
            <?php echo Text::_('COM_OSCAMPUS_VISITS'); ?>
        </div>
        <div class="block2">
            <?php echo Text::_('COM_OSCAMPUS_STATISTICS_COMPLETION'); ?>
        </div>
    </div>
    <?php
    foreach ($this->items as $module) :
        ?>
        <div class="osc-section osc-row-heading">
            <div class="block12 p-left-xx"><i class="fa fa-align-justify"></i>
                <?php echo $module->title; ?>
            </div>
        </div>
        <?php
        $row = 0;
        foreach ($module->lessons as $lesson) :
            if ($showAll || $lesson->visits > 0) :
                ?>
                <div class="<?php echo 'osc-section ' . ($row++ % 2 ? 'osc-row-two' : 'osc-row-one'); ?>">
                    <div class="block4">
                        <?php echo $lesson->lesson_title; ?>
                        <div>(<?php echo HTMLHelper::_('osc.lesson.type', $lesson->type); ?>)</div>
                    </div>
                    <div class="block4">
                        <?php echo $lesson->first_visit; ?>
                        <br/>
                        <?php echo $lesson->last_visit; ?>
                    </div>
                    <div class="block2">
                        <?php echo $lesson->visits ? number_format($lesson->visits) : ''; ?>
                    </div>
                    <div class="block2">
                        <?php echo $lesson->visits
                            ? Text::sprintf('COM_OSCAMPUS_SCORE', number_format($lesson->score))
                            : ''; ?>
                    </div>
                </div>
            <?php endif;
        endforeach;
    endforeach;
    ?>
</div>

<input type="hidden" name="user_id" value="<?php echo $student->id; ?>"/>
<input type="hidden" name="course_id" value="<?php echo $course->id; ?>"/>
<script>
    ;(function($) {
        $.Oscampus.statistics.setPagination('student.lessons');

        $('#course-list').on('click', function(evt) {
            evt.preventDefault();
            evt.stopPropagation();
            $.Oscampus.statistics.back();
        });

        $(".hasTooltip").tooltip({"html": true, "container": "body"})
    })(jQuery);
</script>

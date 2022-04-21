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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die();

$courseId = $this->model->getState('course.id');

?>
<button id="course-list" class="osc-btn">
    <i class="fa fa-chevron-left"></i>
    <?php echo Text::_('COM_OSCAMPUS_LIST_BACK'); ?>
</button>
<div class="page-header">
    <h1><?php echo $this->items[0]->title; ?></h1>
</div>

<div class="osc-section osc-pagination">
    <?php echo $this->pagination->getPaginationLinks('listing.pagination', ['showLimitBox' => $this->showLimitBox]); ?>
</div>
<div class="clearfix"></div>

<div class="osc-table">
    <div class="osc-section osc-row-heading osc-hide-tablet">
        <div class="block3">
            <i class="fa fa-user"></i>
            <?php echo Text::_('COM_OSCAMPUS_COURSE_STUDENTS_1'); ?>
        </div>
        <div class="block4">
            <?php echo Text::_('COM_OSCAMPUS_FIRST_VISIT'); ?> / <?php echo Text::_('COM_OSCAMPUS_LAST_VISIT'); ?>
        </div>
        <div class="block3">
            <i class="fa fa-battery-3"></i>
            <?php echo Text::_('COM_OSCAMPUS_PROGRESS'); ?>
        </div>
        <div class="block1 osc-text-center-desktop">
            <?php echo Text::_('COM_OSCAMPUS_LESSONS'); ?>
        </div>
        <div class="block1 osc-text-center-desktop">
            <i class="fa fa-star-o"></i>
        </div>
    </div>

    <?php
    foreach ($this->items as $item) :
        $user = OscampusFactory::getUser($item->users_id);
        $progress = $item->progress . '%';
        $attribs = [
            'style' => sprintf('width: %s;', $progress),
        ];

        if ($item->progress < 30) :
            $attribs['class'] = 'hasTooltip';
            $attribs['title'] = HTMLHelper::_('tooltipText', '', $progress);
        elseif ($item->progress == 100) :
            $attribs['class'] = 'osc-progress-bar-completed';
        endif;

        ?>
        <div class="osc-section osc-row-one">
            <div class="block3">
                <?php
                if ($user->id) :
                    echo HTMLHelper::_(
                        'link',
                        '#',
                        sprintf('%s (%s)', $user->name, $user->username),
                        sprintf('data-user="%s"', $user->id)
                    );
                else :
                    echo Text::sprintf('COM_OSCAMPUS_DELETED_USER', $item->users_id);
                endif;
                ?>
            </div>
            <div class="block4">
                <?php echo $item->first_visit; ?>
                <br/>
                <?php echo $item->last_visit; ?>
            </div>
            <div class="block3">
                <span class="osc-progress-bar">
                    <span <?php echo ArrayHelper::toString($attribs); ?>>
                        <span>
                            <?php echo ($item->progress >= 30) ? $progress : '' ?>
                        </span>
                    </span>
                </span>
            </div>
            <div class="block1 osc-text-center-desktop">
                <?php echo number_format($item->lessons_completed) . '/' . number_format($item->lesson_count); ?>
            </div>
            <div class="block1 osc-text-center-desktop">
                <?php echo HTMLHelper::_('osc.certificate.icon', $item->certificates_id, $item->date_earned); ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="osc-section osc-pagination">
    <?php echo $this->pagination->getPaginationLinks('listing.pagination'); ?>
</div>
<div class="clearfix"></div>

<input type="hidden" name="course_id" value="<?php echo $courseId; ?>"/>
<input type="hidden" name="user_id" value="" id="user_id"/>
<script>
    ;(function($) {
        $.Oscampus.statistics.setPagination('course');

        $('[data-user]').on('click', function(evt) {
            evt.preventDefault();
            evt.stopPropagation();

            $('#user_id').val($(this).attr('data-user'));

            $.Oscampus.statistics.load('student.lessons');
        });

        $('#course-list').on('click', function(evt) {
            evt.preventDefault();
            evt.stopPropagation();
            $.Oscampus.statistics.load('courses');
        });

        $(".hasTooltip").tooltip({"html": true, "container": "body"})
    })(jQuery);
</script>


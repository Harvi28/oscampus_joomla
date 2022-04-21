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

?>
<div class="osc-section osc-pagination">
    <?php echo $this->pagination->getPaginationLinks('listing.pagination', array('showLimitBox' => $this->showLimitBox)); ?>
</div>
<div class="clearfix"></div>

<div class="osc-table">
    <div class="osc-section osc-row-heading osc-hide-tablet">
        <div class="block2"></div>
        <div class="block4">
            <i class="fa fa-bars"></i>
            <?php echo JText::_('COM_OSCAMPUS_COURSE_TITLE'); ?>
        </div>
        <div class="block2">
            <?php echo JText::_('COM_OSCAMPUS_COURSE_RELEASE_DATE'); ?>
        </div>
        <div class="block2 osc-text-center-desktop">
            <?php echo JText::_('COM_OSCAMPUS_COURSE_STUDENTS'); ?>
        </div>
        <div class="block2">
            <?php echo JText::_('COM_OSCAMPUS_CERTIFICATES'); ?>
        </div>

    </div>
    <?php foreach ($this->items as $idx => $item) : ?>
        <div class="osc-section <?php echo ($idx % 2) ? 'osc-row-two' : 'osc-row-one'; ?>">
            <div class="block2">
                <?php
                echo sprintf(
                    '<i class="fa osc-m-right %s"></i> %s',
                    $item->published ? 'fa-check-circle' : 'fa-times-circle',
                    $item->access
                );
                ?>
            </div>
            <div class="block4">
                <?php
                echo JHtml::_(
                    'link',
                    '#',
                    $item->title,
                    sprintf('data-course="%s"', $item->id)
                );
                ?>
            </div>
            <div class="block2">
                <?php echo $item->publish_up; ?>
            </div>
            <div class="block2 osc-text-center-desktop">
                <?php echo number_format($item->students); ?>
                <span class="osc-show-inline-mobile"><?php echo JText::_('COM_OSCAMPUS_COURSE_STUDENTS'); ?></span>
            </div>
            <div class="block2 osc-text-center-desktop">
                <?php echo number_format($item->certificates); ?>
                <span class="osc-show-inline-mobile"><?php echo JText::_('COM_OSCAMPUS_CERTIFICATES'); ?></span>
            </div>

        </div>
    <?php endforeach; ?>
</div>
<div class="osc-section osc-pagination">
    <?php echo $this->pagination->getPaginationLinks('listing.pagination'); ?>
</div>
<div class="clearfix"></div>

<input type="hidden" name="course_id" value="" id="course_id"/>
<script>
    ;(function($) {
        $.Oscampus.statistics.setPagination('courses');

        $('[data-course]').on('click', function(evt) {
            evt.preventDefault();
            evt.stopPropagation();

            $('#course_id').val($(this).attr('data-course'));
            $.Oscampus.statistics.load('course');
        })
    })(jQuery);

</script>

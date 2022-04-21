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

defined('_JEXEC') or die();

?>
<div class="osc-section osc-pagination">
    <?php echo $this->pagination->getPaginationLinks('listing.pagination', ['showLimitBox' => $this->showLimitBox]); ?>
</div>
<div class="clearfix"></div>

<div class="osc-table">
    <div class="osc-section osc-row-heading osc-hide-tablet">
        <div class="block4">
            <i class="fa fa-user"></i>
            <?php echo Text::_('COM_OSCAMPUS_COURSE_STUDENTS_1'); ?>
        </div>
        <div class="block2">
            <?php echo Text::_('COM_OSCAMPUS_LAST_LOGIN'); ?>
        </div>
        <div class="block2">
            <?php echo Text::_('COM_OSCAMPUS_LAST_VISIT'); ?>
        </div>
        <div class="block2 osc-text-center-desktop">
            <i class="fa fa-bars"></i>
            <?php echo Text::_('COM_OSCAMPUS_COURSES'); ?>
        </div>
        <div class="block1 osc-text-center-desktop">
            <?php echo Text::_('COM_OSCAMPUS_LESSONS'); ?>
        </div>
        <div class="block1 osc-text-center-desktop">
            <i class="fa fa-star-o"></i>
        </div>
    </div>
    <?php
    foreach ($this->items as $idx => $item) :
        $user = OscampusFactory::getUser($item->users_id);
        ?>
        <div class="osc-section osc-row-one">
            <div class="block4">
                <?php
                if ($user->id) :
                    echo HTMLHelper::_(
                        'link',
                        '#',
                        sprintf('%s (%s)', $user->name, $user->username),
                        sprintf('data-user="%s"', $item->users_id)
                    );
                else :
                    echo Text::sprintf('COM_OSCAMPUS_DELETED_USER', $item->users_id);
                endif;
                ?>
            </div>
            <div class="block2">
                <?php echo $user->lastvisitDate; ?>
            </div>
            <div class="block2">
                <?php echo $item->last_visit; ?>
            </div>
            <div class="block2 osc-text-center-desktop">
                <?php echo number_format($item->course_count); ?>
                <span class="osc-show-inline-mobile"><?php echo Text::_('COM_OSCAMPUS_COURSES'); ?></span>
            </div>
            <div class="block1 osc-text-center-desktop">
                <?php echo number_format($item->lessons_completed); ?>
            </div>
            <div class="block1 osc-text-center-desktop">
                <?php echo number_format($item->certificate_count); ?>
                <span class="osc-show-inline-mobile"><?php echo Text::_('COM_OSCAMPUS_CERTIFICATES'); ?></span>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<div class="osc-section osc-pagination">
    <?php echo $this->pagination->getPaginationLinks('listing.pagination'); ?>
</div>
<div class="clearfix"></div>

<input type="hidden" name="user_id" value="" id="user_id"/>
<script>
    ;(function($) {
        $.Oscampus.statistics.setPagination('students');

        $('[data-user]').on('click', function(evt) {
            evt.preventDefault();
            evt.stopPropagation();

            $('#user_id').val($(this).attr('data-user'));
            $.Oscampus.statistics.load('student');
        })
    })(jQuery);
</script>


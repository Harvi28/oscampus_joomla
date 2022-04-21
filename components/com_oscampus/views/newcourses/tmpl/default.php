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

/** @var OscampusViewNewcourses $this */
?>
<div class="<?php echo $this->getPageClass('osc-container oscampus-newcourses'); ?>" id="oscampus">
    <?php
    if ($heading = $this->getHeading('COM_OSCAMPUS_HEADING_NEWCOURSES')) :
        ?>
        <div class="page-header">
            <h1><?php echo $heading; ?></h1>
        </div>
        <?php
    endif;
    ?>

    <?php
    if ($this->items) :
        foreach ($this->items as $item) :
            echo OscampusLayoutHelper::render('listing.course', $item, null, array('length' => false, 'released' => true));
        endforeach;

    else :
        ?>
        <div class="osc-alert-notify">
            <i class="fa fa-info-circle"></i>
            <?php
            $cutoff = $this->getState('filter.cutoff');
            echo JText::sprintf('COM_OSCAMPUS_NO_NEW_COURSES', $cutoff->format('F j, Y'));
            ?>
        </div>
        <?php
    endif;
    ?>
</div>


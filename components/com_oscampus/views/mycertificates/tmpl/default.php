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

/** @var OscampusViewMycertificates $this */

?>
<div class="<?php echo $this->getPageClass('osc-container oscampus-mycertificates'); ?>" id="oscampus">
    <?php
    if ($heading = $this->getHeading('COM_OSCAMPUS_HEADING_MYCERTIFICATES')) :
        ?>
        <div class="page-header">
            <h1><?php echo $heading; ?></h1>
        </div>
        <?php
    endif;
    ?>

    <?php
    if ($this->items) :
        ?>
        <div class="osc-table">
            <div class="osc-section osc-row-heading osc-hide-tablet">
                <div class="block6">
                    <i class="fa fa-bars"></i> <?php echo JText::_('COM_OSCAMPUS_COURSE_TITLE'); ?>
                </div>
                <div class="block3">
                    <i class="fa fa-calendar"></i> <?php echo JText::_('COM_OSCAMPUS_DATE_EARNED'); ?>
                </div>
                <div class="block3">
                    <i class="fa fa-certificate"></i> <?php echo JText::_('COM_OSCAMPUS_CERTIFICATES_1'); ?>
                </div>
            </div>

            <?php
            foreach ($this->items as $item) :
                ?>
                <div class="osc-section osc-row-one">
                    <div class="block6 osc-certificate-course">
                        <?php echo JHtml::_('osc.course.link', $item); ?>
                    </div>
                    <div class="block3 osc-certificate-date">
                        <?php echo $item->date_earned->format('F j, Y'); ?>
                    </div>
                    <div class="block3 osc-certificate-download">
                        <?php echo JHtml::_('osc.link.certificate', $item->id); ?>
                    </div>
                </div>
                <?php
            endforeach;
            ?>

        </div>
        <?php
    else :
        ?>
        <div class="osc-alert-notify a-center">
            <?php
            $link = JRoute::_(OscampusRoute::getInstance()->get('pathways'));
            $link = JHtml::_('link', $link, JText::_('COM_OSCAMPUS_PATHWAYS_LINK'), 'class="osc-btn"');
            echo JText::sprintf('COM_OSCAMPUS_MYCERTIFICATES_GET_STARTED', $link);
            ?>
        </div>
        <?php
    endif;
    ?>
</div>

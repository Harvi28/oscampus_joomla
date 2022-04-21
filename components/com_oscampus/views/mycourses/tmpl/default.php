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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die();

/** @var OscampusViewMycourses $this */

HTMLHelper::_('bootstrap.tooltip');
?>
<div class="<?php echo $this->getPageClass('osc-container oscampus-pathways'); ?>" id="oscampus">
    <?php
    if ($heading = $this->getHeading('COM_OSCAMPUS_HEADING_MYCOURSES')):
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
                    <i class="fa fa-bars"></i> <?php echo Text::_('COM_OSCAMPUS_COURSE_TITLE'); ?>
                </div>
                <div class="block3">
                    <i class="fa fa-calendar"></i> <?php echo Text::_('COM_OSCAMPUS_LAST_VISIT'); ?>
                </div>
                <div class="block3">
                    <i class="fa fa-battery-3"></i> <?php echo Text::_('COM_OSCAMPUS_PROGRESS'); ?>
                </div>
            </div>

            <?php
            foreach ($this->items as $item) :
                $progress = $item->progress . '%';
                $attribs = array(
                    'style' => sprintf('width: %s;', $progress),
                );

                if ($item->progress < 30) :
                    $attribs['class'] = 'hasTooltip';
                    $attribs['title'] = HTMLHelper::_('tooltipText', '', $progress);
                elseif ($item->progress == 100) :
                    $attribs['class'] = 'osc-progress-bar-completed';
                endif;
                ?>
                <div class="osc-section osc-row-one">
                    <div class="block6">
                        <?php echo HTMLHelper::_('osc.course.link', $item); ?>
                    </div>
                    <div class="block3">
                        <?php echo $item->last_visit->format('F j, Y'); ?>
                    </div>
                    <div class="block3">
                <span class="osc-progress-bar">
                    <span <?php echo ArrayHelper::toString($attribs); ?>>
                        <span>
                            <?php echo ($item->progress >= 30) ? $progress : ''; ?>
                        </span>
                    </span>
                </span>
                    </div>
                </div>
            <?php
            endforeach;
            ?>
        </div>
    <?php
    else :
        ?>
        <div class="osc-section">
            <?php
            $link = JRoute::_(OscampusRoute::getInstance()->get('pathways'));
            $link = HTMLHelper::_('link', $link, Text::_('COM_OSCAMPUS_PATHWAYS_LINK'));
            echo Text::sprintf('COM_OSCAMPUS_MYCOURSES_GET_STARTED', $link);
            ?>
        </div>
    <?php
    endif;
    ?>
</div>

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

/** @var OscampusViewSearch $this */

JHtml::_('behavior.core');

?>
<div class="<?php echo $this->getPageClass('osc-container oscampus-search'); ?>" id="oscampus">
    <?php
    if ($heading = $this->getHeading('COM_OSCAMPUS_HEADING_SEARCH')) :
        ?>
        <div class="page-header">
            <h1><?php echo $heading; ?></h1>
        </div>
        <?php
    endif;

    if (!$this->items) :
        ?>
        <div class="osc-alert-warning m-bottom"><i class="fa fa-info-circle"></i>
            <?php echo JText::_('COM_OSCAMPUS_SEARCH_RESULTS_NOTFOUND'); ?>
        </div>
        <?php
    endif;

    $lastSection = null;
    foreach ($this->items as $item) :
        if ($item->section != $lastSection) :
            $heading = 'COM_OSCAMPUS_SEARCH_RESULTS_' . $item->section;
            $count   = $this->model->getTotal($item->section);
            ?>
            <div class="osc-alert-success m-bottom"><i class="fa fa-info-circle"></i>
                <?php echo JText::plural($heading, $count); ?>
            </div>
            <?php
        endif;

        echo OscampusLayoutHelper::render('listing.' . $item->section, $item);
        $lastSection = $item->section;
    endforeach;

    echo $this->pagination->getPaginationLinks('listing.pagination');
    ?>
</div>


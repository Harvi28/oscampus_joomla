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
?>
<div class="<?php echo $this->getPageClass('oscampus-default'); ?>" id="oscampus">
    <div class="osc-section">
        <h1 class="osc-lesson-title"><?php echo $this->lesson->title; ?></h1>
        <div class="osc-lesson-links">
            <?php echo $this->loadDefaultTemplate('navigation'); ?>
        </div>
    </div>
    <!-- .osc-lesson-links -->

    <div class="<?php echo $this->getContentClass(); ?>">
        <?php
        if ($content = $this->lesson->render()) :
            echo $content;

        else :
            echo JText::sprintf('COM_OSCAMPUS_ERROR_LESSON_TYPE_UNDEFINED', $this->lesson->type);
        endif;
        ?>
    </div>
    <?php
    echo $this->loadDefaultTemplate('description');
    echo $this->loadDefaultTemplate('files');

    echo OscampusHelper::renderModule('oscampus_lesson_bottom');
    ?>
</div>

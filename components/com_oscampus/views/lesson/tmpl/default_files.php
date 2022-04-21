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

if ($this->files && $this->lesson->isAuthorised()) :
    ?>
    <div class="osc-section oscampus-lesson-files">
        <h3><?php echo JText::_('COM_OSCAMPUS_LESSON_FILES'); ?></h3>
        <div class="osc-table">
            <?php
            foreach ($this->files as $i => $file) :
                ?>
                <div class="<?php echo 'osc-section ' . ($i % 2 ? 'osc-row-two' : 'osc-row-one'); ?>">
                    <div class="block6">
                        <?php echo JHtml::_('link', $file->path, $file->title, 'target="_blank"'); ?>
                    </div>
                    <div class="block6">
                        <?php echo $file->description; ?>
                    </div>
                </div>
                <?php
            endforeach;
            ?>
        </div>
    </div>
    <?php
endif;

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
<div id="content-content" class="osc-course-tabs-content">
    <div class="osc-table">
        <?php
        foreach ($this->lessons as $module) :
            ?>
            <div class="osc-section osc-row-heading">
                <div class="block12 p-left-x"><i class="fa fa-dot-circle-o"></i>
                    <?php echo $module->title; ?>
                </div>
            </div>
            <?php
            foreach ($module->lessons as $i => $lesson) :
                ?>
                <div class="<?php echo 'osc-section ' . ($i % 2 ? 'osc-row-two' : 'osc-row-one'); ?>">
                    <div class="block8 p-left-xx">
                        <?php
                        echo JHtml::_('osc.lesson.link', $lesson);
                        echo JHtml::_('osc.lesson.freeflag', $lesson);
                        ?>

                    </div>
                    <?php if (isset($this->viewed[$lesson->id])) : ?>
                        <div class="block4 osc-check-viewed osc-hide-tablet">
                            <?php echo JHtml::_('osc.lesson.status', $this->viewed[$lesson->id]); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach;
        endforeach;
        ?>
    </div>
</div>
<!-- #content-lessons -->

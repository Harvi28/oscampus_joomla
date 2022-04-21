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

/**
 * @var JLayoutFile $this
 * @var object      $displayData
 * @var string      $layoutOutput
 * @var string      $path
 */

$lesson = OscampusFactory::getContainer()->lesson;
$lesson->loadById($displayData->id);

$link = JHtml::_('osc.link.lessonid', $lesson->courses_id, $lesson->id, null, null, true);

?>
<div class="osc-section osc-course-list">
    <div class="block4 osc-course-image">
        <?php echo JHtml::_('link', $link, $lesson->getOverlayImage()); ?>
    </div>
    <div class="block8 osc-course-description">
        <h2><?php echo JHtml::_('link', $link, $lesson->title); ?></h2>
        <?php echo JHtml::_('osc.link.course', $lesson->courses_id, $lesson->courseTitle); ?>
    </div>
</div>
<!-- .osc-section -->

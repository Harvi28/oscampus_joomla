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
 * @var OscampusModelUtilities $model
 */

$userId   = OscampusFactory::getApplication()->input->getInt('id');
$model    = $this->getModel();
$activity = $model->getUserActivity($userId);

$more = '<br><span class="icon-plus-circle"></span>' . JText::_('COM_OSCAMPUS_MORE_IN_LIST') . '</li>';

?>
<div class="form-horizontal">
    <label><?php echo JText::_('COM_OSCAMPUS_UTILITIES_CERTIFICATES'); ?></label>
    <ol>
        <?php
        foreach ($activity->certificates as $i => $certificate) :
            ?>
            <li>
                <?php
                echo sprintf(
                    '%s<br>%s',
                    $certificate->date_earned,
                    $certificate->course_title
                );

                $remaining = count($activity->certificates) - $i - 1;
                if ($i >= 4 && $remaining) :
                    echo sprintf($more, $remaining);
                    break;
                endif;
                ?>
            </li>
            <?php
        endforeach;
        ?>
    </ol>
</div>

<div class="form-horizontal">
    <label><?php echo JText::_('COM_OSCAMPUS_UTILITIES_VISITS'); ?></label>
    <ol>
        <?php
        foreach ($activity->visits as $i => $visit) :
            ?>
            <li>
                <?php
                echo sprintf(
                    '%s<br>%s',
                    $visit->last_visit,
                    $visit->lesson_title
                );

                $remaining = count($activity->visits) - $i - 1;
                if ($i >= 4 && $remaining) :
                    echo sprintf($more, $remaining);
                    break;
                endif;
                ?>
            </li>
            <?php
        endforeach;
        ?>
    </ol>
</div>

<div class="form-horizontal">
    <label><?php echo JText::_('COM_OSCAMPUS_UTILITIES_DOWNLOADS'); ?></label>
    <?php
    foreach ($activity->downloads as $i => $downloaded) :
        echo sprintf(
            '%s (%s)<br>',
            JHtml::_('date.relative', $downloaded->weekending, 'week'),
            number_format($downloaded->videos)
        );
        ?>
        <?php
    endforeach;
    ?>
</div>

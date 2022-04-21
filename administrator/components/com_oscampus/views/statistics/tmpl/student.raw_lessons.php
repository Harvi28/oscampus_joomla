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

$container = OscampusFactory::getContainer();

$certificate = $container->certificate->getCertificates($this->student->id, $this->course->id);
$return      = $container->getApplication()->input->get('return');
?>
<div class="btn-toolbar">
    <a id="backButton" class="btn btn-small button-featured">
        <span class="icon-back" aria-hidden="true"></span>
        <?php echo Text::_('COM_OSCAMPUS_BACK'); ?>
    </a>
</div>

<div id="osc-data-area">
    <form method="post" name="adminForm" id="adminForm">
        <h1><?php echo sprintf('%s (%s)', $this->student->name, $this->student->username); ?></h1>
        <h2><?php echo $this->course->title; ?></h2>
        <?php
        if ($certificate) :
            ?>
            <div class="block12">
                <?php echo HTMLHelper::_('osc.certificate.icon', $certificate); ?>
                <?php echo Text::sprintf('COM_OSCAMPUS_CERTIFICATE_EARNED_LABEL', $certificate->date_earned); ?>
            </div>
        <?php endif;

        foreach ($this->items as $module) : ?>
            <fieldset>
                <legend><?php echo $module->title; ?></legend>

                <table class="table table-striped adminlist">
                    <thead>
                    <tr>
                        <th><?php echo Text::_('COM_OSCAMPUS_LESSON'); ?></th>
                        <th><?php echo Text::_('COM_OSCAMPUS_FIRST_VISIT'); ?></th>
                        <th><?php echo Text::_('COM_OSCAMPUS_LAST_VISIT'); ?></th>
                        <th><?php echo Text::_('COM_OSCAMPUS_COMPLETED'); ?></th>
                        <th><?php echo Text::_('COM_OSCAMPUS_VISITS'); ?></th>
                        <th><?php echo Text::_('COM_OSCAMPUS_PROGRESS'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $row = 0;
                    foreach ($module->lessons as $i => $lesson) : ?>
                        <tr class="<?php echo 'row' . ($i % 2); ?>">
                            <td>
                                <?php echo $lesson->lesson_title; ?>
                                <div>(<?php echo HTMLHelper::_('osc.lesson.type', $lesson->type); ?>)</div>
                            </td>
                            <td>
                                <?php echo $lesson->first_visit; ?>
                            </td>
                            <td>
                                <?php echo $lesson->last_visit; ?>
                            </td>
                            <td>
                                <?php
                                echo $lesson->visits
                                    ? $lesson->completed ?: Text::_('COM_OSCAMPUS_LESSON_IN_PROGRESS')
                                    : '';
                                ?>
                            </td>
                            <td>
                                <?php echo number_format($lesson->visits); ?>
                            </td>
                            <td>
                                <?php echo $lesson->visits
                                    ? Text::sprintf('COM_OSCAMPUS_SCORE', number_format($lesson->score))
                                    : ''; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </fieldset>
        <?php endforeach; ?>
    </form>
</div>
<script>
    ;(function($) {
        const parents      = {
                  students: 'student',
                  courses : 'course'
              },
              parentReport = '<?php echo $return; ?>';

        $.Oscampus.admin.statistics.init({
            loading  : '<?php echo $this->loadingImage; ?>',
            form     : $('#adminForm'),
            container: $('#osc-statistics'),
            formData : {
                report   : 'student',
                course_id: <?php echo $this->course->id; ?>,
                user_id  :<?php echo $this->student->id; ?>
            },
            return   : parents[parentReport] || 'course'
        });
    })(jQuery);
</script>

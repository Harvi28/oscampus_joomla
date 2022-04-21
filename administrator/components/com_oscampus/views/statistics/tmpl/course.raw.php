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
use Joomla\CMS\Layout\LayoutHelper;

defined('_JEXEC') or die();

$ordering  = $this->escape($this->state->get('list.ordering'));
$direction = $this->escape($this->state->get('list.direction'));
?>
<div class="btn-toolbar">
    <a id="backButton" class="btn btn-small button-featured">
        <span class="icon-back" aria-hidden="true"></span>
        <?php echo Text::_('COM_OSCAMPUS_BACK'); ?>
    </a>
</div>

<div id="osc-data-area">
    <form method="post" name="adminForm" id="adminForm">
        <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>

        <h1><?php echo $this->course->get('title'); ?></h1>
        <?php if (empty($this->items)) : ?>
            <div class="alert alert-no-items">
                <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </div>

        <?php else : ?>
            <table class="table table-striped adminlist">
                <thead>
                <tr>
                    <th>
                        <?php
                        echo HTMLHelper::_(
                            'searchtools.sort',
                            'COM_OSCAMPUS_CERTIFICATE_STUDENT',
                            'name',
                            $direction,
                            $ordering
                        );
                        ?>
                    </th>
                    <th>
                        <?php
                        echo HTMLHelper::_(
                            'searchtools.sort',
                            'COM_OSCAMPUS_FIRST_VISIT',
                            'first_visit',
                            $direction,
                            $ordering
                        );
                        ?>
                    </th>
                    <th>
                        <?php
                        echo HTMLHelper::_(
                            'searchtools.sort',
                            'COM_OSCAMPUS_LAST_VISIT',
                            'last_visit',
                            $direction,
                            $ordering
                        );
                        ?>
                    </th>
                    <th><?php echo Text::_('COM_OSCAMPUS_PROGRESS'); ?></th>
                    <th><?php echo Text::_('COM_OSCAMPUS_LESSONS'); ?></th>
                    <th></th>
                </tr>
                </thead>

                <tfoot>
                <tr>
                    <td colspan="100%">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
                </tfoot>

                <tbody>
                <?php
                foreach ($this->items as $i => $item) :
                    ?>
                    <tr class="<?php echo 'row' . ($i % 2); ?>">
                        <td>
                            <?php
                            if ($item->users_id) :
                                echo HTMLHelper::_(
                                    'link',
                                    '#',
                                    sprintf('%s (%s)', $item->name, $item->username),
                                    sprintf('data-user="%s"', $item->users_id)
                                );
                            else :
                                echo Text::sprintf('COM_OSCAMPUS_DELETED_USER', $item->users_id);
                            endif;
                            ?>
                        </td>
                        <td>
                            <?php echo $item->first_visit; ?>
                        </td>
                        <td>
                            <?php echo $item->last_visit; ?>
                        </td>
                        <td>
                            <?php echo HTMLHelper::_('osc.progressbar', $item->progress); ?>
                        </td>
                        <td>
                            <?php
                            echo number_format($item->lessons_completed) . '/' . number_format($item->lesson_count);
                            ?>
                        </td>
                        <td>
                            <?php
                            echo HTMLHelper::_(
                                'osc.certificate.icon',
                                $item->certificates_id,
                                $item->date_earned
                            );
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <input id="list_fullordering"
               name="list[fullordering]"
               type="hidden"
               value="<?php echo $ordering . ' ' . $direction; ?>">
    </form>
</div>

<script>
    ;(function($) {
        $.Oscampus.admin.statistics.init({
            loading  : '<?php echo $this->loadingImage; ?>',
            form     : $('#adminForm'),
            container: $('#osc-statistics'),
            formData : {
                report   : 'course',
                course_id: <?php echo (int)$this->course->get('id'); ?>
            },
            return   : 'courses'
        });

        $('[data-user]').on('click', function(evt) {
            evt.preventDefault();

            let userId = $(this).data('user')

            if (userId) {
                $.Oscampus.admin.statistics.submitForm({
                    report : 'student.lessons',
                    user_id: userId,
                    return : 'courses'
                });

            } else {
                alert(Joomla.JText._('COM_OSCAMPUS_STATISTICS_NO_STUDENT'));
            }
        });
    })(jQuery);
</script>

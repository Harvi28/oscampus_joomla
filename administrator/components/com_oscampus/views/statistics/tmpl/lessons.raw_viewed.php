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
use Joomla\CMS\Layout\LayoutHelper;

defined('_JEXEC') or die();

$ordering  = $this->escape($this->state->get('list.ordering'));
$direction = $this->escape($this->state->get('list.direction'));
?>
<div id="osc-data-area">
    <form method="post" name="adminForm" id="adminForm">
        <h3><?php echo Text::_('COM_OSCAMPUS_STATISTICS_SELECT_VIEWED'); ?></h3>
        <?php echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>

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
                            Text::_('COM_OSCAMPUS_WEEK'),
                            'weekstart',
                            $direction,
                            $ordering
                        );
                        ?>
                    </th>
                    <th>
                        <?php
                        echo HTMLHelper::_(
                            'searchtools.sort',
                            Text::_('COM_OSCAMPUS_VIDEOS'),
                            'videos',
                            $direction,
                            $ordering
                        );
                        ?>
                    </th>
                    <th>
                        <?php
                        echo HTMLHelper::_(
                            'searchtools.sort',
                            Text::_('COM_OSCAMPUS_QUIZZES'),
                            'quizzes',
                            $direction,
                            $ordering
                        );
                        ?>
                    </th>
                    <th>
                        <?php
                        echo HTMLHelper::_(
                            'searchtools.sort',
                            Text::_('COM_OSCAMPUS_TEXT'),
                            'text',
                            $direction,
                            $ordering
                        );
                        ?>
                    </th>
                    <th><?php echo Text::_('COM_OSCAMPUS_OTHER'); ?></th>
                    <th>
                        <?php
                        echo HTMLHelper::_(
                            'searchtools.sort',
                            Text::_('COM_OSCAMPUS_TOTAL'),
                            'total',
                            $direction,
                            $ordering
                        );
                        ?>
                    </th>
                    <th>
                        <?php
                        echo HTMLHelper::_(
                            'searchtools.sort',
                            Text::_('COM_OSCAMPUS_USERS'),
                            'users',
                            $direction,
                            $ordering
                        );
                        ?>
                    </th>
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
                foreach ($this->items as $i => $views) :
                    $otherTotal = $views->total - $views->videos - $views->quizzes - $views->text;
                    ?>
                    <tr class="<?php echo 'row' . ($i % 2); ?>">
                        <td><?php echo $views->weekstart; ?></td>
                        <td><?php echo number_format($views->videos); ?></td>
                        <td><?php echo number_format($views->quizzes); ?></td>
                        <td><?php echo number_format($views->text); ?></td>
                        <td><?php echo number_format($otherTotal); ?></td>
                        <td><?php echo number_format($views->total); ?></td>
                        <td><?php echo number_format($views->users); ?></td>
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
                report: 'lessons.viewed'
            }
        });
    }(jQuery));
</script>

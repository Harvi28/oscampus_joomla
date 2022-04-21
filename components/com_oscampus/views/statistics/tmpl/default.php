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

defined('_JEXEC') or die();

if (OscampusFactory::getDocument()->getType() != 'html') :
    OscampusFactory::getApplication()->enqueueMessage(
        JText::sprintf('COM_OSCAMPUS_ERROR_STATISTICS_REPORT_NOT_FOUND', $this->getLayout()),
        'error'
    );
    echo $this->loadDefaultTemplate('messages');

else:
    $itemid = OscampusFactory::getApplication()->input->getInt('Itemid');

    ?>
    <div class="<?php echo $this->getPageClass('osc-container oscampus-statistics'); ?>" id="oscampus">
        <?php
        if ($heading = $this->getHeading('COM_OSCAMPUS_HEADING_STATISTICS')) :
            ?>
            <div class="page-header">
                <h1><?php echo $heading; ?></h1>
            </div>
        <?php
        endif;
        ?>
        <form id="oscAdminForm"
              name="oscAdminForm"
              action="index.php"
              method="post">
            <input type="hidden" name="option" value="com_oscampus"/>
            <input type="hidden" name="view" value="statistics"/>
            <input type="hidden" name="report" value=""/>
            <input type="hidden" name="return" value=""/>
            <input type="hidden" name="format" value="raw"/>
            <input type="hidden" name="Itemid" value="<?php echo $itemid; ?>"/>
            <?php echo JHtml::_('form.token'); ?>

            <div class="osc-section osc-course-tabs">
                <div class="block2 osc-tab">
                    <?php
                    echo JHtml::_(
                        'link',
                        '#statistics',
                        '<i class="fa fa-bar-chart"></i>' . ' ' . JText::_('COM_OSCAMPUS_STATISTICS')
                    );
                    ?>
                </div>

                <div class="block2 osc-tab osc-tab-disabled">
                    <?php
                    echo JHtml::_(
                        'link',
                        '#courses',
                        '<i class="fa fa-bars"></i>' . ' ' . JText::_('COM_OSCAMPUS_COURSES')
                    );
                    ?>
                </div>

                <div class="block2 osc-tab osc-tab-disabled">
                    <?php
                    echo JHtml::_(
                        'link',
                        '#students',
                        '<i class="fa fa-users"></i>' . ' ' . JText::_('COM_OSCAMPUS_COURSE_STUDENTS')
                    );
                    ?>
                </div>
            </div>

            <div id="osc-data-area" class="osc-course-tabs-content"></div>
        </form>
    </div>
<?php
endif;

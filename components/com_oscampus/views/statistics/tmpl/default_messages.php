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

if ($messageQueue = OscampusFactory::getApplication()->getMessageQueue(true)) :
    $messages = array();
    foreach ($messageQueue as $message) {
        $type = $message['type'];
        if (!isset($messages[$type])) {
            $messages[$type] = array();
        }
        $messages[$type][] = $message['message'];
    }
    ?>
    <div class="<?php echo $this->getPageClass('osc-container oscampus-statistics'); ?>" id="oscampus">
        <div class="page-header">
            <h2><?php echo JText::_('COM_OSCAMPUS_ERROR_STATISTICS_RESULT'); ?></h2>
        </div>
        <?php echo JLayoutHelper::render('joomla.system.message', array('msgList' => $messages)); ?>

    </div>
    <?php
endif;

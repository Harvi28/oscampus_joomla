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

class OscampusControllerUtilities extends OscampusControllerRaw
{
    public function checkToken($method = 'post', $redirect = true)
    {
        return true;
    }

    /**
     * Display and optionall execute the transfer of user activity to amother user
     *
     * @return void
     * @throws Exception
     */
    public function transfer()
    {
        /** @var OscampusModelUtilities $model */
        $model = OscampusModel::getInstance('utilities');
        $form  = $model->getForm();
        $form->bind($_REQUEST['jform']);

        $source = $form->getField('source', 'transfer');
        $target = $form->getField('target', 'transfer');

        if ($source->value && $target->value) {
            if ($source->value == $target->value) {
                echo JText::_('COM_OSCAMPUS_UTILITIES_TRANSFER_IDENTICAL');

            } else {
                $model = $this->getModel('utilities');
                $view  = $this->getView('utilities', 'raw');
                $view->setModel($model, true);
                $view->setLayout('transfer');
                $view->display('overlap');
            }

        } elseif ($source->value) {
            echo JText::sprintf('COM_OSCAMPUS_UTILITIES_TRANSFER_SELECT_TARGET', $target->title);

        } elseif ($target->value) {
            echo JText::sprintf('COM_OSCAMPUS_UTILITIES_TRANSFER_SELECT_SOURCE', $source->title);

        } else {
            echo JText::_('COM_OSCAMPUS_UTILITIES_TRANSFER_SELECT_USERS');
        }
    }
}

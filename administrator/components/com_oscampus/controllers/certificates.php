<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2019 Joomlashack.com. All rights reserved
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

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();

class OscampusControllerCertificates extends OscampusControllerAdmin
{
    protected $text_prefix = 'COM_OSCAMPUS_CERTIFICATES';

    public function getModel(
        $name = 'Certificate',
        $prefix = 'OscampusModel',
        $config = ['ignore_request' => true]
    ) {
        return parent::getModel($name, $prefix, $config);
    }

    public function setDefault()
    {
        $this->checkToken();

        $return = 'index.php?option=com_oscampus&view=' . $this->view_list;
        $id     = $this->input->get('cid', [], 'array');
        $id     = (int)array_shift($id);
        if ($id) {
            /** @var OscampusModelCertificate $model */
            $model = $this->getModel();

            try {
                $model->setDefault($id);
                $this->setRedirect($return, Text::_('COM_OSCAMPUS_DEFAULT_UPDATED'));
                return;

            } catch (Throwable $error) {
                $errorMessage = $error->getMessage();
            }

        } else {
            $errorMessage = Text::_('COM_OSCAMPUS_ERROR_NO_SELECTION');
        }

        $this->setRedirect($return, $errorMessage, 'error');
    }
}

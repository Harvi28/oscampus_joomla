<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2021 Joomlashack.com. All rights reserved
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
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
 * along with OSCampus.  If not, see <https://www.gnu.org/licenses/>.
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();

FormHelper::loadFieldClass('Hidden');

class OscampusFormFieldGdcheck extends JFormFieldHidden
{
    /**
     * @inheritDoc
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        if (parent::setup($element, $value, $group)) {
            $this->hidden = true;

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    protected function getInput()
    {
        if (function_exists('gd_info')) {
            return '';
        }

        $field = $this->form->getField('enabled', $this->group);

        $jScript = <<<SCRIPT
;jQuery(document).ready(function ($) {
    \$toggle = $('#{$field->id}')
        .prop('disabled', true)
        .addClass('readonly disabled');
});
SCRIPT;

        Factory::getDocument()->addScriptDeclaration($jScript);

        return sprintf(
            '<div class="alert alert-warning">%s<br>%s</div>',
            Text::_('COM_OSCAMPUS_WARNING_GD_REQUIRED'),
            Text::_('COM_OSCAMPUS_WARNING_GD_CONTACT_HOST')
        );
    }
}

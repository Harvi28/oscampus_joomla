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

JFormHelper::loadFieldClass('List');

class OscampusFormFieldOptions extends JFormFieldList
{
    /**
     * @var string
     */
    protected $optionName = null;

    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        $this->optionName = strtolower((string)$element['options']);

        return parent::setup($element, $value, $group);
    }

    protected function getInput()
    {
        if ($this->optionName == 'pathwayowners') {
            $params = OscampusFactory::getContainer()->params;
            if (!$params->get('advanced.pathowners')) {
                return null;
            }
        }

        return parent::getInput();
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function getOptions()
    {

        try {
            $options = JHtml::_('osc.options.' . $this->optionName);
            return array_merge(parent::getOptions(), $options);

        } catch (Throwable $e) {
            OscampusFactory::getApplication()->enqueueMessage(
                __CLASS__ . ': ' . JText::sprintf('COM_OSCAMPUS_ERROR_FIELD_BAD_OPTIONS', $this->optionName),
                'error'
            );
        }

        return [];
    }
}

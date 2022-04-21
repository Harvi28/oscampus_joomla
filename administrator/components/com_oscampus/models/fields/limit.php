<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2018-2021 Joomlashack.com. All rights reserved
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

class OscampusFormFieldLimit extends JFormFieldList
{
    /**
     * @var bool
     */
    protected $globalDefault = true;

    /**
     * @var bool
     */
    protected $oscampusDefault = true;

    /**
     * @var int[]
     */
    protected $standardOptions = array(10, 15, 20, 25, 30, 50, 100, 200, 500);

    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        if (parent::setup($element, $value, $group)) {
            $properties = array('globalDefault', 'oscampusDefault');
            foreach ($properties as $property) {
                $attribute = strtolower(join('_', preg_split('/(?<=[a-z0-9])(?=[A-Z])/x', $property)));
                if ($value = $element[$attribute]) {
                    $value = (string)$value;

                    $this->$property = !($value == 'false' || $value == '0' || $value == 'off');
                }
            }

            return true;
        }

        return false;
    }

    protected function getOptions()
    {
        $options = array();
        if ($this->globalDefault) {
            $options[] = JHtml::_('select.option', -1, JText::_('COM_OSCAMPUS_OPTION_GLOBAL_DEFAULT'));
        }
        if ($this->oscampusDefault) {
            $options[] = JHtml::_('select.option', -2, JText::_('COM_OSCAMPUS_OPTION_OSCAMPUS_DEFAULT'));
        }

        if ($customOptions = parent::getOptions()) {
            $options = array_merge($customOptions);

        } else {
            foreach ($this->standardOptions as $optionValue) {
                $options[] = JHtml::_('select.option', $optionValue, JText::_('J' . $optionValue));
            }
        }

        return $options;
    }
}

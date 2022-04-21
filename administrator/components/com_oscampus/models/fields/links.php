<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2015-2021 Joomlashack.com. All rights reserved
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

use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die();

JFormHelper::loadFieldClass('Text');

class OscampusFormFieldLinks extends JFormFieldText
{
    protected $commonAttributes = array();

    protected $linkAttributes = array();

    protected $show = null;

    /**
     * @param SimpleXMLElement $element
     * @param mixed            $value
     * @param string           $group
     *
     * @return bool
     */
    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        if (!parent::setup($element, $value, $group)) {
            return false;
        }

        // Common additional attributes
        if ($class = (string)$this->element['class']) {
            $this->commonAttributes['class'] = $class;
        }
        if ((string)$this->element['readonly'] == 'true') {
            $this->commonAttributes['readonly'] = 'readonly';
        }
        if ((string)$this->element['disabled'] == 'true') {
            $this->commonAttributes['disabled'] = 'disabled';
        }

        // Attributes meant only for the link field
        if ($size = (int)$this->element['size']) {
            $this->linkAttributes['size'] = $size;
        }
        if ($maxLength = (int)$this->element['maxlength']) {
            $this->linkAttributes['maxlength'] = $maxLength;
        }
        if ((string)$this->element['autocomplete'] == 'off') {
            $this->linkAttributes['autocomplete'] = 'off';
        }
        if ($onchange = (string)$this->element['onchange']) {
            $this->linkAttributes['onchange'] = $onchange;
        }

        // Reformat value to our needs
        $defaultShow = (string)$this->element['show'];
        $defaultShow = $defaultShow == 'true' || $defaultShow == '1' || $defaultShow == '';

        if (empty($this->value)) {
            $this->show  = $defaultShow;
            $this->value = '';
        } else {
            $value = is_object($this->value) ? $this->value : (object)$this->value;

            $this->show  = isset($value->show) ? $value->show : $defaultShow;
            $this->value = isset($value->link) ? $value->link : '';
        }

        return true;
    }

    public function getInput()
    {
        $linkAttribs = array_merge(
            array(
                'type'  => 'text',
                'name'  => $this->name . '[link]',
                'id'    => $this->id,
                'value' => htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8')
            ),
            $this->commonAttributes,
            $this->linkAttributes
        );

        $link = '<input ' . ArrayHelper::toString($linkAttribs) . '/>';

        return $link;
    }
}

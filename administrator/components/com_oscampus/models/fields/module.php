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

defined('_JEXEC') or die();

JFormHelper::loadFieldClass('Text');

class OscampusFormFieldModule extends JFormFieldText
{
    public function getInput()
    {
        $courseId = $this->getCourseId();
        $options  = array_map(
            function ($row) {
                return $row->text;
            },
            JHtml::_('osc.options.modules', $courseId)
        );
        $options  = json_encode($options);

        JHtml::_('script', 'com_oscampus/jquery-ui.min.js', array('relative' => true));
        JHtml::_('stylesheet', 'com_oscampus/jquery-ui.min.css', array('relative' => true));

        JHtml::_('osc.onready', "$('#{$this->id}').autocomplete({source: {$options}, autoFocus: true});");

        return parent::getInput();
    }

    protected function getCourseId()
    {
        $courseId = null;

        $fieldName = (string)$this->element['coursefield'] ?: 'courses_id';
        if ($courseField = $this->form->getField($fieldName)) {
            return $courseField->value;
        }

        return null;
    }
}

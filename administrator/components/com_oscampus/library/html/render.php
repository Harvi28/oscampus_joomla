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

abstract class OscRender
{
    /**
     * Display the sidebar. J!2 has no equivalent.
     *
     * @return string
     */
    public static function submenu()
    {
        return JHtmlSidebar::render();
    }

    /**
     * Write a fieldset block for the admin UI
     *
     * @param JForm       $form
     * @param string      $fieldSet
     * @param bool|string $legend
     * @param int|string  $span
     *
     * @return string
     */
    public static function adminfieldset(JForm $form, $fieldSet, $legend = false, $span = 12)
    {
        $fieldSets = $form->getFieldsets();

        if (!empty($fieldSets[$fieldSet])) {
            $html   = array();
            $html[] = "<div class=\"span{$span}\">";

            if ($legend === true) {
                $legend = $fieldSets[$fieldSet]->label;
            }
            if ($legend) {
                $html[] = '<fieldset class="adminform">';
                $html[] = '<legend>' . JText::_($legend) . '</legend>';
            }

            if ($description = $fieldSets[$fieldSet]->description) {
                $html[] = '<div class="tab-description alert alert-info">';
                $html[] = '<span class="icon-info"></span> ' . JText::_($description);
                $html[] = '</div>';
            }

            $html[] = $form->renderFieldset($fieldSet);

            if ($legend) {
                $html[] = '</fieldset>';
            }
            $html[] = '</div>';

            return join("\n", $html);
        }

        return '';
    }

    /**
     * Write a field block for the admin UI
     *
     * @param JFormField $field
     * @param bool       $legend
     * @param int|string $span
     *
     * @return string
     */
    public static function adminfield(JFormField $field, $legend = false, $span = 12)
    {
        $legend      = $legend ? $field->getAttribute('label') : null;
        $description = $field->getAttribute('description');

        $html   = array();
        $html[] = "<div class=\"span{$span}\">";

        $html[] = '<fieldset class="adminform">';
        if ($legend) {
            $html[] = '<legend>' . JText::_($legend) . '</legend>';
        }
        if ($description) {
            $html[] = '<p>' . JText::_($description) . '</p>';
        }

        $html[] = $field->input;
        $html[] = '</fieldset>';
        $html[] = '</div>';

        return join("\n", $html);
    }

    /**
     * Simple form field rendering for use in twig
     *
     * @param JFormField $field
     *
     * @return string
     */
    public static function formfield(JFormField $field)
    {
        return $field->label . ' ' . $field->input;
    }

    /**
     * Output all hidden fields in a fieldset
     *
     * @param JForm  $form
     * @param string $fieldSetName
     *
     * @return string
     */
    public static function hiddenfields(JForm $form, $fieldSetName = 'hidden')
    {
        $html = array();

        if ($fieldSetName) {
            $fields = $form->getFieldset($fieldSetName);
        } else {
            $fields    = array();
            $fieldSets = $form->getFieldsets();
            foreach ($fieldSets as $fieldSetName => $fieldSet) {
                $fieldSet = $form->getFieldset($fieldSetName);
                $fields   = array_merge($fields, array_values($fieldSet));
            }
        }
        foreach ($fields as $field) {
            if (!strcasecmp($field->type, 'hidden')) {
                $html[] = $field->input;
            }
        }

        return join("\n", $html);
    }
}

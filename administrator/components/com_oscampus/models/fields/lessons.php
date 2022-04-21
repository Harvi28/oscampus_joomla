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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();

class OscampusFormFieldLessons extends JFormField
{
    protected function getInput()
    {
        $html = array();

        $html[] = '<div id="' . $this->id . '">';
        $html[] = '<ul class="oscampus-module">';

        $modules = $this->getLessons();
        foreach ($modules as $moduleId => $module) {
            $html[] = $this->createModuleItem($module);
        }

        $html[] = '</ul>';
        $html[] = '</div>';

        HTMLHelper::_('osc.fontawesome');
        $this->addJavascript();

        return join("\n", $html);
    }

    /**
     * We ignore $this->value and retrieve the modules/lessons based on the form ID field.
     * If the course ID is in a different field, the 'coursefield' attribute can be used for
     * overriding.
     *
     * @return object[]
     */
    protected function getLessons()
    {
        $courseField = (string)$this->element['coursefield'] ?: 'id';
        if ($courseId = (int)$this->form->getField($courseField)->value) {
            $db = OscampusFactory::getDbo();

            $query = $db->getQuery(true)
                ->select(
                    array(
                        'lesson.id',
                        'lesson.modules_id',
                        'lesson.title',
                        'lesson.alias',
                        'lesson.published',
                        'viewlevel.title AS viewlevel_title',
                        'module.title AS module_title'
                    )
                )
                ->from('#__oscampus_lessons AS lesson')
                ->innerJoin('#__oscampus_modules AS module ON module.id = lesson.modules_id')
                ->innerJoin('#__viewlevels viewlevel ON viewlevel.id = lesson.access')
                ->where('module.courses_id = ' . $courseId)
                ->order('module.ordering ASC, lesson.ordering ASC');

            if ($lessons = $db->setQuery($query)->loadObjectList()) {
                $modules = array();
                foreach ($lessons as $lesson) {
                    if (!isset($modules[$lesson->modules_id])) {
                        $modules[$lesson->modules_id] = (object)array(
                            'id'      => $lesson->modules_id,
                            'title'   => $lesson->module_title,
                            'lessons' => array()
                        );
                    }
                    $modules[$lesson->modules_id]->lessons[$lesson->id] = $lesson;
                }

                return $modules;
            }
        }

        return array();
    }

    /**
     * Render html for top level list of modules
     *
     * @param object $module
     *
     * @return string
     */
    protected function createModuleItem($module)
    {
        $moduleInput = sprintf(
            '<input type="hidden" name="%1$s[%2$s]" value="%2$s"/>%3$s',
            $this->name,
            $module->id,
            $module->title
        );

        $html = array(
            '<li>',
            '<span class="handle">',
            '<i class="fa fa-caret-right"></i> ',
            $moduleInput,
            '</span>',
            '<ul class="oscampus-lesson">'
        );

        foreach ($module->lessons as $lessonId => $lesson) {
            $html[] = $this->createLessonItem($lesson);
        }
        $html[] = '</ul>';
        $html[] = '</li>';

        return join('', $html);
    }

    /**
     * Render the individual row for a lesson
     *
     * @param object $lesson
     *
     * @return string
     */
    protected function createLessonItem($lesson)
    {
        $lessonInput = '<input type="hidden" name="%1$s[%2$s][]" value="%3$s"/>%4$s';

        $link       = 'index.php?option=com_oscampus&task=lesson.edit&id=' . $lesson->id;
        $lessonLink = HtmLHelper::_('link', $link, $lesson->title, 'target="_blank"');

        $html = array(
            '<li class="handle">',
            '<i class="fa fa-caret-right"></i> ',
            sprintf($lessonInput, $this->name, $lesson->modules_id, $lesson->id, $lessonLink),
            sprintf(' (%s: %s)', Text::_('COM_OSCAMPUS_ALIAS'), $lesson->alias),
            " - {$lesson->viewlevel_title}",
            '</li>'
        );

        return join('', $html);
    }

    /**
     * Add all js needed for drag/drop ordering
     *
     * @rfeturn void
     */
    protected function addJavascript()
    {
        HtmLHelper::_('osc.jui');
        HtmLHelper::_('script', 'com_oscampus/admin/lesson.min.js', array('relative' => true));

        $options = json_encode(
            array(
                'container' => '#' . $this->id
            )
        );

        HtmLHelper::_('osc.onready', "$.Oscampus.admin.lesson.ordering({$options});");
    }
}

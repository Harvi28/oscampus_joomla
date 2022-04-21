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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Oscampus\Course;

defined('_JEXEC') or die();

JFormHelper::loadFieldClass('List');

class OscampusFormFieldFiles extends JFormFieldList
{
    /**
     * @var array
     */
    protected $lessons = null;

    /**
     * @var string[]
     */
    protected $files = null;

    /**
     * @return string
     * @throws Exception
     */
    protected function getInput()
    {
        HTMLHelper::_('stylesheet', 'com_oscampus/admin.min.css', array('relative' => true));
        HTMLHelper::_('osc.fontawesome');
        $this->addJavascript();

        $html = array(
            sprintf('<div id="%s" class="osc-file-manager">', $this->id),
            '<ul>'
        );

        $files = $this->getFilesFromValue();
        foreach ($files as $file) {
            $html[] = $this->createFileBlock($file);
        }

        $html = array_merge(
            $html,
            array(
                '</ul>',
                $this->createButton('osc-btn-main-admin osc-file-add', 'fa-plus', 'COM_OSCAMPUS_FILES_ADD'),
                '</div>'
            )
        );

        return join('', $html);
    }

    /**
     * Generate html for single file block
     *
     * @param object $file
     *
     * @return string
     * @throws Exception
     */
    protected function createFileBlock($file = null)
    {
        $id = sprintf(
            '<input type="hidden" name="%s[id][]" value="%s"/>',
            $this->name,
            empty($file->id) ? '' : $file->id
        );

        $title = sprintf(
            '<input type="text" name="%s[title][]" value="%s" size="40" placeholder="%s"/>',
            $this->name,
            empty($file->title) ? '' : htmlspecialchars($file->title),
            Text::_('COM_OSCAMPUS_FILES_TITLE_PLACEHOLDER')
        );

        $description = sprintf(
            '<textarea name="%s[description][]" placeholder="%s">%s</textarea>',
            $this->name,
            Text::_('COM_OSCAMPUS_FILES_DESCRIPTION_PLACEHOLDER'),
            empty($file->description) ? '' : htmlspecialchars($file->description)
        );

        $upload   = sprintf('<input type="file" name="%s[upload][]" value=""/>', $this->name);
        $lessonId = empty($file->lessons_id) ? '' : $file->lessons_id;
        $path     = empty($file->path) ? '' : $file->path;

        $html = '<li class="osc-file-block">'
            . $id
            . $this->createButton('osc-file-ordering', 'fa-arrows')
            . $this->createButton('osc-btn-warning-admin osc-file-delete', 'fa-times')
            . $title
            . '<br class="clr"/>' . $description
            . '<br class="clr"/>' . $this->getFileList($path)
            . Text::_('COM_OSCAMPUS_FILES_UPLOAD_PLACEHOLDER') . ' ' . $upload
            . '<br class="clr"/>' . $this->getLessonOptions($lessonId)
            . '</li>';

        return $html;
    }

    /**
     * Create a standard button
     *
     * @param string $class
     * @param string $icon
     * @param string $text
     *
     * @return string
     */
    protected function createButton($class, $icon, $text = null)
    {
        $button = sprintf(
            '<button type="button" class="%s"><i class="fa %s"></i> %s</button>',
            $class,
            $icon,
            ($text ? Text::_($text) : '')
        );

        return $button;
    }

    /**
     * Add all js needed for drag/drop ordering
     *
     * @rfeturn void
     */
    protected function addJavascript()
    {
        HTMLHelper::_('osc.jquery');
        HTMLHelper::_('osc.jui');
        HTMLHelper::_('script', 'com_oscampus/admin/files.min.js', array('relative' => true));

        $options = json_encode(
            array(
                'container' => '#' . $this->id
            )
        );

        HTMLHelper::_('osc.onready', "$.Oscampus.admin.files.init({$options});");
    }

    /**
     * Get a select dropdown for a previously uploaded file
     *
     * @param string $selected
     *
     * @return mixed
     */
    protected function getFileList($selected)
    {
        if ($this->files === null) {
            jimport('joomla.filesystem.folder');
            $files = JFolder::files(JPATH_SITE . '/' . Course::getFilePath());

            $this->files = array();
            foreach ($files as $file) {
                $path          = Course::getFilePath($file);
                $this->files[] = HTMLHelper::_('select.option', $path, $file);
            }

            array_unshift(
                $this->files,
                HTMLHelper::_('select.option', '', Text::_('COM_OSCAMPUS_OPTION_SELECT_FILE_PATH'))
            );
        }

        $html = HTMLHelper::_(
            'select.genericlist',
            $this->files,
            $this->name . '[path][]',
            array(
                'id'          => '',
                'list.select' => $selected
            )
        );

        return $html;
    }

    /**
     * Get a select field for attaching to specific lesson
     *
     * @param int $selected
     *
     * @return string
     * @throws Exception
     */
    protected function getLessonOptions($selected)
    {
        if ($this->lessons === null) {
            $db = OscampusFactory::getDbo();

            $courseField = (string)$this->element['coursefield'];
            if (!$courseField) {
                OscampusFactory::getApplication()->enqueueMessage('Missing course ID field link', 'error');
                $this->lessons = '';

            } else {
                $courseId = (int)$this->form->getField($courseField)->value;

                $query = $db->getQuery(true)
                    ->select(
                        array(
                            'lesson.id',
                            'lesson.title',
                            'module.title AS ' . $db->quoteName('module_title')
                        )
                    )
                    ->from('#__oscampus_lessons AS lesson')
                    ->innerJoin('#__oscampus_modules AS module ON module.id = lesson.modules_id')
                    ->where('module.courses_id = ' . $courseId)
                    ->order('module.ordering ASC, lesson.ordering ASC');

                $lessons = $db->setQuery($query)->loadObjectList();

                $this->lessons = array();
                foreach ($lessons as $lesson) {
                    $key = $lesson->module_title;
                    if (empty($this->lessons[$key])) {
                        $this->lessons[$key] = array();
                    }
                    $this->lessons[$key][] = HTMLHelper::_('select.option', $lesson->id, $lesson->title);
                }

                $this->lessons = array_merge(
                    array(
                        array(
                            HTMLHelper::_('select.option', '', Text::_('COM_OSCAMPUS_OPTION_SELECT_FILE_LESSON'))
                        )
                    ),
                    $this->lessons
                );
            }
        }

        if ($this->lessons) {
            $options = array(
                'id'                 => null,
                'list.attr'          => null,
                'list.select'        => $selected,
                'group.items'        => null,
                'option.key.toHtml'  => false,
                'option.text.toHtml' => false
            );

            return HTMLHelper::_('select.groupedlist', $this->lessons, $this->name . '[lessons_id][]', $options);
        }

        return '';
    }

    /**
     * Get consistently formatted array of files from $this->value
     *
     * @return object[]
     */
    protected function getFilesFromValue()
    {
        $files = (array)$this->value ?: array(null);

        // If we've come back from a form failure, we need to reformat
        if (isset($files['id'])) {
            $values = $files;
            $files  = array();
            foreach ($values['id'] as $index => $id) {
                $file = array();
                foreach ($values as $fieldName => $fieldValues) {
                    $file[$fieldName] = $fieldValues[$index];
                }
                $files[] = (object)$file;
            }
        }

        return $files;
    }
}

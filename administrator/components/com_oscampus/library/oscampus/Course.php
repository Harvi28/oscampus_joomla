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

namespace Oscampus;

use JComponentHelper;
use JFolder;
use JHtml;
use OscampusFactory;

defined('_JEXEC') or die();

class Course extends AbstractBase
{
    public const BEGINNER     = 'beginner';
    public const INTERMEDIATE = 'intermediate';
    public const ADVANCED     = 'advanced';

    protected static $defaultImage = 'com_oscampus/default-course.jpg';

    /**
     * @var object[]
     */
    protected static $courses = [];

    protected static $filePath = null;

    /**
     * @param int $id
     *
     * @return object
     */
    public function get($id)
    {
        $id = (int)$id;

        if (!isset(static::$courses[$id])) {
            $query = $this->dbo->getQuery(true)
                ->select('*')
                ->from('#__oscampus_courses')
                ->where('id = ' . $id);

            static::$courses[$id] = $this->dbo->setQuery($query)->loadObject();
        }

        return static::$courses[$id];
    }

    /**
     * Creates a snapshot of the selected course
     *
     * @TODO: experimental
     *
     * @param int $courseId
     *
     * @return null|object
     */
    public function snapshot($courseId)
    {
        $query = $this->dbo->getQuery(true)
            ->select(
                array(
                    'course.id',
                    'course.difficulty',
                    'course.length',
                    'course.title',
                    'course.publish_up'
                )
            )
            ->from('#__oscampus_courses AS course')
            ->where('course.id = ' . (int)$courseId);

        if ($course = $this->dbo->setQuery($query)->loadObject()) {
            $query = $this->dbo->getQuery(true)
                ->select(
                    array(
                        'lesson.id',
                        'module.title AS module_title',
                        'lesson.title',
                        'lesson.type'
                    )
                )
                ->from('#__oscampus_lessons AS lesson')
                ->innerJoin('#__oscampus_modules AS module ON module.id = lesson.modules_id')
                ->where('module.courses_id = ' . (int)$courseId)
                ->order('module.ordering ASC, lesson.ordering ASC');

            $course->lessons = $this->dbo->setQuery($query)->loadObjectList('id');

            return $course;
        }

        return null;
    }

    /**
     * @param int          $id
     * @param string       $alt
     * @param string|array $attribs
     * @param bool         $pathOnly
     *
     * @return string
     */
    public static function getImage($id = null, $alt = null, $attribs = null, $pathOnly = true)
    {
        if ($id) {
            if ($course = OscampusFactory::getContainer()->course->get($id)) {
                if ($course->image) {
                    $image = $course->image;
                }
            }
        }

        if (empty($image)) {
            $image    = static::$defaultImage;
            $relative = $image[0] !== '/';

        } else {
            $relative = false;
        }

        $image = JHtml::_('image', $image, $alt, $attribs, $relative, $pathOnly);

        return $image;
    }

    /**
     * get a relative path to uploaded file assets for courses/lessons
     *
     * @param string $fileName
     *
     * @return string
     */
    public static function getFilePath($fileName = null)
    {
        if (static::$filePath === null) {
            $filePath = JComponentHelper::getParams('com_media')->get('file_path');

            static::$filePath = rtrim($filePath, '\\/') . '/oscampus/files';

            $fullPath = JPATH_SITE . '/' . static::$filePath;
            if (!is_dir($fullPath)) {
                jimport('joomla.filesystem.folder');
                JFolder::create($fullPath);
            }
        }

        return static::$filePath . ($fileName ? '/' . $fileName : '');
    }
}

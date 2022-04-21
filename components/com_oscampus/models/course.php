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

use Joomla\Registry\Registry;
use \Joomla\CMS\Factory;
use Oscampus\Course;
use Oscampus\File;
use Oscampus\Lesson\Properties;
use Oscampus\UserActivity;

defined('_JEXEC') or die();

class OscampusModelCourse extends OscampusModelSite
{
    /**
     * @var UserActivity
     */
    protected $activity = null;

    /**
     * @var array
     */
    protected $cache = array();

    /**
     * OscampusModelCourse constructor.
     *
     * @param array $config
     *
     * @return void
     * @throws Exception
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        $this->activity = OscampusFactory::getContainer()->activity;
    }

    /**
     * @return object
     * @throws Exception
     */
    public function getCourse()
    {
        if (empty($this->cache['course'])) {
            $db = $this->getDbo();

            $query = $db->getQuery(true)
                ->select(
                    array(
                        'course.*',
                        'GROUP_CONCAT(tags.title) AS tags'
                    )
                )
                ->from('#__oscampus_courses AS course')
                ->leftJoin('#__oscampus_courses_tags AS ct ON ct.courses_id = course.id')
                ->leftJoin('#__oscampus_tags AS tags ON tags.id = ct.tags_id')
                ->where(
                    array(
                        'course.id = ' . (int)$this->getState('course.id'),
                        'course.published = 1',
                        $this->whereAccess('course.access'),
                        'course.publish_up <= UTC_TIMESTAMP()',
                        'IFNULL(course.publish_down, UTC_TIMESTAMP()) >= UTC_TIMESTAMP()'
                    )
                )
                ->group('course.id');

            $course = $db->setQuery($query)->loadObject();
            if (!$course) {
                throw new Exception(JText::_('COM_OSCAMPUS_ERROR_COURSE_NOT_FOUND'), 404);
            }

            if (empty($course->image)) {
                $course->image = Course::getImage();
            }
            $course->metadata    = new Registry($course->metadata);
            $course->description = JHtml::_('content.prepare', $course->description);
            $course->tags        = array_filter(explode(',', $course->tags));

            $this->cache['course'] = $course;
        }

        return $this->cache['course'];
    }

    /**
     * Teacher information for currently selected course
     *
     * @return object
     */
    public function getTeacher()
    {
        if (empty($this->cache['teacher'])) {
            $db  = OscampusFactory::getDbo();
            $cid = (int)$this->getState('course.id');

            $query = $db->getQuery(true)
                ->select(
                    array(
                        'teacher.*',
                        'user.username',
                        'user.name',
                        'user.email'
                    )
                )
                ->from('#__oscampus_teachers AS teacher')
                ->innerJoin('#__oscampus_courses AS course ON course.teachers_id = teacher.id')
                ->leftJoin(('#__users AS user ON user.id = teacher.users_id'))
                ->where('course.id = ' . $cid);

            if ($teacher = $db->setQuery($query)->loadObject()) {
                $teacher->links = json_decode($teacher->links);

                // Get other courses for this teacher
                $queryCourses = $db->getQuery(true)
                    ->select('course.*')
                    ->from('#__oscampus_teachers AS teacher')
                    ->innerJoin('#__oscampus_courses AS course ON course.teachers_id = teacher.id')
                    ->where(
                        array(
                            'course.published = 1',
                            $this->whereAccess('course.access'),
                            'course.publish_up <= UTC_TIMESTAMP()',
                            'course.id != ' . $cid,
                            'teacher.id = ' . $teacher->id,
                        )
                    )
                    ->group('course.id')
                    ->order('course.title ASC');

                $teacher->courses = $db->setQuery($queryCourses)->loadObjectList();
            }

            $this->cache['teacher'] = $teacher;
        }

        return $this->cache['teacher'];
    }

    /**
     * Get all additional file downloads for this course
     *
     * @return File[]
     */
    public function getFiles()
    {
        if (empty($this->cache['files'])) {
            $files = array();

            $cid = $this->getState('course.id');
            if ($cid > 0) {
                $db = OscampusFactory::getDbo();

                $query = $db->getQuery(true)
                    ->select(
                        array(
                            'file.id',
                            'file.path',
                            'GROUP_CONCAT(file.title) AS title',
                            'file.description'
                        )
                    )
                    ->from('#__oscampus_files AS file')
                    ->innerJoin('#__oscampus_courses AS course ON course.id = file.courses_id')
                    ->leftJoin('#__oscampus_lessons AS lesson ON lesson.id = file.lessons_id')
                    ->where(
                        array(
                            'file.courses_id = ' . $cid,
                            $this->whereAccess('course.access'),
                            sprintf('(%s OR lesson.id IS NULL)', $this->whereAccess('lesson.access'))
                        )
                    )
                    ->order('file.ordering ASC, file.title ASC')
                    ->group('file.path');

                $files = $db->setQuery($query)->loadObjectList(null, '\\Oscampus\\File');

                // Cleanup for possible multiple titles on same file
                array_walk($files, function ($file) {
                    $fixedTitle = array_unique(
                        array_map(
                            'trim',
                            explode(',', $file->title)
                        )
                    );

                    $file->title = array_shift($fixedTitle);
                    if ($fixedTitle) {
                        $file->title .= ' (' . join(', ', $fixedTitle) . ')';
                    }
                });
            }

            $this->cache['files'] = $files;
        }

        return $this->cache['files'];
    }

    /**
     * Get lessons for the currently selected course
     *
     * @return array
     */
    public function getLessons()
    {
        if (empty($this->cache['lessons'])) {
            $db      = $this->getDbo();
            $cid     = (int)$this->getState('course.id');
            $lessons = array();

            if ($cid > 0) {
                $query = $db->getQuery(true)
                    ->select(
                        array(
                            'module.courses_id',
                            'lesson.modules_id',
                            'module.title AS module_title',
                            'lesson.*'
                        )
                    )
                    ->from('#__oscampus_modules AS module')
                    ->innerJoin('#__oscampus_lessons AS lesson ON lesson.modules_id = module.id')
                    ->where([
                        'module.courses_id = ' . (int)$this->getState('course.id'),
                        'lesson.published = 1',
                        'IFNULL(lesson.publish_up, UTC_TIMESTAMP()) <= UTC_TIMESTAMP()',
                        'IFNULL(lesson.publish_down, UTC_TIMESTAMP()) >= UTC_TIMESTAMP()'
                    ])
                    ->order('module.ordering ASC, lesson.ordering ASC');

                $list = $db->setQuery($query)->loadObjectList();

                $module = (object)array(
                    'id'      => null,
                    'title'   => null,
                    'lessons' => array()
                );
                foreach ($list as $index => $lesson) {
                    if ($lesson->modules_id != $module->id) {
                        if ($module->lessons) {
                            $lessons[] = clone $module;
                        }
                        $module->id      = $lesson->modules_id;
                        $module->title   = $lesson->module_title;
                        $module->lessons = array();
                    }

                    $lesson->index     = $index;
                    $module->lessons[] = new Properties($lesson);
                }
                if ($module->lessons) {
                    $lessons[] = clone $module;
                }
            }

            $this->cache['lessons'] = $lessons;
        }

        return $this->cache['lessons'];
    }

    /**
     * Get lesson viewed info for the selected user in the currently selected course
     *
     * @return array
     * @throws Exception
     */
    public function getViewedLessons()
    {
        if (empty($this->cache['viewed'])) {
            $uid = (int)$this->getState('user.id');
            $cid = (int)$this->getState('course.id');

            if ($uid > 0 && $cid > 0) {
                $this->activity->setUser($uid);
                $viewed = $this->activity->getCourseLessons($cid);
            }

            $this->cache['viewed'] = empty($viewed) ? array() : $viewed;
        }

        return $this->cache['viewed'];
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function populateState()
    {
        $app = OscampusFactory::getApplication();

        $cid = $app->input->getInt('cid');
        $this->setState('course.id', $cid);

        $uid = $app->input->getInt('uid', OscampusFactory::getUser()->id);
        $this->setState('user.id', $uid);
    }
}

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

use Oscampus\File;
use Oscampus\Lesson;
use Oscampus\UserActivity;

defined('_JEXEC') or die();

class OscampusModelLesson extends OscampusModelSite
{
    /**
     * @var UserActivity
     */
    protected $activity = null;

    /**
     * OscampusModelLesson constructor.
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
     * @var Lesson
     */
    protected $lesson = null;

    /**
     * @return Lesson
     * @throws Exception
     */
    public function getItem()
    {
        if ($this->lesson === null) {
            $this->lesson = OscampusFactory::getContainer()->lesson;

            if ($lessonId = (int)$this->getState('lesson.id')) {
                $this->lesson->loadById($lessonId);

            } else {
                $courseId = (int)$this->getState('course.id');
                $index    = (int)$this->getState('lesson.index');

                $this->lesson->loadByIndex($index, $courseId);
            }

            if ($uid = (int)$this->getState('user.id')) {
                $this->activity->setUser($uid);
                $this->activity->visitLesson($this->lesson);
            }
        }

        if (!$this->lesson->id) {
            throw new Exception(JText::_('COM_OSCAMPUS_ERROR_LESSON_NOT_FOUND', 404));
        }

        return $this->lesson;
    }

    /**
     * The user activity record for this lesson
     *
     * @return object
     * @throws Exception
     */
    public function getLessonStatus()
    {
        $lesson = $this->getItem();
        return $this->activity->getLessonStatus($lesson->id);
    }

    /**
     * @return File[]
     * @throws Exception
     */
    public function getFiles()
    {
        if ($this->getItem()->isAuthorised()) {
            $courseId = (int)$this->getState('course.id');
            $lessonId = (int)$this->getState('lesson.id');

            if ($courseId && $lessonId) {
                $db    = $this->getDbo();
                $query = $db->getQuery(true)
                    ->select(
                        array(
                            'file.id',
                            'file.path',
                            'file.title',
                            'file.description'
                        )
                    )
                    ->from('#__oscampus_files AS file')
                    ->where(
                        array(
                            'file.courses_id = ' . $courseId,
                            'file.lessons_id = ' . $lessonId
                        )
                    )
                    ->order('file.ordering ASC, file.title ASC');

                $files = $db->setQuery($query)->loadObjectList(null, '\\Oscampus\\File');
                foreach ($files as $file) {
                    if ($file->description) {
                        $file->description = JHtml::_('content.prepare', $file->description);
                    }
                }

                return $files;
            }
        }

        return array();
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

        if ($lid = $app->input->getInt('lid')) {
            $this->setState('lesson.id', $lid);

        } else {
            $index = $app->input->getInt('index');
            $this->setState('lesson.index', $index);
        }

        $uid = $app->input->getInt('uid', OscampusFactory::getUser()->id);
        $this->setState('user.id', $uid);
    }
}

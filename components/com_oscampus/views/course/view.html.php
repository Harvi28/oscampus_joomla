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

defined('_JEXEC') or die();

class OscampusViewCourse extends OscampusViewSite
{
    /**
     * @var object
     */
    protected $course = null;

    /**
     * @var object
     */
    protected $teacher = null;

    /**
     * @var Lesson[]
     */
    protected $lessons = array();

    /**
     * @var File[]
     */
    protected $files = array();

    /**
     * @var object[]
     */
    protected $viewed = array();

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function display($tpl = null)
    {
        try {
            /** @var OscampusModelCourse $model */
            $model = $this->getModel();

            $this->course = $model->getCourse();
            if ($this->course) {
                $this->teacher = $model->getTeacher();
                $this->lessons = $model->getLessons();
                $this->files   = $model->getFiles();
                $this->viewed  = $model->getViewedLessons();
            }

            $this->setMetadata(
                $this->course->metadata,
                $this->course->title,
                $this->course->introtext ?: $this->course->description
            );

        } catch (Throwable $e) {
            if ($e->getCode() == 404) {
                $this->setLayout('notfound');
            } else {
                throw $e;
            }
        }

        parent::display($tpl);
    }
}

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

use Oscampus\Activity\LessonStatus;
use Oscampus\File;
use Oscampus\Lesson;
use Oscampus\Lesson\Type\Quiz;

defined('_JEXEC') or die();

class OscampusViewLesson extends OscampusViewSite
{
    /**
     * @var OscampusModelLesson
     */
    protected $model = null;

    /**
     * @var Lesson
     */
    protected $lesson = null;

    /**
     * @var File[]
     */
    protected $files = [];

    /**
     * @var LessonStatus
     */
    protected $activity = null;

    /**
     * @var Quiz
     */
    protected $quiz = null;

    /**
     * @param string $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $app = OscampusFactory::getApplication();

        try {
            $this->model    = $this->getModel();
            $this->lesson   = $this->model->getItem();
            $this->quiz     = $this->lesson->renderer;
            $this->files    = $this->model->getFiles();
            $this->activity = $this->model->getLessonStatus();

            $pathway = OscampusFactory::getApplication()->getPathway();

            $link = JHtml::_('osc.link.course', $this->lesson->courses_id, null, null, true);
            $pathway->addItem($this->lesson->courseTitle, $link);

            $pathway->addItem($this->lesson->title);

            $layout = $app->input->getCmd('layout') ?: $this->lesson->type;
            $this->setLayout($layout);

            $this->setMetadata(
                $this->lesson->metadata,
                $this->lesson->title . ' - ' . $this->lesson->courseTitle,
                $this->lesson->description
            );

            parent::display($tpl);

            if (!$this->lesson->isAuthorised()) {
                echo $this->loadDefaultTemplate('noauth');
            }

        } catch (Throwable $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
        }
    }

    /**
     * @param string $base
     *
     * @return string
     */
    protected function getPageClass($base = '')
    {
        $lessonBase = [
            'osc-container'
        ];
        if ($this->lesson) {
            $lessonBase[] = 'oscampus-' . $this->lesson->type;
        }
        if ($base) {
            $lessonBase[] = trim($base);
        }

        return parent::getPageClass(join(' ', $lessonBase));
    }

    /**
     * @param string $extras
     *
     * @return string
     */
    protected function getContentClass($extras = '')
    {
        $classes = [
            'osc-section',
            'oscampus-lesson-content',
            $this->lesson->isAuthorised() ? 'osc-authorised-box' : 'osc-signup-box'
        ];
        if ($extras) {
            $classes[] = trim($extras);
        }

        return join(' ', $classes);
    }
}

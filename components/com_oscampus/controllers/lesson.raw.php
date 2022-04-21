<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2018-2021 Joomlashack.com. All rights reserved
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

class OscampusControllerLesson extends OscampusControllerBase
{
    /**
     * @return void
     * @throws Exception
     */
    public function download()
    {
        $this->checkToken('request');

        try {
            $app = OscampusFactory::getApplication();

            $lessonId = $app->input->getInt('lessonId');
            if (!$lessonId) {
                throw new Exception(JText::sprintf('COM_OSCAMPUS_ERROR_MISSING_ARG', 'lessonId'), 500);
            }

            $lesson = OscampusFactory::getContainer()->lesson->loadById($lessonId);
            if (!$lesson) {
                throw new Exception(JText::_('COM_OSCAMPUS_ERROR_LESSON_NOT_FOUND'));
            }

            $lesson->sendDownload();

            // If the download succeeded, we shouldn't be here!
            throw new Exception(JText::_('COM_OSCAMPUS_ERROR_DOWNLOAD_FAILED'), 500);

        } catch (Throwable $e) {
            $this->errorReturn($e->getMessage());
        }

    }
}

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

defined('_JEXEC') or die();

class OscampusControllerQuiz extends OscampusControllerBase
{
    public function display($cachable = false, $urlparams = array())
    {
        echo $this->getTask() . ' is under construction';
    }

    /**
     * @return void
     * @throws Exception
     */
    public function grade()
    {
        $this->checkToken();

        $app       = OscampusFactory::getApplication();
        $container = OscampusFactory::getContainer();

        if ($lessonId = $app->input->getInt('lid')) {
            $responses = $app->input->get('questions', array(), 'array');

            $lesson   = $container->lesson->loadById($lessonId);
            $activity = $container->activity;

            $activity->recordProgress($lesson, 0, $responses);

            $app->redirect(JURI::getInstance());
        }
    }
}

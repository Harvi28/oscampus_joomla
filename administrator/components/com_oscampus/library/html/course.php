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

abstract class OscCourse
{
    /**
     * @param object $course
     * @param string $text
     * @param mixed  $attribs
     * @param bool   $uriOnly
     *
     * @return string
     * @throws Exception
     */
    public static function link($course, $text = null, $attribs = null, $uriOnly = false)
    {
        $query        = OscampusRoute::getInstance()->getQuery('course');
        $query['cid'] = isset($course->courses_id) ? $course->courses_id : $course->id;

        $link = 'index.php?' . http_build_query($query);

        if ($uriOnly) {
            return $link;
        }

        return JHtml::_('link', JRoute::_($link), $text ?: $course->title, $attribs);
    }

    /**
     * Translate a difficulty code to text
     *
     * @param string $value
     *
     * @return string
     */
    public static function difficulty($value)
    {
        $difficulties = JHtml::_('osc.options.difficulties');

        foreach ($difficulties as $difficulty) {
            if (!strcasecmp($difficulty->value, $value)) {
                return $difficulty->text;
            }
        }

        return $value . ': ' . JText::_('COM_OSCAMPUS_UNDEFINED');
    }

    /**
     * Generate the start button for a course
     *
     * @param object $course
     *
     * @return string
     * @throws Exception
     */
    public static function startbutton($course)
    {
        if (empty($course->lessons_viewed)) {
            $icon    = 'fa-play';
            $text    = JText::_('COM_OSCAMPUS_START_THIS_CLASS');
            $attribs = 'class="osc-btn osc-btn-main"';

        } elseif (!empty($course->date_earned)) {
            $icon    = 'fa-repeat';
            $text    = JText::_('COM_OSCAMPUS_WATCH_THIS_CLASS_AGAIN');
            $attribs = 'class="osc-btn osc-btn-active"';

        } else {
            $icon    = 'fa-step-forward';
            $text    = JText::_('COM_OSCAMPUS_CONTINUE_THIS_CLASS');
            $attribs = 'class="osc-btn"';
        }

        $button = sprintf('<i class="fa %s"></i> %s', $icon, $text);

        // @TODO: Figure out some way to send them to where they left off
        if (!empty($course->lessons_viewed) && empty($course->certificates_id)) {
            return static::link($course, $button, $attribs);
        }

        return JHtml::_('osc.link.lesson', $course->id, 0, $button, $attribs);
    }

    /**
     * Get the requirements for passing a course
     *
     * @param array $modules
     *
     * @return string
     */
    public static function requirements(array $modules = array())
    {
        $quizCount = 0;
        foreach ($modules as $module) {
            foreach ($module->lessons as $lesson) {
                if ($lesson->type == 'quiz') {
                    $quizCount++;
                }
            }
        }

        $params = OscampusFactory::getContainer()->params;
        if ($quizCount && $quizPassing = $params->get('quizzes.passingScore')) {
            // Because JText::plural doesn't work the way I want it to
            if ($quizCount == 1) {
                return JText::sprintf('COM_OSCAMPUS_CERTIFICATE_REQUIREMENT_QUIZ_1', $quizPassing);
            } else {
                return JText::sprintf('COM_OSCAMPUS_CERTIFICATE_REQUIREMENT_QUIZ', $quizPassing);
            }
        }

        return JText::_('COM_OSCAMPUS_CERTIFICATE_REQUIREMENT');
    }
}

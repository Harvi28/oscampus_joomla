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
use Oscampus\Lesson;
use Oscampus\Request;
use Oscampus\Lesson\Properties;

defined('_JEXEC') or die();

abstract class OscLesson
{
    protected static $statusIcons = array(
        'viewed'      => 'fa-check',
        'in-progress' => 'fa-spinner',
        'failed'      => 'fa-times',
        'passed'      => 'fa-check'

    );

    /**
     * Return the free indicator for guest users
     *
     * @param Properties|Lesson $lesson
     *
     * @return string
     */
    public static function freeflag($lesson)
    {
        $user         = OscampusFactory::getUser();
        $showFreeflad = OscampusFactory::getContainer()->params->get('lesson.showFreeflag', true);

        if ($user->guest && $showFreeflad) {
            if ($lesson->isAuthorised()) {
                return '<span class="osc-free-tag">' . JText::_('COM_OSCAMPUS_LESSON_FREE') . '</span>';
            }
        }

        return '';
    }

    /**
     * Return the label indicator for each lesson
     *
     * @param Lesson|Properties $lesson
     *
     * @return string
     */
    public static function icon($lesson)
    {
        $classes = array(
            'osc-lesson-tag',
        );

        if ($lesson->isAuthorised()) {
            $classes[] = 'osc-lesson-tag-access';
            $icon      = $lesson->icon;

        } else {
            $classes[] = 'osc-lesson-tag-noaccess';
            $icon      = 'fa-lock';
        }

        return sprintf(
            '<span class="%s"><i class="fa %s"></i></span>',
            join(' ', $classes),
            $icon
        );
    }

    /**
     * @param Properties|Lesson $lesson
     * @param string            $text
     * @param mixed             $attribs
     * @param bool              $uriOnly
     *
     * @return string
     * @throws Exception
     */
    public static function link($lesson, $text = null, $attribs = null, $uriOnly = false)
    {
        if ($query = static::linkQuery($lesson)) {
            $link = JRoute::_('index.php?' . http_build_query($query));

            if ($uriOnly) {
                return $link;
            }

            if (!$attribs || is_string($attribs)) {
                $attribs = JUtility::parseAttributes($attribs);
            }

            if (empty($attribs['class'])) {
                $attribs['class'] = '';
            }
            $classes          = array_filter(explode(' ', $attribs['class']));
            $classes[]        = $lesson->isAuthorised() ? 'osc-lesson-access' : 'osc-lesson-noaccess';
            $attribs['class'] = join(' ', array_unique($classes));

            return JHtml::_('link', JRoute::_($link), $text ?: $lesson->title, $attribs);
        }

        return null;
    }

    /**
     * Translate a lesson type code into text
     *
     * @param string $value
     *
     * @return string
     */
    public static function type($value)
    {
        $types = JHtml::_('osc.options.lessontypes');

        foreach ($types as $type) {
            if (!strcasecmp($type->value, $value)) {
                return $type->text;
            }
        }

        return JText::_('COM_OSCAMPUS_UNDEFINED');
    }

    /**
     * Setup js for lesson navigation handling
     *
     * @param Lesson $lesson
     *
     * @return void
     * @throws Exception
     */
    public static function navigation(Lesson $lesson)
    {
        $lessons = array(
            'previous' => $lesson->previous,
            'current'  => $lesson->current,
            'next'     => $lesson->next
        );

        foreach ($lessons as $key => $properties) {
            $options = new Properties($properties);

            unset($options->content);
            $options->link = JRoute::_(static::link($properties, null, null, true));

            $lessons[$key] = $options;
        }

        $lessons = json_encode($lessons);

        JText::script('COM_OSCAMPUS_LESSON_LOADING_NEXT');
        JText::script('COM_OSCAMPUS_LESSON_LOADING_PREVIOUS');
        JText::script('COM_OSCAMPUS_LESSON_LOADING_TITLE');

        /**
         * Check if we called the lesson from a normal request or ajax request.
         * An ajax request needs to print the JS right in the output, without
         * passing to the document. Otherwise it won't be added to the output.
         */
        if (!Request::isAjax()) {
            JHtml::_('osc.jquery');
            JHtml::_('script', 'com_oscampus/lesson.min.js', array('relative' => true));
        }

        JHtml::_('osc.onready', "$.Oscampus.lesson.navigation({$lessons});", Request::isAjax());
    }

    /**
     * @param LessonStatus $status
     *
     * @return string
     */
    public static function status(LessonStatus $status)
    {
        $statusId = $status->getId();

        return sprintf(
            '<span class="osc-label osc-label-small osc-label-%s"><i class="fa %s"></i> %s</span>',
            $statusId,
            static::$statusIcons[$statusId],
            JText::_('COM_OSCAMPUS_LESSON_' . str_replace('-', '_', $statusId))
        );
    }

    /**
     * Create the link to retry a quiz
     *
     * @param Properties|Lesson $lesson
     * @param string            $text
     * @param string|array      $attribs
     * @param bool              $uriOnly
     *
     * @return string
     * @throws Exception
     */
    public static function retrylink($lesson, $text, $attribs = null, $uriOnly = false)
    {
        $query         = static::linkQuery($lesson);
        $query['task'] = 'lesson.retry';

        $link = 'index.php?' . http_build_query($query);
        if ($uriOnly) {
            return $link;
        }

        return JHtml::_('link', JRoute::_($link), $text ?: $lesson->title, $attribs);
    }

    /**
     * Get the base url query the lesson
     *
     * @param Properties|Lesson $lesson
     *
     * @return array|null
     * @throws Exception
     */
    protected static function linkQuery($lesson)
    {
        if ($lesson instanceof Lesson) {
            $properties = $lesson->current;
        } elseif ($lesson instanceof Properties) {
            $properties = $lesson;
        } else {
            throw new Exception(
                JText::sprintf(
                    'COM_OSCAMPUS_ERROR_ARGUMENT_INVALID',
                    __CLASS__,
                    __METHOD__,
                    getType($lesson)
                )
            );
        }

        if (!$properties->id) {
            return null;
        }

        $query = OscampusRoute::getInstance()->getQuery('course');

        $query['view'] = 'lesson';
        $query['cid']  = $properties->courses_id;
        $query['lid']  = $properties->id;

        return $query;
    }
}

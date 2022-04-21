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

use Oscampus\Activity\CourseStatus;
use Oscampus\Course;

defined('_JEXEC') or die();

abstract class OscOptions
{
    protected static $cache = array();

    /**
     * Get option list of course difficulty levels
     *
     * @return object[]
     */
    public static function difficulties()
    {
        if (empty(static::$cache['difficulties'])) {
            static::$cache['difficulties'] = array(
                JHtml::_(
                    'select.option',
                    Course::BEGINNER,
                    JText::_('COM_OSCAMPUS_COURSE_DIFFICULTY_BEGINNER')
                ),
                JHtml::_(
                    'select.option',
                    Course::INTERMEDIATE,
                    JText::_('COM_OSCAMPUS_COURSE_DIFFICULTY_INTERMEDIATE')
                ),
                JHtml::_(
                    'select.option',
                    Course::ADVANCED,
                    JText::_('COM_OSCAMPUS_COURSE_DIFFICULTY_ADVANCED')
                )
            );
        }

        return static::$cache['difficulties'];
    }

    /**
     * Get option list of known teachers
     *
     * @return object[]
     */
    public static function teachers()
    {
        if (empty(static::$cache['teachers'])) {
            $db = OscampusFactory::getDbo();
            $db->escape('');

            $query = $db->getQuery(true)
                ->select(
                    array(
                        'teacher.id',
                        'user.username',
                        'user.name'
                    )
                )
                ->from('#__oscampus_teachers teacher')
                ->innerJoin('#__users user ON user.id = teacher.users_id')
                ->order('user.username');

            static::$cache['teachers'] = array_map(
                function ($row) {
                    return JHtml::_('select.option', $row->id, sprintf('%s (%s)', $row->username, $row->name));
                },
                $db->setQuery($query)->loadObjectList()
            );
        }

        return static::$cache['teachers'];
    }

    /**
     * Create options list of available pathways
     *
     * @param bool $coreOnly
     *
     * @return object[]
     */
    public static function pathways($coreOnly = false)
    {
        $key = md5(
            json_encode(
                array(
                    'base' => 'pathways',
                    'core' => $coreOnly
                )
            )
        );

        if (empty(static::$cache[$key])) {
            $db    = OscampusFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('pathway.id, pathway.title, pathway.alias')
                ->from('#__oscampus_pathways pathway')
                ->order('pathway.title ASC, pathway.alias ASC');

            if ($coreOnly) {
                $query->where('pathway.users_id = 0');
            }

            static::$cache[$key] = array_map(
                function ($row) {
                    return (object)array(
                        'value' => $row->id,
                        'text'  => sprintf('%s (%s)', $row->title, $row->alias)
                    );
                },
                $db->setQuery($query)->loadObjectList()
            );

        }

        return static::$cache[$key];
    }

    /**
     * Create option list of available tags
     *
     * @return object[]
     */
    public static function tags()
    {
        if (empty(static::$cache['tags'])) {
            $db    = OscampusFactory::getDbo();
            $query = $db->getQuery(true)
                ->select(
                    array(
                        'tag.id AS ' . $db->quoteName('value'),
                        'tag.title AS ' . $db->quoteName('text')
                    )
                )
                ->from('#__oscampus_tags tag')
                ->order('tag.title ASC');

            static::$cache['tags'] = $db->setQuery($query)->loadObjectList();
        }

        return static::$cache['tags'];
    }

    /**
     * option list of available courses
     *
     * @return object[]
     */
    public static function courses()
    {
        if (empty(static::$cache['courses'])) {
            $db = OscampusFactory::getDbo();

            $query = $db->getQuery(true)
                ->select(
                    array(
                        'id AS ' . $db->qn('value'),
                        'title AS ' . $db->qn('text')
                    )
                )
                ->from('#__oscampus_courses')
                ->order('title ASC');

            static::$cache['courses'] = $db->setQuery($query)->loadObjectList();
        }

        return static::$cache['courses'];
    }

    /**
     * Option list of available Lesson Types
     *
     * @TODO: This should not be hardcoded and needs to use the eventual lesson classes to get its list
     *
     * @return object[]
     */
    public static function lessontypes()
    {
        $options = array(
            JHtml::_(
                'select.option',
                'quiz',
                JText::_('COM_OSCAMPUS_LESSON_TYPE_QUIZ')
            ),
            JHtml::_(
                'select.option',
                'text',
                JText::_('COM_OSCAMPUS_LESSON_TYPE_TEXT')
            ),
            JHtml::_(
                'select.option',
                'wistia',
                JText::_('COM_OSCAMPUS_LESSON_TYPE_WISTIA')
            ),
            JHtml::_(
                'select.option',
                'vimeo',
                JText::_('COM_OSCAMPUS_LESSON_TYPE_VIMEO')
            ),
            JHtml::_(
                'select.option',
                'embed',
                JText::_('COM_OSCAMPUS_LESSON_TYPE_EMBED')
            )
        );

        return $options;
    }

    /**
     * Generate option list of pathway owners
     *
     * @return object[]
     */
    public static function pathwayowners()
    {
        if (!isset(static::$cache['pathwayOwners'])) {
            $db    = OscampusFactory::getDbo();
            $query = $db->getQuery(true)
                ->select('user.id, user.name, user.username')
                ->from('#__users AS user')
                ->innerJoin('#__oscampus_pathways AS pathway ON pathway.users_id = user.id')
                ->group('user.id')
                ->order('user.name ASC');

            $users = $db->setQuery($query)->loadObjectList();

            $text = '%s (%s)';

            static::$cache['pathwayOwners'] = array();
            foreach ($users as $user) {
                static::$cache['pathwayOwners'][] = JHtml::_(
                    'select.option',
                    $user->id,
                    sprintf($text, $user->name, $user->username)
                );
            }
        }

        return static::$cache['pathwayOwners'];
    }

    /**
     * Generate list of module options for selected course
     *
     * @param int $courseId
     *
     * @return string
     */
    public static function modules($courseId)
    {
        $key = md5('module-' . $courseId);
        if (!isset(static::$cache[$key])) {
            $db    = OscampusFactory::getDbo();
            $query = $db->getQuery(true)
                ->select(
                    array(
                        'module.id AS ' . $db->qn('value'),
                        'module.title AS ' . $db->qn('text')
                    )
                )
                ->from('#__oscampus_modules AS module')
                ->where('module.courses_id =' . (int)$courseId)
                ->order('module.ordering');

            static::$cache[$key] = $db->setQuery($query)->loadObjectList();
        }

        return static::$cache[$key];
    }

    /**
     * Course progress statuses
     *
     * @return object[]
     */
    public static function progress()
    {
        $options = array(
            JHtml::_(
                'select.option',
                CourseStatus::NOT_STARTED,
                JText::_('COM_OSCAMPUS_OPTION_COURSE_NOT_STARTED')
            ),
            JHtml::_(
                'select.option',
                CourseStatus::IN_PROGRESS,
                JText::_('COM_OSCAMPUS_OPTION_COURSE_IN_PROGRESS')
            ),
            JHtml::_(
                'select.option',
                CourseStatus::COMPLETED,
                JText::_('COM_OSCAMPUS_OPTION_COURSE_COMPLETED')
            )
        );

        return $options;
    }

    public static function certificates()
    {
        $db = OscampusFactory::getDbo();

        $query = $db->getQuery(true)
            ->select([
                'id AS' . $db->quoteName('value'),
                'title AS ' . $db->quoteName('text')
            ])
            ->from('#__oscampus_certificates');

        return $db->setQuery($query)->loadObjectList();
    }

    /**
     * Standard method for retrieving access levels
     *
     * @return string
     */
    public static function access()
    {
        return JHtml::_('access.assetgroups');
    }
}

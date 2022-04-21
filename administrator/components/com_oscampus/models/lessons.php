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

class OscampusModelLessons extends OscampusModelAdminList
{
    public function __construct($config = [])
    {
        $config['filter_fields'] = [
            'access',
            'course',
            'course.published',
            'course.title',
            'lesson.id',
            'lesson.ordering',
            'lesson.published',
            'lesson.title',
            'lesson.type',
            'lesson_view.title',
            'lessontype',
            'module.title',
            'search',
        ];

        parent::__construct($config);

        $app = OscampusFactory::getApplication();

        if ($context = $app->input->getCmd('context')) {
            $this->context .= '.' . $context;
        }
    }

    protected function getListQuery()
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->select([
                'lesson.id',
                'lesson.modules_id',
                'module.courses_id',
                'module.ordering AS module_ordering',
                'lesson.ordering',
                'lesson.title',
                'lesson.alias',
                'lesson.type',
                'lesson.published',
                'lesson.publish_up',
                'lesson.publish_down',
                'lesson.checked_out',
                'lesson_view.title AS viewlevel_title',
                'module.title AS module_title',
                'course.title AS course_title',
                'course.published AS course_published',
                'course.publish_up AS course_released',
                'course.difficulty AS course_difficulty',
                'editor_user.name AS editor'
            ])
            ->from('#__oscampus_lessons lesson')
            ->leftJoin('#__oscampus_modules AS module ON module.id = lesson.modules_id')
            ->leftJoin('#__oscampus_courses AS course ON course.id = module.courses_id')
            ->leftJoin('#__viewlevels AS lesson_view ON lesson_view.id = lesson.access')
            ->leftJoin('#__viewlevels AS course_view ON course_view.id = course.access')
            ->leftJoin('#__users AS editor_user ON editor_user.id = lesson.checked_out');

        if ($search = $this->getState('filter.search')) {
            $fields = ['lesson.title', 'lesson.alias'];
            $query->where($this->whereTextSearch($search, $fields, 'lesson.id'));
        }

        $published = $this->getState('filter.published');
        if ($published != '') {
            $query->where('lesson.published = ' . (int)$published);
        }

        if ($course = (int)$this->getState('filter.course')) {
            $query->where('course.id = ' . $course);
        }

        if ($lessonType = $this->getState('filter.lessontype')) {
            $query->where('lesson.type = ' . $db->q($lessonType));
        }

        if ($access = (int)$this->getState('filter.access')) {
            $query->where('lesson.access = ' . $access);
        }

        $primary   = $this->getState('list.ordering', 'course.title');
        $direction = $this->getState('list.direction', 'ASC');
        switch ($primary) {
            case 'lesson.ordering':
                $query->order([
                    'course.title ' . $direction,
                    'course.id ' . $direction,
                    'module.ordering ' . $direction,
                    'lesson.ordering ' . $direction
                ]);
                break;

            case 'lesson.title':
            case 'lesson.id':
                $query->order($primary . ' ' . $direction);
                break;

            default:
                $query->order([
                    $primary . ' ' . $direction,
                    'lesson.title ' . $direction
                ]);
                break;
        }

        if (!in_array($primary, ['lesson.title', 'lesson.id', 'lesson.ordering'])) {
            $query->order('lesson.title ' . $direction);
        }

        return $query;
    }

    protected function populateState($ordering = 'lesson.title', $direction = 'ASC')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published');
        $this->setState('filter.published', $published);

        $course = $this->getUserStateFromRequest($this->context . '.filter.course', 'filter_course', null, 'int');
        $this->setState('filter.course', $course);

        $lessonType = $this->getUserStateFromRequest(
            $this->context . '.filter.lessontype',
            'filter_lessontype',
            '',
            'string'
        );
        $this->setState('filter.lessontype', $lessonType);

        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', null, 'int');
        $this->setState('filter.access', $access);

        parent::populateState($ordering, $direction);
    }
}

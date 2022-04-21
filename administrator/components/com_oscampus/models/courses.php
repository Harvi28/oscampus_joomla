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


class OscampusModelCourses extends OscampusModelAdminList
{
    /**
     * OscampusModelCourses constructor.
     *
     * @param array $config
     *
     * @return void
     * @throws Exception
     */
    public function __construct($config = array())
    {
        $config['filter_fields'] = array(
            'published',
            'pathway',
            'tags',
            'difficulty',
            'access',
            'teacher',
            'course.id',
            'course.title',
            'tag.title',
            'pathway.title',
            'course.difficulty',
            'course.published',
            'viewlevel.title',
            'teacher_user.name',
            'cp.ordering'
        );

        parent::__construct($config);
    }

    protected function getListQuery()
    {
        $db = $this->getDbo();

        $pathwaysQuery = $db->getQuery(true)
            ->select('GROUP_CONCAT(p2.title ORDER BY p2.title)')
            ->from('#__oscampus_pathways AS p2')
            ->innerJoin('#__oscampus_courses_pathways AS cp2 ON cp2.pathways_id = p2.id')
            ->where('cp2.courses_id = course.id');

        $query = $db->getQuery(true);
        $query->select(
            array(
                'course.*',
                'teacher_user.name AS teacher_name',
                'viewlevel.title AS viewlevel_title',
                'GROUP_CONCAT(DISTINCT tag.title ORDER BY tag.title) AS tags',
                "({$pathwaysQuery}) AS pathways",
                'editor_user.name AS editor'
            )
        )
            ->from('#__oscampus_courses course')
            ->leftJoin('#__oscampus_teachers AS teacher ON course.teachers_id = teacher.id')
            ->leftJoin('#__users AS teacher_user ON teacher.users_id = teacher_user.id')
            ->leftJoin('#__viewlevels AS viewlevel ON course.access = viewlevel.id')
            ->leftJoin('#__oscampus_courses_tags AS course_tags ON course.id = course_tags.courses_id')
            ->leftJoin('#__oscampus_tags AS tag ON course_tags.tags_id = tag.id')
            ->leftJoin('#__users AS editor_user ON editor_user.id = course.checked_out');

        if ($search = $this->getState('filter.search')) {
            $fields = array('course.title', 'course.alias');
            $query->where($this->whereTextSearch($search, $fields, 'course.id'));
        }

        $published = $this->getState('filter.published');
        if ($published != '') {
            $query->where('course.published = ' . (int)$published);
        }

        if ($pathway = (int)$this->getState('filter.pathway')) {
            $query->leftJoin('#__oscampus_courses_pathways AS cp ON cp.courses_id = course.id');
            $query->where('cp.pathways_id = ' . $pathway);
        }
        $ordering = $pathway ? 'cp.ordering' : $db->q('---');
        $query->select($ordering . ' AS ordering');

        $tag = $this->getState('filter.tags');
        if (is_numeric($tag) != '') {
            $queryTag = $db->getQuery(true)
                ->select('courses_id')
                ->from('#__oscampus_courses_tags')
                ->where('tags_id  = ' . (int)$tag);

            $query->where("course.id IN ({$queryTag})");

        } elseif ($tag == 'null') {
            $query->where('tag.id IS NULL');
        }

        if ($difficulty = $this->getState('filter.difficulty')) {
            $query->where('course.difficulty = ' . $db->q($difficulty));
        }

        if ($access = (int)$this->getState('filter.access')) {
            $query->where('course.access = ' . $access);
        }

        if ($teacher = (int)$this->getState('filter.teacher')) {
            $query->where('teacher.id = ' . $teacher);
        }

        $query->group('course.id');

        // Set ordering
        $primary   = $this->getState('list.ordering', 'course.title');
        $direction = $this->getState('list.direction', 'ASC');

        // Unless the pathway filter is in place, ordering makes no sense
        if ($primary == 'cp.ordering' && !$pathway) {
            $primary = 'course.title';
            $this->setState('list.ordering', $primary);
        }

        $query->order($primary . ' ' . $direction);
        if (!in_array($primary, array('course.title', 'course.id'))) {
            $query->order('course.title ' . $direction);
        }

        return $query;
    }

    protected function populateState($ordering = 'course.title', $direction = 'ASC')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published');
        $this->setState('filter.published', $published);

        $pathway = $this->getUserStateFromRequest($this->context . '.filter.pathway', 'filter_pathway', null, 'int');
        $this->setState('filter.pathway', $pathway);

        $tag = $this->getUserStateFromRequest($this->context . '.filter.tags', 'filter_tags');
        $this->setState('filter.tags', $tag);

        $difficulty = $this->getUserStateFromRequest($this->context . '.filter.difficulty', 'filter_difficulty');
        $this->setState('filter.difficulty', $difficulty);

        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', null, 'int');
        $this->setState('filter.access', $access);

        $teacher = $this->getUserStateFromRequest($this->context . '.filter.teacher', 'filter_teacher', null, 'int');
        $this->setState('filter.teacher', $teacher);

        parent::populateState($ordering, $direction);
    }
}

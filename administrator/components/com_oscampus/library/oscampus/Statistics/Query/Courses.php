<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2017-2021 Joomlashack.com. All rights reserved
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

namespace Oscampus\Statistics\Query;

defined('_JEXEC') or die();

class Courses extends AbstractQueryBase
{
    /**
     * @inheritDoc
     */
    public function getDefaultOrdering(): string
    {
        return 'title asc';
    }

    /**
     * @inheritDoc
     */
    public function getFilterFields(): array
    {
        return [
            'title',
            'publish_up',
            'students',
            'certificates'
        ];
    }

    /**
     * @inheritDoc
     */
    public function get()
    {
        $db = $this->getDbo();

        $activityQuery = $db->getQuery(true)
            ->select([
                'log.users_id',
                'module.courses_id',
                'COUNT(DISTINCT lesson.id) AS lessons',
                'COUNT(DISTINCT log.users_id) AS students',
                'MAX(log.last_visit) AS last_visit',
                'COUNT(DISTINCT certificate.id) AS certificates'
            ])
            ->from('#__oscampus_modules AS module')
            ->innerJoin('#__oscampus_lessons AS lesson ON lesson.modules_id = module.id AND lesson.published = 1')
            ->innerJoin('#__oscampus_users_lessons AS log ON log.lessons_id = lesson.id')
            ->leftJoin(
                sprintf(
                    '#__oscampus_courses_certificates AS certificate ON %s',
                    join(' AND ', [
                        'certificate.courses_id = module.courses_id',
                        'certificate.users_id = log.users_id'
                    ])
                )
            )
            ->group('module.courses_id');

        if ($userFilter = $this->getParamFilter('filter.users')) {
            $activityQuery->where(sprintf('log.users_id IN (%s)', $userFilter));
        }

        $order = [];

        $orderDirection = $this->getParam('list.direction', 'ASC');
        $orderColumn    = $this->getParam('list.ordering', 'course.title');
        $order[]        = $orderColumn . ' ' . $orderDirection;

        $query = $db->getQuery(true)
            ->select([
                'course.id',
                'course.title',
                'course.published',
                'viewlevel.title AS access',
                'course.publish_up',
                'activity.lessons',
                'activity.students',
                'activity.last_visit',
                'activity.certificates'
            ])
            ->from('#__oscampus_courses AS course')
            ->innerJoin(sprintf('(%s) AS activity ON activity.courses_id = course.id', $activityQuery))
            ->leftJoin('#__viewlevels AS viewlevel ON viewlevel.id = course.access')
            ->group('course.id')
            ->order($order);

        if ($search = $this->getParam('filter.search')) {
            $query->where($this->model->whereTextSearch($search, 'course.title'));
        }

        return $query;
    }
}

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

use OscampusModelList;

defined('_JEXEC') or die();

class Students extends AbstractQueryBase
{
    /**
     * @inheritDoc
     */
    public function getDefaultOrdering(): string
    {
        return 'last_visit desc';
    }

    /**
     * @inheritDoc
     */
    public function getFilterFields(): array
    {
        return [
            'name',
            'first_visit',
            'last_visit',
            'course_count',
            'lessons_completed'
        ];
    }

    /**
     * @inheritDoc
     */
    public function get()
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->select([
                'student.name',
                'student.username',
                'activity.users_id',
                'MIN(activity.first_visit) AS first_visit',
                'MAX(activity.last_visit) AS last_visit',
                'COUNT(DISTINCT module.courses_id) AS course_count',
                'COUNT(activity.lessons_id) AS lessons_viewed',
                'COUNT(activity.completed) AS lessons_completed',
                '0 AS certificate_count'
            ])
            ->from('#__oscampus_users_lessons AS activity')
            ->innerJoin('#__oscampus_lessons AS lesson ON lesson.id = activity.lessons_id')
            ->innerJoin('#__oscampus_modules AS module ON module.id = lesson.modules_id')
            ->innerJoin('#__users AS student ON student.id = activity.users_id')
            ->group('activity.users_id');

        if ($userFilter = $this->getParamFilter('filter.users')) {
            $query->where(sprintf('activity.users_id IN (%s)', $userFilter));
        }

        if ($search = $this->getParam('filter.search')) {
            $query->where($this->model->whereTextSearch($search, ['student.name', 'student.username']));
        }

        $ordering  = $this->getParam('list.ordering');
        $direction = $this->getParam('list.direction');
        $query->order($ordering . ' ' . $direction);

        return $query;
    }

    /**
     * @param string            $context
     * @param OscampusModelList $model
     * @param object[]          $items
     *
     * @return void
     */
    public function oscampusAfterGetList(string $context, OscampusModelList $model, array &$items)
    {
        if (!$items) {
            return;
        }

        $db               = $this->getDbo();
        $certificateQuery = $db->getQuery(true)
            ->select([
                'users_id',
                'COUNT(DISTINCT id) AS certificates'
            ])
            ->from('#__oscampus_courses_certificates')
            ->group('users_id');

        if (count($items) <= 100) {
            $userIds = array_map(
                function ($row) {
                    return $row->users_id;
                },
                $items
            );

            $certificateQuery->where(sprintf('users_id IN (%s)', join(',', $userIds)));
        }

        $certificates = $db->setQuery($certificateQuery)->loadObjectList('users_id');

        foreach ($items as $item) {
            $item->certificate_count = empty($certificates[$item->users_id])
                ? 0
                : $certificates[$item->users_id]->certificates;
        }
    }
}

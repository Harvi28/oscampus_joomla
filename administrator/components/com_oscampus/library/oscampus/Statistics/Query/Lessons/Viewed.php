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

namespace Oscampus\Statistics\Query\Lessons;

use Oscampus\Statistics\Query\AbstractQueryBase;

defined('_JEXEC') or die();

class Viewed extends AbstractQueryBase
{
    public function getDefaultOrdering(): string
    {
        return 'weekstart desc';
    }

    /**
     * @inheritDoc
     */
    public function getFilterFields(): array
    {
        return [
            'weekstart',
            'videos',
            'quizzes',
            'text',
            'total',
            'users'
        ];
    }

    public function get()
    {
        $db = $this->getDbo();

        $types       = [
            'videos'  => ['vimeo', 'wistia'],
            'quizzes' => ['quiz'],
            'text'    => ['text']
        ];
        $sumTypes    = 'SUM(IF(lesson.type IN (%s), 1, 0)) AS %s';
        $startOfWeek = 'DATE(DATE_ADD(activity.last_visit, INTERVAL(-WEEKDAY(activity.last_visit)) DAY)) AS weekstart';

        $columns = [
            'YEARWEEK(activity.last_visit) AS weeknum',
            sprintf($startOfWeek, $this->daynumber['Monday'])
        ];

        foreach ($types as $column => $fields) {
            $fields    = array_map([$db, 'quote'], $fields);
            $columns[] = sprintf($sumTypes, join(',', $fields), $column);
        }

        $columns = array_merge(
            $columns,
            [
                'COUNT(DISTINCT activity.users_id) AS users',
                'COUNT(*) AS total'
            ]
        );

        $query = $db->getQuery(true)
            ->select($columns)
            ->from('#__oscampus_users_lessons AS activity')
            ->innerJoin('#__oscampus_lessons AS lesson ON lesson.id = activity.lessons_id')
            ->group('weeknum');

        if ($userFilter = $this->getParamFilter('filter.users')) {
            $query->where(sprintf('activity.users_id IN (%s)', $userFilter));
        }

        if ($months = (int)$this->getParam('filter.months')) {
            $query->where(
                sprintf(
                    'DATE_SUB(CURDATE(), INTERVAL %s MONTH) <= activity.last_visit',
                    $months
                )
            );

            // When limited months filter active, ignore list limit
            $this->setParam('list.limit', 0);

        } else {
            $ordering    = $this->getParam('list.ordering');
            $direction = $this->getParam('list.direction');

            $query->order($ordering . ' ' . $direction);
        }

        return $query;
    }
}

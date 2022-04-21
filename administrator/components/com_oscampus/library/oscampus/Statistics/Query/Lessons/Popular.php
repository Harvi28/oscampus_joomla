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

class Popular extends AbstractQueryBase
{
    /**
     * @inheritDoc
     */
    public function getDefaultOrdering(): string
    {
        return 'visits desc';
    }

    /**
     * @inheritDoc
     */
    public function getFilterFields(): array
    {
        return [
            'earliest_visit',
            'lesson_title',
            'course_title',
            'visits'
        ];
    }

    public function get()
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->select([
                'activity.lessons_id',
                'MIN(activity.last_visit) AS earliest_visit',
                'MAX(activity.last_visit) AS latest_visit',
                'lesson.title AS lesson_title',
                'course.title AS course_title',
                'count(*) visits'
            ])
            ->from('#__oscampus_users_lessons AS activity')
            ->innerJoin('#__oscampus_lessons AS lesson ON lesson.id = activity.lessons_id')
            ->innerJoin('#__oscampus_modules AS module ON module.id = lesson.modules_id')
            ->innerJoin('#__oscampus_courses AS course ON course.id = module.courses_id')
            ->group('lesson.id');

        $ordering  = $this->getParam('list.ordering');
        $direction = $this->getParam('list.direction');

        if ($ordering && $direction) {
            $query->order($ordering . ' ' . $direction);
        }

        if ($days = (int)$this->getParam('filter.days')) {
            $query->where('DATEDIFF(CURDATE(), DATE(activity.last_visit)) <= ' . $days);
        }

        if ($userFilter = $this->getParamFilter('filter.users')) {
            $query->where(sprintf('activity.users_id IN (%s)', $userFilter));
        }

        if ($search = $this->getParam('filter.search')) {
            $query->where($this->model->whereTextSearch($search, ['lesson.title', 'course.title']));
        }

        return $query;
    }
}

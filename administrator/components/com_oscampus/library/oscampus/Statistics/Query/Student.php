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

use Exception;
use Oscampus\Activity\CourseStatus;
use OscampusModelList;

defined('_JEXEC') or die();

class Student extends AbstractQueryBase
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
            'last_visit',
            'activity.students',
            'activity.certificates'
        ];
    }

    /**
     * @inheritDoc
     */
    public function get()
    {
        $db     = $this->getDbo();
        $userId = (int)$this->getParam('user.id');

        $order     = $this->getParam('list.ordering');
        $direction = $this->getParam('list.direction');

        $coursesQuery = $db->getQuery(true)
            ->select([
                'c1.id',
                'c1.title',
                'COUNT(DISTINCT l1.id) AS lesson_count'
            ])
            ->from('#__oscampus_courses AS c1')
            ->innerJoin('#__oscampus_modules AS m1 ON m1.courses_id = c1.id')
            ->innerJoin('#__oscampus_lessons AS l1 ON l1.modules_id = m1.id')
            ->where('l1.published = 1')
            ->group('c1.id');

        $query = $db->getQuery(true)
            ->select([
                'course.id',
                'activity.users_id',
                'course.title',
                'COUNT(lesson.id) AS lessons_viewed',
                'COUNT(activity.completed) AS lessons_completed',
                'course.lesson_count',
                'MIN(first_visit) AS first_visit',
                'MAX(last_visit) AS last_visit',
                'certificate.id AS certificates_id',
                'certificate.date_earned'
            ])
            ->from('#__oscampus_users_lessons AS activity')
            ->innerJoin('#__oscampus_lessons AS lesson ON lesson.id = activity.lessons_id')
            ->innerJoin('#__oscampus_modules AS module ON module.id = lesson.modules_id')
            ->innerJoin(sprintf('(%s) AS course ON course.id = module.courses_id', $coursesQuery))
            ->leftJoin(sprintf(
                '#__oscampus_courses_certificates AS certificate ON %s',
                join(' AND ', [
                    'certificate.users_id = activity.users_id',
                    'certificate.courses_id = course.id'
                ])
            ))
            ->where('activity.users_id = ' . $userId)
            ->group('course.id')
            ->order($order . ' ' . $direction);

        if ($search = $this->getParam('filter.search')) {
            $query->where($this->model->whereTextSearch($search, ['course.title']));
        }

        return $query;
    }

    /**
     * @param string            $context
     * @param OscampusModelList $model
     * @param object[]          $items
     *
     * @return void
     * @throws Exception
     */
    public function oscampusAfterGetList($context, $model, &$items)
    {
        foreach ($items as &$item) {
            $itemClass = new CourseStatus();
            $itemClass->setProperties(get_object_vars($item));
            $item = $itemClass;
        }
    }
}

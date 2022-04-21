<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2018-2021 Joomlashack.com. All rights reserved
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

namespace Oscampus\Statistics\Query\Student;

use Exception;
use Joomla\CMS\Language\Text;
use Oscampus\Statistics\Query\AbstractQueryBase;
use OscampusModelList;

defined('_JEXEC') or die();

class Lessons extends AbstractQueryBase
{
    /**
     * @inheritDoc
     */
    public function get()
    {
        $db = $this->getDbo();

        $userId   = (int)$this->getParam('user.id');
        $courseId = (int)$this->getParam('course.id');

        if ($userId && $courseId) {
            $query = $db->getQuery(true)
                ->select([
                    'activity.id',
                    'course.id AS courses_id',
                    'module.id AS modules_id',
                    'lesson.id AS lessons_id',
                    'course.difficulty',
                    'course.title AS course_title',
                    'module.title AS module_title',
                    'lesson.title AS lesson_title',
                    'lesson.type',
                    'activity.completed',
                    'activity.score',
                    'activity.visits',
                    'activity.data',
                    'activity.first_visit',
                    'activity.last_visit'
                ])
                ->from('#__oscampus_courses AS course')
                ->innerJoin('#__oscampus_modules AS module ON module.courses_id = course.id')
                ->innerJoin('#__oscampus_lessons AS lesson ON lesson.modules_id = module.id')
                ->leftJoin(sprintf(
                    '#__oscampus_users_lessons AS activity ON %s',
                    join(' AND ', [
                        'activity.lessons_id = lesson.id',
                        'activity.users_id = ' . $userId
                    ])
                ))
                ->where('course.id = ' . $courseId)
                ->order([
                    'module.ordering ASC',
                    'lesson.ordering ASC'
                ]);

            // No pagination for these results
            $this->setParam('list.limit', 0);

            return $query;
        }

        throw new Exception(Text::sprintf('COM_OSCAMPUS_ERROR_INVALID_METHOD_CALL', static::class, __FUNCTION__));
    }

    /**
     * @param string            $context
     * @param OscampusModelList $model
     * @param object[]          $items
     *
     * @return void
     */
    public function oscampusAfterGetList(string $context, OscampusModelList $model, &$items)
    {
        $list = [];
        foreach ($items as $item) {
            $key = md5($item->module_title);
            if (!isset($list[$key])) {
                $list[$key] = (object)[
                    'title'   => $item->module_title,
                    'lessons' => []
                ];
            }
            $list[$key]->lessons[] = $item;
        }

        $items = array_values($list);
    }
}

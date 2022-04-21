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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\User;
use Oscampus\Activity\CourseStatus;

defined('_JEXEC') or die();

class OscampusModelCourselist extends OscampusModelSiteList
{
    /**
     * @inheritDoc
     */
    public function __construct(array $config = [])
    {
        $filters = [
            'filter_fields' => [
                'difficulty',
                'progress',
                'tag',
                'teacher',
                'text'
            ]
        ];

        $config = array_merge_recursive($config, $filters);

        parent::__construct($config);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function getListQuery()
    {
        $db = $this->getDbo();

        /** @var User $user */
        $user = $this->getState('user');

        $tags  = sprintf(
            'GROUP_CONCAT(DISTINCT tag.title ORDER BY tag.title ASC SEPARATOR %s) AS tags',
            $db->quote(', ')
        );
        $query = $db->getQuery(true)
            ->select([
                'course.*',
                'COUNT(DISTINCT lesson.id) AS lesson_count',
                $tags,
                'teacher_user.name AS teacher'
            ])
            ->from('#__oscampus_courses AS course')
            ->innerJoin('#__oscampus_modules AS module ON module.courses_id = course.id')
            ->innerJoin('#__oscampus_lessons AS lesson ON lesson.modules_id = module.id')
            ->leftJoin('#__oscampus_teachers AS teacher ON teacher.id = course.teachers_id')
            ->leftJoin('#__oscampus_courses_tags AS ct ON ct.courses_id = course.id')
            ->leftJoin('#__oscampus_tags AS tag ON tag.id = ct.tags_id')
            ->leftJoin('#__users AS teacher_user ON teacher_user.id = teacher.users_id')
            ->where([
                'course.published = 1',
                'course.publish_up <= UTC_TIMESTAMP()',
                'IFNULL(course.publish_down, UTC_TIMESTAMP()) >= UTC_TIMESTAMP()',
                $this->whereAccess('course.access', $user)
            ])
            ->group('course.id');

        // Set user activity fields
        if ($user->id) {
            $query->select([
                'activity.users_id',
                'COUNT(DISTINCT activity.id) AS lessons_viewed',
                'certificate.id AS certificates_id',
                'certificate.date_earned'
            ])
                ->leftJoin('#__oscampus_users_lessons AS activity ON activity.lessons_id = lesson.id and activity.users_id = ' . $user->id)
                ->leftJoin('#__oscampus_courses_certificates AS certificate ON certificate.courses_id = course.id AND certificate.users_id = ' . $user->id);
        } else {
            $query->select([
                '0 AS users_id',
                '0 AS lessons_viewed',
                '0 AS certificates_id',
                'NULL AS date_earned'
            ]);
        }

        // Tag filter
        if ($tagId = (int)$this->getState('filter.tag')) {
            $tagQuery = $db->getQuery(true)
                ->select('courses_id')
                ->from('#__oscampus_courses_tags')
                ->where('tags_id = ' . $tagId)
                ->group('courses_id');

            $query->where(sprintf('course.id IN (%s)', $tagQuery));
        }

        // Teacher filter
        if ($teacherId = (int)$this->getState('filter.teacher')) {
            $query->where('teacher.id = ' . $teacherId);
        }

        // Difficulty filter
        if ($difficulty = $this->getState('filter.difficulty')) {
            $query->where('course.difficulty = ' . $db->quote($difficulty));
        }

        // Text filter
        if ($text = $this->getState('filter.text')) {
            $fields = [
                'course.title',
                'course.introtext',
                'course.description',
                'lesson.description'
            ];
            $query->where($this->whereTextSearch($text, $fields));
        }

        // User progress status filter
        $progress = $this->getState('filter.progress');
        if ($progress !== null) {
            if ($progress == CourseStatus::NOT_STARTED) {
                $query->having('lessons_viewed = 0');

            } elseif ($progress == CourseStatus::COMPLETED) {
                $query->having('certificates.id > 0');

            } elseif ($progress == CourseStatus::IN_PROGRESS) {
                $query->having('lessons_viewed > 0');
            }
        }

        return $query;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getItems()
    {
        $storeId  = $this->getStoreId();
        $isCached = isset($this->cache[$storeId]);

        $items = parent::getItems();
        if (!$isCached) {
            $tbd = Text::_('COM_OSCAMPUS_TEACHER_UNKNOWN');
            foreach ($items as $item) {
                if (!$item->teacher) {
                    $item->teacher = $tbd;
                }
                $item->description = HTMLHelper::_('content.prepare', $item->description);
            }
        }

        return $items;
    }

    /**
     * @inheritDoc
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $this->setState('user', OscampusFactory::getUser());

        parent::populateState($ordering, $direction);
    }
}

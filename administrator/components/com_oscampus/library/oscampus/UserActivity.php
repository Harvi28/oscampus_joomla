<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2021 Joomlashack.com. All rights reserved
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

namespace Oscampus;

use Exception;
use JDatabaseDriver;
use JDatabaseQuery;
use Joomla\CMS\User\User;
use Oscampus\Activity\CourseStatus;
use Oscampus\Activity\LessonStatus;
use Oscampus\Activity\LessonSummary;
use OscampusFactory;

defined('_JEXEC') or die();

class UserActivity extends AbstractBase
{
    /**
     * @var User
     */
    protected $user = null;

    /**
     * @var LessonStatus
     */
    protected $lessonStatus = null;

    /**
     * @var LessonStatus[][]
     */
    protected $lessons = [];

    /**
     * @var LessonSummary
     */
    protected $lessonSummary = null;

    /**
     * @var Certificate
     */
    public $certificate = null;

    /**
     * @var CourseStatus
     */
    protected $courseStatus = null;

    /**
     * @var CourseStatus[]
     */
    protected $courses = null;

    public function __construct(
        JDatabaseDriver $dbo,
        User $user,
        LessonStatus $lessonStatus,
        LessonSummary $lessonSummary,
        CourseStatus $courseStatus,
        Certificate $certificate
    ) {
        parent::__construct($dbo);

        $this->user          = $user;
        $this->lessonStatus  = $lessonStatus;
        $this->lessonSummary = $lessonSummary;
        $this->courseStatus  = $courseStatus;
        $this->certificate   = $certificate;
    }

    /**
     * Change to the selected user's tracking data
     *
     * @param int $id
     *
     * @return User
     */
    public function setUser(int $id): User
    {
        if ($id != $this->user->id) {
            $this->user    = OscampusFactory::getUser($id);
            $this->lessons = [];
            $this->courses = [];
        }

        return $this->user;
    }

    /**
     * Internal method for loading activity records for the currently set user
     *
     * @param int $courseId
     *
     * @return LessonStatus[]
     * @throws Exception
     */
    protected function get(int $courseId): array
    {
        if ($this->user->id) {
            if (!isset($this->lessons[$courseId])) {
                $query = $this->getStatusQuery()
                    ->where([
                        'module.courses_id = ' . $courseId,
                        'activity.id'
                    ]);

                $this->lessons[$courseId] = $this->dbo
                    ->setQuery($query)
                    ->loadObjectList('lessons_id', get_class($this->lessonStatus));
            }
        }

        return $this->lessons[$courseId];
    }

    /**
     * Get the course ID from the lesson ID since lessons doesn't
     * provide this information directly
     *
     * @param int $lessonId
     *
     * @return ?int
     */
    protected function getCourseIdFromLessonId(int $lessonId): ?int
    {
        $query = $this->dbo->getQuery(true)
            ->select('courses_id')
            ->from('#__oscampus_modules m1')
            ->innerJoin('#__oscampus_lessons l1 ON l1.modules_id = m1.id')
            ->where('l1.id = ' . $lessonId);

        return $this->dbo->setQuery($query)->loadColumn();
    }

    /**
     * Get all activity records for this user for the selected course
     *
     * @param int $courseId
     *
     * @return LessonStatus[]
     * @throws Exception
     */
    public function getCourseLessons(int $courseId): array
    {
        return $this->get($courseId);
    }

    /**
     * Update last visit date and number of visits
     *
     * @param Lesson $lesson
     *
     * @return void
     * @throws Exception
     */
    public function visitLesson(Lesson $lesson)
    {
        if ($lesson->isAuthorised()) {
            $app = OscampusFactory::getApplication();

            $lessonStatus = $this->getLessonStatus($lesson->id);

            // Always record the current time
            $lessonStatus->last_visit = OscampusFactory::getDate()->toSql();

            // Don't bump the visit count if the page is only refreshing
            $visited = $app->getUserState('oscampus.lesson.visited');
            if ($lessonStatus->id && $visited != $lesson->id) {
                $lessonStatus->visits++;
            }
            $this->setStatus($lessonStatus);

            $this->recordProgress($lesson);
            $app->setUserState('oscampus.lesson.visited', $lesson->id);
        }
    }

    /**
     * Insert/Update user activity record
     *
     * @param Lesson  $lesson
     * @param ?string $score
     * @param ?mixed  $data
     *
     * @return void
     * @throws Exception
     */
    public function recordProgress(Lesson $lesson, ?string $score = null, $data = null)
    {
        $lessonStatus = $this->getLessonStatus($lesson->id);
        $completed    = $lessonStatus->completed;

        $lesson->prepareActivityProgress($lessonStatus, $score, $data);
        $this->setStatus($lessonStatus);

        // On transition to completed, check to see if they earned a certificate
        if (!$completed && $lessonStatus->completed) {
            $this->certificate->award($lessonStatus->courses_id, $this);
        }
    }

    /**
     * Get an activity status record
     *
     * @param int $lessonId
     *
     * @return LessonStatus
     * @throws Exception
     */
    public function getLessonStatus(int $lessonId): LessonStatus
    {
        if ($this->user->id) {
            $query = $this->getStatusQuery()
                ->where('lesson.id = ' . $lessonId);

            $lessonStatus = $this->dbo->setQuery($query)->loadObject(get_class($this->lessonStatus));
        }

        if (empty($lessonStatus)) {
            $lessonStatus = clone $this->lessonStatus;
        }
        if (!$lessonStatus->users_id) {
            $lessonStatus->setProperties([
                'users_id'   => $this->user->id,
                'lessons_id' => $lessonId
            ]);
        }

        return $lessonStatus;
    }

    /**
     * Standard query for finding status records. We're doing this so
     * that we can pull all activity records for a course by filtering
     * on module.courses_id when needed
     *
     * @return JDatabaseQuery
     */
    protected function getStatusQuery(): JDatabaseQuery
    {
        $userId = $this->user->id;

        return $this->dbo->getQuery(true)
            ->select('activity.*, module.courses_id, lesson.type')
            ->from('#__oscampus_lessons lesson')
            ->innerJoin('#__oscampus_modules AS module ON module.id = lesson.modules_id')
            ->leftJoin(sprintf(
                '#__oscampus_users_lessons AS activity ON %s',
                join(' AND ', [
                    'activity.lessons_id = lesson.id',
                    'activity.users_id = ' . $userId
                ])
            ))
            ->where('lesson.published = 1')
            ->order('module.ordering ASC, lesson.ordering ASC');
    }

    /**
     * insert/update an activity status record
     *
     * @param LessonStatus $lessonStatus
     *
     * @return bool
     * @throws Exception
     */
    public function setStatus(LessonStatus $lessonStatus): bool
    {
        if (!empty($lessonStatus->users_id) && !empty($lessonStatus->lessons_id)) {
            $fields = $this->dbo->getTableColumns('#__oscampus_users_lessons');

            $thisVisit = OscampusFactory::getDate();

            $lessonStatus->last_visit = $thisVisit;
            if (empty($lessonStatus->id)) {
                $lessonStatus->first_visit = $thisVisit;
                $lessonStatus->visits      = 1;

                $insert  = (object)array_intersect_key($lessonStatus->toArray(), $fields);
                $success = (bool)$this->dbo->insertObject('#__oscampus_users_lessons', $insert);

            } else {
                $update  = (object)array_intersect_key($lessonStatus->toArray(), $fields);
                $success = (bool)$this->dbo->updateObject('#__oscampus_users_lessons', $update, ['id']);
            }

            return $success;
        }

        return false;
    }

    /**
     * Get a summary of this user's lesson activity for courses
     *
     * @param ?int $courseId
     *
     * @return LessonSummary[]
     * @throws Exception
     */
    public function getLessonSummary(?int $courseId = null): array
    {
        $queryCount = $this->dbo->getQuery(true)
            ->select('m1.courses_id, count(distinct l1.id) lessons')
            ->from('#__oscampus_lessons AS l1')
            ->innerJoin('#__oscampus_modules AS m1 ON m1.id = l1.modules_id')
            ->where('l1.published = 1')
            ->group('m1.courses_id');

        $query = $this->dbo->getQuery(true)
            ->select([
                'course.id',
                'activity.users_id',
                'course.certificates_id',
                'certificate.id AS awarded_id',
                'lcount.lessons',
                'count(DISTINCT activity.lessons_id) AS viewed',
                'SUM(DISTINCT activity.visits) AS visits',
                'MAX(activity.completed) AS completed',
                'certificate.date_earned AS certificate',
                'MIN(activity.first_visit) AS first_visit',
                'MAX(activity.last_visit) AS last_visit'
            ])
            ->from('#__oscampus_users_lessons AS activity')
            ->leftJoin('#__oscampus_lessons AS lesson ON lesson.id = activity.lessons_id')
            ->leftJoin('#__oscampus_modules AS module ON module.id = lesson.modules_id')
            ->leftJoin('#__oscampus_courses AS course ON course.id = module.courses_id')
            ->leftJoin(join(' ', [
                '#__oscampus_courses_certificates AS certificate',
                'ON certificate.users_id = activity.users_id',
                'AND certificate.courses_id = course.id'
            ]))
            ->leftJoin("({$queryCount}) AS lcount ON lcount.courses_id = course.id")
            ->where('activity.users_id = ' . $this->user->id)
            ->group('activity.users_id, module.courses_id');

        if ($courseId) {
            $query->where('course.id = ' . $courseId);
        }

        return $this->dbo->setQuery($query)->loadObjectList('id', get_class($this->lessonSummary));
    }
}

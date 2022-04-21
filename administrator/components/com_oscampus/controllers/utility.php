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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();

class OscampusControllerUtility extends OscampusControllerBase
{
    /**
     * @var array[object[]]
     */
    protected $lessonScores = [];

    /**
     * @var OscampusTableCertificates
     */
    protected $defaultCertificate = null;

    /**
     * @var Registry
     */
    protected $params = null;

    /**
     * @var object[]
     */
    protected $tasks = null;

    /**
     * OscampusControllerUtility constructor.
     *
     * @inheritDoc
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->params = $this->container->params;

        $link = 'index.php?option=com_oscampus&task=utility.%s';

        $this->tasks = [
            (object)[
                'link'   => sprintf($link, 'checkCerts'),
                'text'   => 'Check for Missing Certificates',
                'button' => 'Certificates'
            ],
            (object)[
                'link'   => sprintf($link, 'checkActivity'),
                'text'   => 'Check for invalid certificates and missing activity entries',
                'button' => 'Invalid/Missing'
            ],
            (object)[
                'link'   => sprintf($link, 'checkDuplicateLog'),
                'text'   => 'Check for duplicate user log entries',
                'button' => 'Duplicates'
            ],
            (object)[
                'link'   => sprintf($link, 'searchDB'),
                'text'   => 'Find a string in the database',
                'button' => 'DB Search'
            ]
        ];

        OscampusToolbarHelper::link('index.php?option=com_oscampus', 'OSCampus');

        foreach ($this->tasks as $task) {
            OscampusToolbarHelper::link($task->link, $task->button);
        }
    }

    /**
     * @inheritDoc
     */
    public function display($cachable = false, $urlparams = [])
    {
        echo $this->heading('Available utilities');

        $links = [];
        foreach ($this->tasks as $task) {
            $links[] = HTMLHelper::_('link', $task->link, $task->text);
        }

        echo $this->showList($links);
    }

    /**
     * Find duplicate log entries
     *
     * @return void
     */
    public function checkDuplicateLog()
    {
        echo $this->heading('Check for duplicate log entries');

        $db = OscampusFactory::getDbo();

        $remove = $this->app->input->getInt('remove', 0);
        $start  = microtime(true);

        $query = $db->getQuery(true)
            ->select('user.username, activity.users_id, activity.lessons_id, count(*) AS duplicates')
            ->from('#__oscampus_users_lessons AS activity')
            ->innerJoin('#__users AS user ON user.id = activity.users_id')
            ->group('activity.users_id, activity.lessons_id')
            ->having('duplicates > 1');

        $lessons = $db->setQuery($query)->loadObjectList();

        $users = [];
        foreach ($lessons as $lesson) {
            if (!isset($users[$lesson->username])) {
                $users[$lesson->username] = [
                    'id'      => $lesson->users_id,
                    'lessons' => []
                ];
            }
            $users[$lesson->username]['lessons'][$lesson->lessons_id] = $lesson->duplicates;
        }

        echo $this->runtime($start, 'Query');

        echo '<pre>' . $db->replacePrefix($query) . '</pre>';

        $removalStart = microtime(true);

        $fixed = [];
        foreach ($users as $username => $user) {
            $userId  = $user['id'];
            $lessons = $user['lessons'];

            $status = sprintf(
                '%s (%s): %s Duplicates in %s Lessons (%s)',
                $username,
                $userId,
                array_sum($lessons),
                count($lessons),
                join(', ', array_keys($lessons))
            );

            if ($remove) {
                $affected = 0;
                foreach ($lessons as $id => $count) {
                    $query = $db->getQuery(true)
                        ->delete('#__oscampus_users_lessons')
                        ->where([
                            'users_id = ' . (int)$userId,
                            'lessons_id = ' . (int)$id
                        ])
                        ->order('first_visit DESC, id DESC');
                    $db->setQuery($query . ' LIMIT ' . ($count - 1))->execute();
                    $affected += $db->getAffectedRows();
                }
                $status .= sprintf(' [Removed %s]', $affected);
            }
            $fixed[] = $status;
        }

        if ($remove) {
            echo $this->runtime($removalStart, 'Removal');
        }
        echo $this->runtime($start);

        echo $this->heading(sprintf('Found duplicate activity entries for %s users', count($users)));
        if (!$remove && $fixed) {
            echo '<p>Duplicate entries HAVE NOT been removed (url: \'remove=1\')</p>';
        }


        if ($fixed) {
            echo $this->showList($fixed);
        }
    }

    /**
     * Check all active users without certificates to verify they
     * have not passed the course. If they have passed, optionally
     * create a certificate for them when ?create=1 is specified in the url
     *
     * @return void
     * @throws Exception
     */
    public function checkCerts()
    {
        echo $this->heading('Check Certificates');

        $db     = OscampusFactory::getDbo();
        $create = $this->app->input->getInt('create', 0);
        $start  = microtime(true);

        // Get list of candidates
        $query = $this->getActivityQuery()
            ->where('earned.id IS NULL')
            ->having('lessonsViewed = totalLessons')
            ->order('last_visit ASC');

        $activities = $db->setQuery($query)->loadObjectList();
        echo $this->runtime($start, 'Query');

        echo '<pre>' . $db->replacePrefix($query) . '</pre>';

        $fixed      = [];
        $inProgress = [];
        $errors     = [];
        foreach ($activities as $activity) {
            $courseCertificate = $this->getCertificate($activity->certificates_id);
            $candidate         = sprintf(
                '%s: %s [%s] (%s) for %s (%s) [\'%s\' certificate]',
                $this->getDetailLink($activity),
                $activity->name,
                $activity->username,
                $activity->users_id,
                $activity->course_title,
                $activity->courses_id,
                $courseCertificate->title ?: '??'
            );

            if ($this->passedCourse($activity)) {
                $certificate = (object)[
                    'users_id'        => $activity->users_id,
                    'courses_id'      => $activity->courses_id,
                    'date_earned'     => $activity->last_visit,
                    'certificates_id' => $courseCertificate
                ];

                if ($create) {
                    try {
                        $db->insertObject('#__oscampus_courses_certificates', $certificate);

                        $fixed[] = '[CREATED CERTIFICATE] ' . $candidate;

                    } catch (Throwable $e) {
                        $errors[] = $e->getMessage();
                    }

                } else {
                    $fixed[] = $candidate;
                }

            } else {
                $inProgress[] = $candidate;
            }
        }

        echo $this->runtime($start);

        echo $this->heading(number_format(count($errors)) . ' Database Errors');
        echo $this->showList($errors);

        echo $this->heading(number_format(count($fixed)) . ' Missing Certificates');
        if (!$create && $fixed) {
            echo '<p>New certificates HAVE NOT been created (url: \'create=1\')</p>';
        }
        echo $this->showList($fixed);

        echo $this->heading(number_format(count($inProgress)) . ' Classes In Progress');
        echo $this->showList($inProgress);
    }

    /**
     * Look for certificates earned for incomplete courses
     *
     * @return void
     */
    public function checkActivity()
    {
        $db = OscampusFactory::getDbo();

        $fixFailed      = $this->app->input->getInt('failed', 0);
        $fixMissingType = $this->app->input->getCmd('missing', '');
        $setPassing     = $this->app->input->getInt('pass', 0);

        echo $this->heading('Check for Invalid Activity Log Entries');

        $start = microtime(true);

        // Get list of active user IDs
        $query = $this->getActivityQuery()
            ->where([
                'earned.id IS NOT NULL',
            ])
            ->order('last_visit ASC');

        $activities = $db->setQuery($query)->loadObjectList();
        echo $this->runtime($start, 'Query');

        echo '<pre>' . $db->replacePrefix($query) . '</pre>';

        $valid      = [];
        $notPassed  = [];
        $missing    = [];
        $duplicates = [];
        $errors     = [];
        foreach ($activities as $activity) {
            $userId   = $activity->users_id;
            $courseId = $activity->courses_id;

            $candidate = sprintf(
                '%s: %s/%s - %s (%s)  %s (%s) [%s]',
                $this->getDetailLink($activity),
                $activity->lessonsViewed,
                $activity->totalLessons,
                $activity->username,
                $activity->users_id,
                $activity->course_title,
                $activity->courses_id,
                $activity->lessonTypes
            );

            if ($this->passedCourse($activity)) {
                $valid[] = $candidate;

            } elseif ($activity->lessonsViewed > $activity->totalLessons) {
                $duplicates[] = $candidate;

            } else {
                $scores     = $this->getLessonScores($userId, $courseId);
                $emptyTotal = 0;
                $emptyFixed = 0;
                foreach ($scores as $score) {
                    if ($score->id === null) {
                        $emptyTotal++;

                        if ($score->type == $fixMissingType) {
                            try {
                                $activityInsert = (object)[
                                    'users_id'    => $userId,
                                    'lessons_id'  => $score->lessons_id,
                                    'completed'   => $activity->last_visit,
                                    'score'       => 0,
                                    'visits'      => 1,
                                    'first_visit' => $activity->last_visit,
                                    'last_visit'  => $activity->last_visit
                                ];

                                $db->insertObject('#__oscampus_users_lessons', $activityInsert, 'id');
                                $emptyFixed++;

                            } catch (Throwable $e) {
                                $errors[] = $e->getMessage();
                            }
                        }
                    }
                }

                if ($emptyTotal) {
                    if ($fixMissingType) {
                        $candidate = sprintf('[Created %s of %s] ', $emptyFixed, $emptyTotal) . $candidate;
                    }
                    $missing[] = $candidate;

                } else {
                    if ($fixFailed) {
                        try {
                            $query = $db->getQuery(true)
                                ->delete('#__oscampus_courses_certificates')
                                ->where('id = ' . $activity->certificates_id);

                            $db->setQuery($query)->execute();

                            $candidate = sprintf(
                                '[REMOVED CERTIFICATE #%s] %s',
                                $activity->certificates_id,
                                $candidate
                            );

                        } catch (Throwable $e) {
                            $errors[] = $e->getMessage();
                        }

                    } elseif ($setPassing) {
                        $candidate = sprintf('[%s] %s', $this->setToPassing($activity), $candidate);
                    }

                    $notPassed[] = $candidate;
                }
            }
        }

        echo $this->runtime($start);

        // Display any database errors
        echo $this->heading(number_format(count($errors)) . ' Database Errors');
        echo $this->showList($errors);

        // Display missing log entries and action taken
        if ($fixMissingType) {
            echo $this->heading(
                sprintf(
                    'Created %s missing activity logs for \'%s\' lessons',
                    number_format(count($missing)),
                    $fixMissingType
                )
            );

        } else {
            echo $this->heading(sprintf('%s missing activity log entries', number_format(count($missing))));
            if ($missing) {
                echo '<p>Use url \'missing=[lessonType]\') to create entries for the selected lesson type</p>';
            }
        }

        echo $this->showList($missing);

        // Display Duplicated log entries and action
        echo $this->heading(sprintf('%s duplicate log entries', count($duplicates)));
        echo $this->showList($duplicates);

        // Display certificates earned despite a failed class
        if ($fixFailed) {
            echo $this->heading(
                sprintf(
                    'Removed %s certificates on failed classes',
                    number_format(count($notPassed))
                )
            );

        } elseif ($setPassing) {
            echo $this->heading(
                sprintf(
                    'Set %s courses to passing',
                    number_format(count($notPassed))
                )
            );

        } else {
            echo $this->heading(sprintf('%s certificates found for failed classes', number_format(count($notPassed))));
            if ($notPassed) {
                echo '<p>Use url \'failed=1\' to remove the certificates</p>';
                echo '<p>Use url \'pass=1\' to fix all activities to passing</p>';
            }
        }

        echo $this->showList($notPassed);

        echo $this->heading(number_format(count($valid)) . ' Valid Certificates');
    }

    /**
     * search all text/varchar fields in db for desired string
     *
     * @return void
     */
    public function searchDB()
    {
        $regex = $this->app->input->getString('search');
        if (!$regex) {
            echo '<h3>DB Search</h3>';
            echo '<p>Use ?search to specify the string to search for</p>';
            return;
        }

        echo '<h3>DB Search: ' . $regex . '</h3>';

        $db = OscampusFactory::getDbo();

        $start = microtime(true);

        $html   = ['<ul>'];
        $tables = $db->setQuery('SHOW TABLES')->loadColumn();
        foreach ($tables as $table) {
            // Look for tables that have a single auto increment primary key
            $query   = "SHOW COLUMNS FROM {$table} WHERE `Key` = 'PRI' AND `Extra` = 'auto_increment'";
            $idField = $db->setQuery($query)->loadColumn();
            if ($error = $db->getErrorMsg()) {
                echo 'ERROR: ' . $error;
                return;
            }

            if (count($idField) == 1) {
                $query = "SHOW COLUMNS FROM {$table} WHERE type = 'text' OR type like 'varchar%'";
                if ($fields = $db->setQuery($query)->loadColumn()) {
                    $idField = $idField[0];
                    array_unshift($fields, $idField);

                    $fields = array_map([$db, 'quoteName'], $fields);
                    $where  = [];
                    foreach ($fields as $field) {
                        $where[] = $field . ' RLIKE ' . $db->quote($regex);
                    }
                    $query = $db->getQuery(true)
                        ->select($fields)
                        ->from($table)
                        ->where($where, 'OR');

                    if ($rows = $db->setQuery($query)->loadAssocList()) {
                        $html[] = '<li>' . $table . ' (' . number_format(count($rows)) . ')<ul>';
                        foreach ($rows as $row) {
                            // Report
                            $fields = array_keys($row);
                            array_shift($fields);
                            $html[] = sprintf(
                                '<li>%s: %s (%s)</li>',
                                $idField,
                                $row[$idField],
                                join(', ', $fields)
                            );
                        }
                        $html[] = '</ul></li>';
                    }
                }
            }
        }

        $html[] = '</ul>';

        echo join("\n", $html);
        echo $this->runtime($start);
    }

    /**
     * Detailed information about activity for a user/course
     */
    public function details()
    {
        $db       = OscampusFactory::getDbo();
        $userId   = $this->app->input->getInt('uid');
        $courseId = $this->app->input->getInt('cid');

        echo $this->heading('Activity Details for User/Course');

        $query = $this->getActivityQuery();
        $query->where([
            'user.id = ' . $userId,
            'course.id = ' . $courseId
        ]);

        $start = microtime(true);

        $activities = $db->setQuery($query)->loadObjectList();

        echo $this->runtime($start, 'Query');

        if (count($activities) == 1) {
            $activity = array_pop($activities);

            echo sprintf('<p>User: %s (%s) [%s]</p>', $activity->name, $activity->username, $userId);
            echo sprintf('<p>Course: %s [%s]</p>', $activity->course_title, $courseId);

            $certificate = OscampusTable::getInstance('Certificates');
            if ($activity->certificates_id > 0) {
                $certificate->load(['id' => $activity->certificates_id]);
            } elseif ($activity->certificates_id == 0) {
                $certificate->load(['default' => 1]);
            }
            if ($certificate->id) {
                echo sprintf('<p>Certificate: %s [%s]</p>', $certificate->title, $certificate->id);

            } else {
                echo '<p>Certificate has not been earned</p>';
            }

            $scores = $this->getLessonScores($userId, $courseId);

            $lastModule = -1;
            $lessonIds  = [];
            echo '<ol>';
            foreach ($scores as $score) {
                if ($score->modules_id != $lastModule) {
                    if ($lastModule >= 0) {
                        echo '</ol></li>';
                    }
                    echo '<li class="alert alert-info">' . $score->module . '<ol>';
                }

                echo sprintf(
                    '<li>%s [%s]: %s/%s - %s [%s]%s</li>',
                    $score->id ? $score->completed : 'MISSING',
                    $score->id,
                    $score->type,
                    $score->id ? $score->score : 'NA',
                    $score->title,
                    $score->lessons_id,
                    array_search($score->lessons_id, $lessonIds) === false
                        ? ''
                        : ' <span class="alert-error">DUP</span>'
                );

                $lessonIds[] = $score->lessons_id;
                $lastModule  = $score->modules_id;
            }
            echo '</ol></li></ol>';

        } elseif (count($activities) > 1) {
            echo '<p>Multiple records found</p>';
            echo '<pre>' . print_r($activities, 1) . '</pre>';

        } else {
            echo '<p>No records found</p>';
        }

        echo $this->runtime($start, 'Total runtime');
    }

    /**
     * @param string $heading
     *
     * @return string
     */
    protected function heading(string $heading): string
    {
        return '<h3>' . $heading . '</h3>';
    }

    /**
     * @param float   $start
     * @param ?string $name
     *
     * @return string
     */
    protected function runtime(float $start, ?string $name = 'Total'): string
    {
        return sprintf('<p>%s Runtime: %s seconds</p>', $name, number_format((microtime(true) - $start), 1));
    }

    /**
     * Get the base query for finding summary user activities
     *
     * @return JDatabaseQuery
     */
    protected function getActivityQuery(): JDatabaseQuery
    {
        $db = OscampusFactory::getDbo();

        $courseQuery = $db->getQuery(true)
            ->select([
                'course.id',
                'course.title',
                'COUNT(*) AS totalLessons'
            ])
            ->from('#__oscampus_modules AS module')
            ->innerJoin('#__oscampus_lessons AS lesson ON lesson.modules_id = module.id')
            ->innerJoin('#__oscampus_courses AS course ON course.id = module.courses_id')
            ->group('courses_id');

        return $db->getQuery(true)
            ->select([
                'activity.id',
                'activity.users_id',
                'module.courses_id',
                'earned.id AS earned_id',
                'earned.certificates_id',
                'user.name,user.username',
                'course.title AS course_title',
                'earned.date_earned',
                'count(*) AS lessonsViewed',
                'course.totalLessons',
                'MIN(activity.first_visit) AS first_visit',
                'MAX(activity.last_visit) AS last_visit',
                'GROUP_CONCAT(DISTINCT lesson.type) AS lessonTypes'
            ])
            ->from('#__oscampus_users_lessons AS activity')
            ->innerJoin('#__oscampus_lessons AS lesson ON lesson.id = activity.lessons_id')
            ->innerJoin('#__oscampus_modules AS module ON module.id = lesson.modules_id')
            ->innerJoin(sprintf('(%s) AS course ON course.id = module.courses_id', $courseQuery))
            ->leftJoin(sprintf(
                '#__oscampus_courses_certificates AS earned ON %s',
                join(' AND ', [
                    'earned.users_id = activity.users_id',
                    'earned.courses_id = module.courses_id'
                ])
            ))
            ->innerJoin('#__users AS user ON user.id = activity.users_id')
            ->group('activity.users_id, module.courses_id');
    }

    /**
     * @param int $userId
     * @param int $courseId
     *
     * @return object[]
     */
    protected function getLessonScores(int $userId, int $courseId): array
    {
        if (!isset($this->lessonScores[$userId])) {
            $this->lessonScores[$userId] = [];
        }

        if (!isset($this->lessonScores[$userId][$courseId])) {
            $db = OscampusFactory::getDbo();

            $query = $db->getQuery(true)
                ->select([
                    'activity.id',
                    'activity.users_id',
                    'module.courses_id',
                    'lesson.modules_id',
                    'lesson.id AS lessons_id',
                    'lesson.type',
                    'module.title AS module',
                    'lesson.title',
                    'activity.score',
                    'IF(activity.completed, activity.completed, \'0000-00-00 00:00:00\') AS completed'
                ])
                ->from('#__oscampus_courses AS course')
                ->innerJoin('#__oscampus_modules AS module ON module.courses_id = course.id')
                ->innerJoin('#__oscampus_lessons AS lesson ON lesson.modules_id = module.id')
                ->leftJoin(
                    sprintf(
                        '#__oscampus_users_lessons AS activity ON %s',
                        join(' AND ', [
                            'activity.lessons_id = lesson.id',
                            'activity.users_id = ' . $userId
                        ])
                    )
                )
                ->where('course.id = ' . $courseId)
                ->order([
                    'module.ordering ASC',
                    'lesson.ordering ASC'
                ]);

            $this->lessonScores[$userId][$courseId] = $db->setQuery($query)->loadObjectList();
        }

        if (!empty($this->lessonScores[$userId][$courseId])) {
            return $this->lessonScores[$userId][$courseId];
        }

        return [];
    }

    /**
     * see if user passed the course
     *
     * @param object $activity
     *
     * @return bool
     */
    protected function passedCourse(object $activity): bool
    {
        $userId   = (int)$activity->users_id;
        $courseId = (int)$activity->courses_id;
        $scores   = $this->getLessonScores($userId, $courseId);

        $passingQuiz    = $this->params->get('quizzes.passingScore');
        $completedVideo = $this->params->get('videos.completion');

        if ($activity->lessonsViewed === $activity->totalLessons) {
            if ($passingQuiz || $completedVideo) {
                foreach ($scores as $score) {
                    switch ($score->type) {
                        case 'quiz':
                            if ($passingQuiz && $score->score < $passingQuiz) {
                                return false;
                            }
                            break;

                        case 'vimeo':
                        case 'wistia':
                            if ($completedVideo && $score->score < $completedVideo) {
                                return false;
                            }
                            break;
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param ?int $certificateId
     *
     * @return OscampusTableCertificates
     */
    protected function getCertificate(?int $certificateId): OscampusTableCertificates
    {

        if ($certificateId) {
            /** @var OscampusTableCertificates $certificate */
            $certificate = OscampusTable::getInstance('Certificates');
            $certificate->load(['id' => $certificateId]);

            return $certificate;

        } elseif ($this->defaultCertificate === null) {
            $this->defaultCertificate = OscampusTable::getInstance('Certificates');
            $this->defaultCertificate->load(['default' => 1]);
        }


        return $this->defaultCertificate;
    }

    /**
     * Standard display of a list of items
     *
     * @param string[] $items
     *
     * @return string
     */
    protected function showList(array $items): string
    {
        if ($items) {
            return '<ul><li>' . join('</li><li>', $items) . '</li></ul>';
        }

        return '';
    }

    /**
     * @param object $candidate
     *
     * @return string
     * @throws Exception
     */
    protected function setToPassing(object $candidate): string
    {
        $db             = $this->container->dbo;
        $completedVideo = $this->params->get('videos.completion');
        $passingQuiz    = $this->params->get('quizzes.passingScore');

        $activity = $this->container->activity;
        $activity->setUser($candidate->users_id);

        $query = $db->getQuery(true)
            ->select('activity.*')
            ->from('#__oscampus_users_lessons AS activity')
            ->innerJoin('#__oscampus_lessons AS lesson ON lesson.id = activity.lessons_id')
            ->innerJoin('#__oscampus_modules AS module ON module.id = lesson.modules_id')
            ->where([
                'activity.users_id = ' . $candidate->users_id,
                'module.courses_id = ' . $candidate->courses_id
            ]);

        $logEntries = $db->setQuery($query)->loadObjectList('lessons_id');
        $lessons    = $activity->getCourseLessons($candidate->courses_id);

        $updates = 0;
        $added   = 0;
        foreach ($lessons as $lessonId => $lesson) {
            $logEntry = $logEntries[$lessonId] ?? null;
            $logHash  = md5(json_encode($logEntry));

            if (empty($logEntry)) {
                $logEntry = (object)[
                    'users_id'    => $candidate->users_id,
                    'lessons_id'  => $lesson->id,
                    'completed'   => $lesson->last_visit,
                    'visits'      => 1,
                    'first_visit' => $lesson->first_visit,
                    'last_visit'  => $lesson->last_visit
                ];
            }

            switch ($lesson->type) {
                case 'quiz':
                    $logEntry->score     = $passingQuiz;
                    $logEntry->completed = $logEntry->completed ?: $lesson->last_visit;
                    break;

                case 'vimeo':
                case 'wistia':
                    $logEntry->score     = (string)max($logEntry->score, $completedVideo);
                    $logEntry->completed = $logEntry->completed ?: $lesson->last_visit;

                    break;

                default:
                    $logEntry->score     = '100';
                    $logEntry->completed = $logEntry->completed ?: $lesson->last_visit;
                    break;
            }

            if ($logHash != md5(json_encode($logEntry))) {
                if (empty($logEntry->id)) {
                    $db->insertObject('#__oscampus_users_lessons', $logEntry);
                    $added++;

                } else {
                    $db->updateObject('#__oscampus_users_lessons', $logEntry, 'id');
                    $updates++;
                }

            }
        }

        $result = [];
        if ($updates) {
            $result[] = 'Updated ' . $updates;
        }
        if ($added) {
            $result[] = 'Added ' . $added;
        }

        return $result ? join('/', $result) : 'NO CHANGE';
    }

    /**
     * @param string $source
     *
     * @return void
     */
    protected function backupTable(string $source)
    {
        $backup = $source . '_bak';

        $db = OscampusFactory::getDbo();

        $db->setQuery("DROP TABLE IF EXISTS {$backup}")->execute();
        $db->setQuery("CREATE TABLE {$backup} LIKE {$source}")->execute();
        $db->setQuery("INSERT {$backup} SELECT * FROM {$source}")->execute();
    }

    /**
     * @param string $source
     *
     * @return bool
     */
    protected function restoreTable(string $source): bool
    {
        $backup = $source . '_bak';

        $db     = OscampusFactory::getDbo();
        $tables = $db->getTableList();

        $restored = false;
        if (in_array($db->replacePrefix($backup), $tables)) {
            /** @noinspection SqlWithoutWhere */
            $db->setQuery("DELETE FROM {$source}");
            $db->setQuery("INSERT {$source} SELECT * FROM {$backup}");
            $db->dropTable($backup);
            $restored = true;
        }

        return $restored;
    }

    /**
     * @param object $activity
     *
     * @return string
     */
    protected function getDetailLink(object $activity): string
    {
        $detailLink = Route::_('index.php?' . http_build_query([
                'option' => 'com_oscampus',
                'task'   => 'utility.details',
                'uid'    => $activity->users_id,
                'cid'    => $activity->courses_id
            ]));

        return HTMLHelper::_('link', $detailLink, $activity->last_visit, ['target' => '_blank']);
    }
}

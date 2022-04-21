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

defined('_JEXEC') or die();

class OscampusModelUtilities extends OscampusModelAdmin
{
    /**
     * Overridden due to no table item managed in this model
     *
     * @param null $pk
     *
     * @return null
     */
    public function getItem($pk = null)
    {
        return null;
    }

    /**
     * Don't call parent here since we're doing odd things for this model type
     *
     * @return void
     * @throws Exception
     */
    protected function populateState()
    {
        $app = OscampusFactory::getApplication();

        // Load the parameters.
        $value = JComponentHelper::getParams($this->option);
        $this->setState('params', $value);

        $execute = $app->input->getBool('execute', false);
        $this->setState('execute', $execute);
    }

    /**
     * We aren't managing any table items in this model
     *
     * @param string $type
     * @param string $prefix
     * @param array  $config
     *
     * @return null
     */
    public function getTable($type = '', $prefix = 'OscampusTable', $config = array())
    {
        return null;
    }

    /**
     * Returns a simple summary of certificates, lesson visits and wistia downloads
     *
     * @param int $userId
     *
     * @return object
     */
    public function getUserActivity($userId)
    {
        $db = $this->getDbo();

        $query        = $db->getQuery(true)
            ->select(
                array(
                    'certificate.id',
                    'certificate.courses_id',
                    'certificate.date_earned',
                    'course.title AS course_title'
                )
            )
            ->from('#__oscampus_courses_certificates AS certificate')
            ->leftJoin('#__oscampus_courses AS course ON course.id = certificate.courses_id')
            ->where('certificate.users_id = ' . (int)$userId)
            ->order('certificate.date_earned DESC');
        $certificates = $db->setQuery($query)->loadObjectList();

        $query  = $db->getQuery(true)
            ->select(
                array(
                    'visit.id',
                    'visit.lessons_id',
                    'visit.last_visit',
                    'visit.completed',
                    'lesson.title AS lesson_title',
                    'module.title AS module_title',
                    'course.title AS course_title'
                )
            )
            ->from('#__oscampus_users_lessons AS visit')
            ->leftJoin('#__oscampus_lessons AS lesson ON lesson.id = visit.lessons_id')
            ->leftJoin('#__oscampus_modules AS module ON module.id = lesson.modules_id')
            ->leftJoin('#__oscampus_courses AS course ON course.id = module.courses_id')
            ->where('visit.users_id = ' . (int)$userId)
            ->order('visit.last_visit DESC');
        $visits = $db->setQuery($query)->loadObjectList();

        $query = $db->getQuery(true)
            ->select(
                array(
                    'DATE_ADD(DATE(downloaded),INTERVAL 8 - DAYOFWEEK(downloaded) DAY) AS weekending',
                    'count(*) AS downloads'
                )
            )
            ->from('#__oscampus_downloads')
            ->where('users_id = ' . (int)$userId)
            ->group('weekending')
            ->order('weekending desc');

        $downloads = $db->setQuery($query)->loadObjectList();

        return (object)array(
            'certificates' => $certificates,
            'visits'       => $visits,
            'downloads'    => $downloads
        );
    }

    /**
     * Transfer user activity from one user to another.
     *
     * @param int  $sourceUserId
     * @param int  $targetUserId
     *
     * @return object
     */
    public function transfer($sourceUserId, $targetUserId)
    {
        $source = $this->getUserActivity($sourceUserId);
        $target = $this->getUserActivity($targetUserId);

        $overlap = (object)array(
            'certificates' => $this->getTargetOverlap(
                $source->certificates,
                $target->certificates,
                'courses_id'
            ),

            'visits' => $this->getTargetOverlap(
                $source->visits,
                $target->visits,
                'lessons_id'
            )
        );

        if ($this->getState('execute', false)) {
            $this->copyUserActivity(
                '#__oscampus_courses_certificates',
                $source->certificates,
                $overlap->certificates,
                $targetUserId
            );
            $this->copyUserActivity(
                '#__oscampus_users_lessons',
                $source->visits,
                $overlap->visits,
                $targetUserId
            );

            $db    = $this->getDbo();
            $query = $db->getQuery(true)
                ->update('#__oscampus_downloads')
                ->set('users_id =' . $targetUserId)
                ->where('users_id =' . $sourceUserId);
            $db->setQuery($query)->execute();
        }

        return $overlap;
    }

    /**
     * @param string $table
     * @param array  $source
     * @param array  $overlap
     * @param int    $newUserId
     * @param string $keyField
     *
     * @return void
     */
    protected function copyUserActivity($table, array $source, array $overlap, $newUserId, $keyField = 'id')
    {
        $db = $this->getDbo();

        $ids = array();
        foreach ($overlap as $item) {
            $ids[] = $item->$keyField;
        }

        if ($ids) {
            $query = $db->getQuery(true)
                ->delete($table)
                ->where(sprintf('%s IN (%s)', $keyField, join(',', $ids)));

            $db->setQuery($query)->execute();
        }

        foreach ($source as $item) {
            $update = (object)array(
                $keyField  => $item->$keyField,
                'users_id' => (int)$newUserId
            );

            $db->updateObject($table, $update, array($keyField));
        }
    }

    /**
     * Find overlapping array items based on a single property
     *
     * @param object[] $source
     * @param object[] $target
     * @param string   $property
     *
     * @return object[]
     */
    protected function getTargetOverlap(array $source, array $target, $property)
    {
        $sourceIds = array();
        foreach ($source as $item) {
            if (isset($item->$property)) {
                $sourceIds[] = $item->$property;
            }
        }

        $overlap = array();
        foreach ($target as $item) {
            if (isset($item->$property) && array_search($item->$property, $sourceIds) !== false) {
                $overlap[] = $item;
            }
        }

        return $overlap;
    }
}

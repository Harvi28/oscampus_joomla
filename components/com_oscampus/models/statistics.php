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

defined('_JEXEC') or die();

class OscampusModelStatistics extends OscampusModelSiteList
{
    /**
     * @param string $ordering
     * @param string $direction
     *
     * @throws Exception
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = OscampusFactory::getApplication();

        $courseId = $app->input->getInt('course_id');
        $this->setState('course.id', $courseId);

        $userId = $app->input->getInt('user_id');
        $this->setState('user.id', $userId);

        if ($report = $app->input->getCmd('report')) {
            $this->context .= '.' . $report;
        }

        $this->setState('report', $report);

        parent::populateState($ordering, $direction);
    }

    /**
     * @return JDatabaseQuery
     * @throws Exception
     */
    protected function getListQuery()
    {
        $statistics = OscampusFactory::getContainer()->statistics;
        $report     = $this->getState('report');

        $queryClass = $statistics->getQuery($report, $this);

        switch ($report) {
            case 'lessons.popular':
                $fullOrdering = 'visits desc';
                $this->setState('filter.days', 30);
                $this->setState('list.limit', 10);
                break;

            case 'lessons.viewed':
                $this->setState('filter.months', 3);
                break;
        }

        if (empty($fullOrdering)) {
            $fullOrdering = $queryClass->getDefaultOrdering();
        }
        $fullOrdering = explode(' ', $fullOrdering);
        $this->setState('list.direction', array_pop($fullOrdering));
        $this->setState('list.ordering', array_pop($fullOrdering));

        $this->oscampusTrigger('oscampusBeforeGetListQuery', [$this->context, $this]);

        return $queryClass->get();
    }
}

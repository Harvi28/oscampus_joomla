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

class OscampusModelMycourses extends OscampusModelSiteList
{
    protected $filter_fields = array(
        'course.title'
    );

    /**
     * @return JDatabaseQuery
     * @throws Exception
     */
    public function getListQuery()
    {
        $statistics = OscampusFactory::getContainer()->statistics;
        $oscQuery   = $statistics->getQuery('student', $this);
        $query      = $oscQuery->get();

        return $query;
    }

    /**
     * @param string $ordering
     * @param string $direction
     *
     * @return void
     * @throws Exception
     */
    protected function populateState($ordering = 'course.title', $direction = 'ASC')
    {
        $userId = OscampusFactory::getApplication()->input->getInt('user_id');
        $user   = OscampusFactory::getUser($userId);
        $this->setState('user.id', $user->id);

        parent::populateState($ordering, $direction);

        // No pagination for this model
        $this->setState('list.limit', 0);
        $this->setState('list.start', 0);
    }
}

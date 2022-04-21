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

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;

defined('_JEXEC') or die();

JLoader::import('courselist', __DIR__);

class OscampusModelNewcourses extends OscampusModelCourselist
{
    /**
     * @return JDatabaseQuery
     * @throws Exception
     */
    protected function getListQuery()
    {
        /**
         * @var JDate $cutoff
         */

        $db     = $this->getDbo();
        $cutoff = $this->getState('filter.cutoff');

        $query = parent::getListQuery();

        $query->where([
            'course.publish_up >= ' . $db->quote($cutoff->toSql()),
            'IFNULL(course.publish_down, UTC_TIMESTAMP()) >= UTC_TIMESTAMP()'
        ]);

        $ordering  = $this->getState('list.ordering');
        $direction = $this->getState('list.direction');
        $query->order($ordering . ' ' . $direction);

        return $query;
    }

    /**
     * @param string $ordering
     * @param string $direction
     *
     * @throws Exception
     */
    protected function populateState($ordering = 'course.publish_up', $direction = 'DESC')
    {
        $app = OscampusFactory::getApplication();

        if (method_exists($app, 'getParams')) {
            $params = $app->getParams();
        } else {
            $params = new Registry();
        }

        $releasePeriod = $params->get('releasePeriod', '1 month');
        $cutoff        = OscampusFactory::getDate('now - ' . $releasePeriod);
        $this->setState('filter.cutoff', $cutoff);

        parent::populateState($ordering, $direction);

        // Ignore pagination for now
        $this->setState('list.limit', 0);
        $this->setState('list.start', 0);
    }
}

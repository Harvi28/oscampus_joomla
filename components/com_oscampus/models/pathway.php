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

defined('_JEXEC') or die();

JLoader::import('courselist', __DIR__);

class OscampusModelPathway extends OscampusModelCourselist
{
    /**
     * @return JDatabaseQuery
     * @throws Exception
     */
    protected function getListQuery()
    {
        $user  = $this->getState('user');
        $query = parent::getListQuery();

        // Set pathway selection
        if ($pathwayId = (int)$this->getState('pathway.id')) {
            $query
                ->select('MIN(cp.pathways_id) AS pathways_id')
                ->innerJoin('#__oscampus_courses_pathways AS cp ON cp.courses_id = course.id')
                ->innerJoin('#__oscampus_pathways AS pathway ON pathway.id = cp.pathways_id')
                ->where(
                    array(
                        'pathway.published = 1',
                        $this->whereAccess('pathway.access', $user),
                        'pathway.id = ' . $pathwayId
                    )
                );
        } else {
            $query->select('0 AS pathways_id');
        }

        $ordering  = $this->getState('list.ordering');
        $direction = $this->getState('list.direction');
        $query->order($ordering . ' ' . $direction);
        if ($ordering != 'course.title') {
            $query->order('course.title ' . $direction);
        }

        return $query;
    }

    /**
     * Get the current pathway information. Note that this only
     * makes sense if a pathway is selected
     *
     * @return object
     * @throws Exception
     */
    public function getPathway()
    {
        $pathway = $this->getState('pathway');
        if ($pathway === null) {
            if ($pathwayId = (int)$this->getState('pathway.id')) {
                $db = $this->getDbo();

                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__oscampus_pathways')
                    ->where('id = ' . $pathwayId);

                $pathway = $db->setQuery($query)->loadObject();

                $pathway->metadata = new Registry($pathway->metadata);
                $pathway->description = JHtml::_('content.prepare', $pathway->description);

                $this->setState('pathway', $pathway);
            }
        }
        return $pathway;
    }

    /**
     * @param string $ordering
     * @param string $direction
     *
     * @throws Exception
     */
    protected function populateState($ordering = 'cp.ordering', $direction = 'ASC')
    {
        $app    = OscampusFactory::getApplication();
        $params = $this->getParams();

        $pathwayId = $app->input->getInt('pid', 0);
        $this->setState('pathway.id', $pathwayId);

        $ordering = $params->get('courses.ordering', $ordering);
        $direction = $params->get('courses.direction', $direction);

        parent::populateState($ordering, $direction);
    }

    /**
     * @param string $id
     *
     * @return string
     * @throws Exception
     */
    protected function getStoreId($id = '')
    {
        $id .= ($id ? '' : ':') . $this->getState('pathway.id');

        return parent::getStoreId($id);
    }
}

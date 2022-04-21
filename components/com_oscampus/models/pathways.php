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

defined('_JEXEC') or die();


class OscampusModelPathways extends OscampusModelSiteList
{
    /**
     * OscampusModelPathways constructor.
     *
     * @param array $config
     *
     * @return void
     * @throws Exception
     */
    public function __construct(array $config = array())
    {
        $filters = array(
            'filter_fields' => array(
                'owner',
                'pathways',
                'tag',
                'text'
            )
        );

        $config = array_merge_recursive($config, $filters);

        parent::__construct($config);
    }

    /**
     * @return object[]
     * @throws Exception
     */
    public function getItems()
    {
        $storeId  = $this->getStoreId();
        $isCached = isset($this->cache[$storeId]);

        $items = parent::getItems();
        if (!$isCached) {
            foreach ($items as $item) {
                $item->description = JHtml::_('content.prepare', $item->description);
            }
        }

        return $items;
    }

    /**
     * @return JDatabaseQuery
     * @throws Exception
     */
    protected function getListQuery()
    {
        $user = $this->getState('user');

        $query = $this->getDbo()->getQuery(true)
            ->select('*')
            ->from('#__oscampus_pathways AS pathway')
            ->where([
                'pathway.published = 1',
                'IFNULL(pathway.publish_up, UTC_TIMESTAMP()) <= UTC_TIMESTAMP()',
                'IFNULL(pathway.publish_down, UTC_TIMESTAMP()) >= UTC_TIMESTAMP()',
                $this->whereAccess('pathway.access', $user)
            ]);

        // Various ways the pathways can be selected
        if ($pathways = $this->getState('filter.pathways')) {
            $pathways = array_filter(
                array_map('intval', (array)$pathways)
            );
            $query->where(sprintf('pathway.id IN (%s)', join(',', $pathways)));

        } elseif ($pathwayOwner = (int)$this->getState('filter.owner')) {
            $query->where('pathway.users_id = ' . $pathwayOwner);

        } else {
            $query->where('pathway.users_id = 0');
        }

        if ($tagId = (int)$this->getState('filter.tag')) {
            $subQuery = $this->getDbo()->getQuery(true)
                ->select('cp.pathways_id')
                ->from('#__oscampus_courses_tags AS ct')
                ->innerJoin('#__oscampus_courses AS course ON course.id = ct.courses_id')
                ->innerJoin('#__oscampus_courses_pathways AS cp ON cp.courses_id = course.id')
                ->where('ct.tags_id = ' . $tagId)
                ->group('cp.pathways_id');

            $query->where(sprintf('pathway.id IN (%s)', $subQuery));
        }

        if ($text = $this->getState('filter.text')) {
            $fields = array(
                'pathway.title',
                'pathway.description'
            );
            $query->where($this->whereTextSearch($text, $fields));
        }

        $ordering  = $this->getState('list.ordering');
        $direction = $this->getState('list.direction');
        $query->order($ordering . ' ' . $direction);
        if ($ordering != 'pathway.title') {
            $query->order('pathway.title ' . $direction);
        }

        return $query;
    }

    /**
     * @param string $ordering
     * @param string $direction
     *
     * @return void
     * @throws Exception
     */
    protected function populateState($ordering = 'pathway.ordering', $direction = 'ASC')
    {
        $this->setState('user', OscampusFactory::getUser());

        $params    = $this->getParams();
        $ordering  = $params->get('pathways.ordering', $ordering);
        $direction = $params->get('pathways.direction', $direction);

        parent::populateState($ordering, $direction);
    }
}

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

use Joomla\Registry\Registry as Registry;

defined('_JEXEC') or die();

abstract class OscampusModelSiteList extends OscampusModelList
{
    /**
     * @param string $property
     * @param mixed  $default
     *
     * @return JObject
     * @throws Exception
     */
    public function getState($property = null, $default = null)
    {
        $init = !$this->__state_set;
        if ($init) {
            $this->getParams();
        }
        return parent::getState($property, $default);
    }

    /**
     * Get component params merged with menu params
     *
     * @return Registry
     * @throws Exception
     */
    public function getParams()
    {
        $params = $this->state->get('parameters.merged', null);
        if ($params === null) {
            $params = OscampusFactory::getContainer()->params;
            $this->state->set('parameters.component', $params);

            $menuParams = $this->state->get('parameters.menu');
            if (!$menuParams) {
                $menu = OscampusFactory::getApplication()->getMenu();
                $menu = $menu ? $menu->getActive() : null;
                if ($menu) {
                    $menuParams = new Registry($menu->params);
                }
            }

            $mergedParams = clone $params;
            $mergedParams->merge($menuParams);
            $this->state->set('parameters.merged', $mergedParams);
        }

        return $params;
    }

    /**
     * Frontend list models should not use the core populate state method
     * as this will cause all sorts of problems for pagination
     *
     * @param string $ordering
     * @param string $direction
     *
     * @throws Exception
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app    = OscampusFactory::getApplication();
        $params = $this->getParams();

        $ordering = $this->getUserStateFromRequest(
            $this->context . '.list.ordering',
            'ordering',
            $params->get('ordering', $ordering),
            'cmd',
            false
        );
        $this->setState('list.ordering', $ordering);

        $direction = $this->getUserStateFromRequest(
            $this->context . '.list.direction',
            'direction',
            $params->get('direction', $direction),
            'cmd',
            false
        );
        $this->setState('list.direction', $direction);

        $this->setState('list.start', $app->input->get('limitstart', 0, 'uint'));

        $limit = (int)$app->getUserStateFromRequest(
            $this->context . '.list.limit',
            'limit',
            (int)$params->get('list_limit', -1),
            'int'
        );

        if ($limit == -2) {
            $limit = (int)$this->state->get('parameters.component')->get('list_limit', -1);
        }
        if ($limit < 0) {
            $limit = (int)$app->get('list_limit');
        }

        $this->setState('list.limitbox', $limit > 0);
        $this->setState('list.limit', $limit);
    }

    /**
     * @param string $id
     *
     * @return string
     * @throws Exception
     */
    protected function getStoreId($id = '')
    {
        foreach ($this->filter_fields as $filter) {
            if ($filter) {
                $id .= ':' . (is_string($filter) ? $filter : json_encode($filter));
            }
        }

        return parent::getStoreId($id);
    }
}

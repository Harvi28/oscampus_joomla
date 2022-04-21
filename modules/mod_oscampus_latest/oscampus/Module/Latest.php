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

namespace Oscampus\Module;

use Joomla\Registry\Registry;
use OscampusFactory;
use OscampusModel;
use OscampusModelNewcourses;
use OscampusModelPathways;

defined('_JEXEC') or die();

class Latest extends ModuleBase
{
    /**
     * @inheritDoc
     */
    public function __construct(Registry $params, object $module)
    {
        parent::__construct($params, $module);

        $this->params->def('releasePeriod', '1 month');
    }

    /**
     * Returns a flat list of courses
     *
     * @return object[]
     * @throws \Exception
     */
    protected function getCourses(): array
    {
        /** @var OscampusModelNewcourses $model */
        $key = $this->getCacheKey('classes');

        if (!($classes = $this->cache->get($key))) {
            $model = OscampusModel::getInstance('Newcourses');

            $model->getState();

            $model->setState('list.start', 0);
            $model->setState('list.limit', 0);

            $released = $this->params->get('releasePeriod');
            $cutoff   = OscampusFactory::getDate('now - ' . $released);

            $model->setState('filter.cutoff', $cutoff);

            $classes = $model->getItems();

            $this->cache->store($classes, $key);
        }

        return $classes;
    }

    /**
     * Returns a list of pathways containing a list of the classes matching parameters
     *
     * @return object[]
     * @throws \Exception
     */
    protected function getPathways(): array
    {
        /** @var OscampusModelPathways $model */

        $key = $this->getCacheKey('pathways');

        if (!($pathways = $this->cache->get($key))) {
            $model = OscampusModel::getInstance('Pathways');
            $state = $model->getState()->getProperties();
            foreach ($state as $name => $value) {
                if (strpos($name, 'filter.') === 0) {
                    $model->setState($name, null);
                }
            }
            $model->setState('list.start', 0);
            $model->setState('list.limit', 0);

            if ($rawPathways = $model->getItems()) {
                if ($rawCourses = $this->getCourses()) {
                    $coursePool = [];
                    foreach ($rawCourses as $course) {
                        $coursePool[$course->id] = $course;
                    }

                    $pathwayPool = [];
                    foreach ($rawPathways as $pathway) {
                        $pathwayPool[$pathway->id] = $pathway;
                    }

                    $query  = $this->db->getQuery(true)
                        ->select('course.id, cp.pathways_id')
                        ->from('#__oscampus_courses AS course')
                        ->innerJoin('#__oscampus_courses_pathways AS cp ON cp.courses_id = course.id')
                        ->where([
                            sprintf('course.id IN (%s)', join(',', array_keys($coursePool))),
                            sprintf('cp.pathways_id IN (%s)', join(',', array_keys($pathwayPool)))
                        ]);
                    $idList = $this->db->setQuery($query)->loadObjectList();

                    $pathways = [];
                    foreach ($idList as $ids) {
                        $pathwayId = $ids->pathways_id;
                        $pathway   = isset($pathwayPool[$pathwayId]) ? $pathwayPool[$pathwayId] : null;

                        $courseId = $ids->id;
                        $course   = isset($coursePool[$courseId]) ? $coursePool[$courseId] : null;

                        if ($course && $pathway) {
                            if (!isset($pathways[$pathwayId])) {
                                $pathway->courses     = [];
                                $pathways[$pathwayId] = $pathway;
                            }
                            $pathways[$pathwayId]->courses[] = $course;
                        }
                    }

                    $this->cache->store($pathways, $key);
                }
            }
        }

        if (!empty($pathways)) {
            return $pathways;
        }

        return [];
    }

    /**
     * @param string $key
     *
     * @return string
     */
    protected function getCacheKey(string $key): string
    {
        $key = md5($key . $this->params->get('releasePeriod'));

        return $key;
    }
}

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

use Exception;
use Joomla\Registry\Registry;
use OscampusModel;
use OscampusModelPathway;
use OscampusModelPathways;

defined('_JEXEC') or die();

class Pathways extends ModuleBase
{
    /**
     * @var array
     */
    protected $items = array();

    public function __construct(Registry $params, $module)
    {
        parent::__construct($params, $module);

        $this->params->def('pathways.ordering', 'pathway.title');
        $this->params->def('pathways.direction', 'ASC');
        $this->params->def('pathways.showEmpty', 0);
        $this->params->def('courses.maximum', 8);
        $this->params->def('courses.ordering', 'course.publish_up');
        $this->params->def('courses.direction', 'DESC');
        $this->params->def('courses.allowDuplicates', 0);
    }

    /**
     * @return object[]
     * @throws Exception
     */
    protected function getItems()
    {
        $maxCourses      = $this->params->get('courses.maximum');
        $allowDuplicates = $this->params->get('courses.allowDuplicates');
        $courseOrdering  = $this->params->get('courses.ordering');
        $courseDirection = $this->params->get('courses.direction');
        $showEmpty       = $this->params->get('pathways.showEmpty');

        $limit = $maxCourses;
        if (!$allowDuplicates) {
            // A bit of a fudge. Get twice as many courses if we're going to be pruning the list
            $limit *= 2;
        }

        $storeId = $this->getStoreId(
            array(
                $maxCourses,
                $allowDuplicates,
                $courseOrdering,
                $courseDirection,
                $showEmpty,
                $limit
            )
        );

        if (!isset($this->items[$storeId])) {
            /** @var OscampusModelPathway $model */
            $model = OscampusModel::getInstance('Pathway');
            $model->getState();

            $model->setState('list.start', 0);
            $model->setState('list.limit', $limit);
            $model->setState('list.ordering', $this->params->get('courses.ordering'));
            $model->setState('list.direction', $this->params->get('courses.direction'));

            $fullList = $this->getPathways();
            $listed   = array();

            $this->items[$storeId] = array();
            foreach ($fullList as $pathway) {
                $model->setState('pathway.id', $pathway->id);

                $courses = $model->getItems();
                if (!$allowDuplicates) {
                    $courses = $this->pruneCourses($courses, $listed, $maxCourses);
                }
                if ($courses || $showEmpty) {
                    $pathway->courses = $courses;

                    $this->items[$storeId][] = $pathway;
                }
            }
        }

        return $this->items[$storeId];
    }

    /**
     * remove any duplicated courses and update the listed IDs array
     *
     * @param object[] $courses
     * @param int[]    $listed
     * @param int      $max
     *
     * @return object[]
     */
    protected function pruneCourses(array $courses, array &$listed, $max = 0)
    {
        $prunedList = array();

        foreach ($courses as $course) {
            if (!in_array($course->id, $listed)) {
                $prunedList[] = $course;
                $listed[]     = $course->id;
            }
            if ($max > 0 && count($prunedList) >= $max) {
                break;
            }
        }

        return $prunedList;
    }

    /**
     * @return object[]
     * @throws Exception
     */
    protected function getPathways()
    {
        $pathwayIds = array_filter(
            array_map('intval', $this->params->get('pathways.selected', array()))
        );

        $state = array(
            'list.limit'      => 0,
            'list.start'      => 0,
            'list.ordering'   => $this->params->get('pathways.ordering'),
            'list.direction'  => $this->params->get('pathways.direction'),
            'filter.pathways' => $pathwayIds
        );

        /** @var OscampusModelPathways $model */
        $model = OscampusModel::getInstance('Pathways');
        $model->getState();
        foreach ($state as $key => $value) {
            $model->setState($key, $value);
        }

        $pathways = $model->getItems();

        return $pathways;
    }
}

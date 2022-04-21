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

use Joomla\Registry\Registry;
use Oscampus\Statistics\Manager;
use Oscampus\Statistics\Query\AbstractQueryBase;

defined('_JEXEC') or die();

class OscampusModelStatistics extends OscampusModelAdminList
{
    /**
     * @var Manager
     */
    protected $manager = null;

    /**
     * @var string
     */
    protected $report = null;

    /**
     * @var AbstractQueryBase
     */
    protected $queryClass = null;

    /**
     * @var Registry[]
     */
    protected $courses = [];

    /**
     * @inheritDoc
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $input = OscampusFactory::getApplication()->input;

        $this->report = $input->getCmd('report', $input->getCmd('layout', 'lessons.popular'));

        $this->context .= '.' . str_replace('.', '-', $this->report);

        $this->manager = OscampusFactory::getContainer()->statistics;

        if ($this->queryClass = $this->manager->getQuery($this->report, $this)) {
            $this->filter_fields = $this->queryClass->getFilterFields();
        }
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = OscampusFactory::getApplication();

        if ($this->queryClass) {
            $fullOrdering = explode(' ', $this->queryClass->getDefaultOrdering());
            $direction    = array_pop($fullOrdering);
            $ordering     = array_pop($fullOrdering);
        }

        $courseId = $app->input->getInt('course_id');
        $this->setState('course.id', $courseId);

        $userId = $app->input->getInt('user_id');
        $this->setState('user.id', $userId);

        parent::populateState($ordering, $direction);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getFilterForm($data = [], $loadData = true)
    {
        $this->filterFormName = 'filter_statistics_' . $this->report;

        return parent::getFilterForm($data, $loadData);
    }

    /**
     * @inheritDoc
     */
    protected function getListQuery()
    {
        if ($this->queryClass instanceof AbstractQueryBase) {
            return $this->queryClass->get();
        }

        return null;
    }
}

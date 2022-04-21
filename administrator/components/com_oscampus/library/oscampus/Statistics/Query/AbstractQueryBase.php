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

namespace Oscampus\Statistics\Query;

use Exception;
use JDatabaseDriver;
use JDatabaseQuery;
use JObject;
use JObservableInterface;
use JObserverInterface;
use Joomla\Registry\Registry;
use Oscampus\Statistics\Manager;
use OscampusModel;
use OscampusModelList;

defined('_JEXEC') or die();

abstract class AbstractQueryBase implements JObserverInterface
{
    /**
     * @var OscampusModel|OscampusModelList
     */
    protected $model = null;

    /**
     * @var int[]
     */
    protected $daynumber = [
        'Sunday'    => 1,
        'Monday'    => 2,
        'Tuesday'   => 3,
        'Wednesday' => 4,
        'Thursday'  => 5,
        'Friday'    => 6,
        'Saturday'  => 7
    ];

    /**
     * @var Manager
     */
    protected $manager = null;

    /**
     * AbstractQueryBase constructor.
     *
     * @param Manager                         $manager
     * @param OscampusModel|OscampusModelList $model
     *
     * @return void
     * @throws Exception
     */
    public function __construct(Manager $manager, $model)
    {
        $this->manager = $manager;
        $this->model   = $model;

        $model->attachObserver($this);
    }

    public function __toString()
    {
        return (string)$this->get();
    }

    /**
     * @inheritDoc
     */
    public static function createObserver(JObservableInterface $observableObject, $params = [])
    {
        // We are self registering but still need to obey the inheritance rules
    }

    /**
     * @return JDatabaseDriver
     */
    protected function getDbo()
    {
        return $this->manager->getDbo();
    }

    /**
     * @param ?string $name
     * @param mixed   $default
     *
     * @return JObject|Registry
     */
    protected function getParam(?string $name = null, $default = null)
    {
        $params = $this->model->getState();

        if ($name) {
            $value = $params->get($name, $default);
            $params->set($name, $value);

            return $value;
        }

        return $params;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return mixed
     */
    protected function setParam($name, $value)
    {
        return $this->model->setState($name, $value);
    }

    /**
     * Normalizes a parameter into a string usable for WHERE IN () sql clauses
     * Note this accepts JDatabaseQuery as it can convert to a string and is
     * useful for where subqueries
     *
     * @param string|string[] $name
     * @param string|string[] $default
     *
     * @return string
     */
    protected function getParamFilter($name, $default = null): string
    {
        $value = $this->getParam($name, $default);
        if (is_array($value)) {
            // @TODO: We're assuming array of ids - is this sensible
            $value = join(',', array_filter(array_map('intval', $value)));
        }

        return (string)$value;
    }

    /**
     * @return array
     */
    public function getFilterFields(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function getDefaultOrdering(): string
    {
        return '';
    }

    /**
     * @return JDatabaseQuery
     */
    abstract public function get();
}

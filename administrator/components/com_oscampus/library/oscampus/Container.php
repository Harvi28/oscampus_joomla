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

namespace Oscampus;

use Exception;
use JDatabaseDriver;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentRecord;
use Joomla\Registry\Registry;
use JUser;
use Oscampus\Font\Manager as FontManager;
use Oscampus\Lesson\Download;
use Oscampus\Statistics\Manager as StatisticsManager;
use ReflectionClass;

defined('_JEXEC') or die();

/**
 * Class Container
 *
 * @package OSCampus
 *
 * @property Course            $course
 * @property Certificate       $certificate
 * @property Download          $download
 * @property CMSApplication    $applicataion
 * @property JDatabaseDriver   $dbo
 * @property JUser             $user
 * @property Lesson            $lesson
 * @property StatisticsManager $statistics
 * @property UserActivity      $activity
 * @property Registry          $params
 * @property ComponentRecord   $component
 * @property Theme             $theme
 * @property FontManager       $fonts
 */
class Container extends \Pimple\Container
{
    /**
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this[$name])) {
            return $this[$name];
        }

        return null;
    }

    /**
     * @param $name
     * @param $args
     *
     * @return mixed
     */
    public function __call($name, $args)
    {
        if (strpos($name, 'get') === 0 && !$args) {
            $key = strtolower(substr($name, 3));
            if (isset($this[$key])) {
                return $this[$key];
            }
        }
        return null;
    }

    /**
     * Get instance of a class using parameter autodetect
     *
     * @param $className
     *
     * @return object
     * @throws Exception
     */
    public function getInstance($className)
    {
        $class = new ReflectionClass($className);
        if ($instance = $this->getServiceEntry($class)) {
            return $instance;
        }

        $dependencies = array();
        if (!is_null($class->getConstructor())) {
            $params = $class->getConstructor()->getParameters();
            foreach ($params as $param) {
                $dependentClass = $param->getClass();
                if ($dependentClass) {
                    $dependentClassName  = $dependentClass->name;
                    $dependentReflection = new ReflectionClass($dependentClassName);
                    if ($dependentReflection->isInstantiable()) {
                        //use recursion to get dependencies
                        $dependencies[] = $this->getInstance($dependentClassName);
                    } elseif ($dependentReflection->isInterface()) {
                        // Interfaces need to be pre-registered in the container
                        if ($concrete = $this->getServiceEntry($dependentReflection, true)) {
                            $dependencies[] = $concrete;
                        }
                    }
                }
            }
        }

        $instance = $class->newInstanceArgs($dependencies);
        return $instance;
    }

    /**
     * Find a service in the container based on class name
     * Classes can be registered either through their short name
     * or full class name. Short name take precedence.
     *
     * @param ReflectionClass $class
     * @param bool            $require
     *
     * @return object|null
     * @throws Exception
     */
    protected function getServiceEntry(ReflectionClass $class, $require = false)
    {
        $key = strtolower($class->getShortName());
        if (isset($this[$key])) {
            return $this[$key];
        }

        $name = $class->getName();
        if (isset($this[$name])) {
            return $this[$name];
        }

        if ($require) {
            throw new Exception($class->getName() . ' -  is not registered in the container');
        }

        return null;
    }
}

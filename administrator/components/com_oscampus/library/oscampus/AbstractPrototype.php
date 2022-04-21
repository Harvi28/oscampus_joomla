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

namespace Oscampus;

use DateTime;
use Exception;
use OscampusFactory;
use ReflectionClass;
use ReflectionProperty;

defined('_JEXEC') or die();

abstract class AbstractPrototype
{
    /**
     * @var ReflectionProperty[]
     */
    protected $defaultValues = null;

    /**
     * @var string[]
     */
    protected $dateProperties = array();

    /**
     * AbstractPrototype constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $presets = get_object_vars($this);
        $this->setProperties($presets);
    }

    /**
     * @throws Exception
     */
    public function __clone()
    {
        $this->setProperties(array(), true);
    }

    /**
     * Return all public properties as an array using transformations
     * for use in database queries
     *
     * @return array
     * @throws Exception
     */
    public function toArray()
    {
        $properties = $this->getDefaults();
        foreach ($properties as $name => $default) {
            if ($this->$name instanceof DateTime) {
                $properties[$name] = $this->$name->format('Y-m-d H:i:s');
            } else {
                $properties[$name] = $this->$name;
            }
        }

        return $properties;
    }

    /**
     * Wrapper for toArray() to return an object
     *
     * @return object
     * @throws Exception
     */
    public function toObject()
    {
        return (object)$this->toArray();
    }

    /**
     * Wrapper for toArray() to return a JSON string
     *
     * @return string
     * @throws Exception
     */
    public function toString()
    {
        return json_encode($this->toArray());
    }

    /**
     * @return string
     * @throws Exception
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * Set public properties using an array. Optionally set all
     * other properties not referenced/
     *
     * @param array $data
     * @param bool  $setAll
     *
     * @return void
     * @throws Exception
     */
    public function setProperties(array $data = array(), $setAll = false)
    {
        $properties = $this->getDefaults();

        foreach ($properties as $name => $default) {
            if (array_key_exists($name, $data)) {
                $this->setProperty($name, $data[$name]);

            } elseif ($setAll) {
                $this->$name = $default;
            }
        }
    }

    /**
     * Set a single property, applying any transformations that may be needed
     *
     * @param string $name
     * @param mixed  $value
     */
    public function setProperty($name, $value)
    {
        if (in_array($name, $this->dateProperties) && property_exists($this, $name)) {
            if (is_string($value) && substr($value, 0, 10) != '0000-00-00') {
                $this->$name = OscampusFactory::getDate($value);

            } elseif (is_numeric($value)) {
                $this->$name = OscampusFactory::getDate();
                $this->$name->setTimestamp($value);

            } elseif ($value instanceof DateTime) {
                $this->$name = $value;

            } else {
                $this->$name = null;
            }
        } else {
            $this->$name = $value;
        }
    }

    /**
     * Find all public properties and their default values
     *
     * @return array
     * @throws Exception
     */
    protected function getDefaults()
    {
        if ($this->defaultValues === null) {
            $this->defaultValues = array();

            $class         = new ReflectionClass($this);
            $properties    = $class->getProperties(ReflectionProperty::IS_PUBLIC);
            $defaultValues = $class->getDefaultProperties();

            foreach ($properties as $property) {
                $name = $property->name;

                $this->defaultValues[$name] = $defaultValues[$name];
            }
        }

        return $this->defaultValues;
    }
}

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

use Joomla\CMS\Filter\InputFilter;

defined('_JEXEC') or die();

jimport('joomla.filter.input');

class OscampusFilterInput extends InputFilter
{
    protected static $instances = [];

    /**
     * @inheritDoc
     *
     * @return OscampusFilterInput
     */
    public static function &getInstance(
        $tagsArray = [],
        $attrArray = [],
        $tagsMethod = 0,
        $attrMethod = 0,
        $xssAuto = 1,
        $stripUSC = -1
    ) {
        $sig = md5(serialize([$tagsArray, $attrArray, $tagsMethod, $attrMethod, $xssAuto]));

        if (empty(static::$instances[$sig])) {
            static::$instances[$sig] = new static($tagsArray, $attrArray, $tagsMethod, $attrMethod, $xssAuto);
        }

        return static::$instances[$sig];
    }

    public function clean($source, $type = 'string')
    {
        switch (strtoupper($type)) {
            case 'ARRAY_KEYS':
                if (is_array($source)) {
                    $result = $this->cleanArray($source);
                } else {
                    $result = $this->remove($this->decode($source));
                }
                break;

            default:
                $result = parent::clean($source, $type);
                break;
        }

        return $result;
    }

    /**
     * Filter an array and its keys to strings. Will recognize a key
     * of 'username' and use the username filter
     *
     * @param array $source
     *
     * @return array
     */
    protected function cleanArray(array $source): array
    {
        $result = [];
        foreach ($source as $key => $value) {
            $key = $this->remove($this->decode($key));
            if (is_string($value)) {
                $filter       = ($key == 'username' ? 'username' : 'string');
                $result[$key] = $this->clean($value, $filter);
            } else {
                $result[$key] = $this->cleanArray($value);
            }
        }
        return $result;
    }
}

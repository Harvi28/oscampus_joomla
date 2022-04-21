<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2018-2021 Joomlashack.com. All rights reserved
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

namespace Oscampus\Lesson;

use Oscampus\AbstractBase;

defined('_JEXEC') or die();

/**
 * Class Download
 *
 * @property-read string $id
 * @property-read string $type
 * @property-read string $title
 * @property-read int    $limit
 * @property-read int    $period
 * @property-read string $signupLink
 * @property-read string $upgradeLink
 * @property-read string $mimetype
 * @property-read string $filename
 * @property-read string $url
 */
class Download extends AbstractBase
{
    /**
     * @var string
     */
    protected $id = null;

    /**
     * @var string`
     */
    protected $type = null;

    /**
     * @var string
     */
    protected $title = null;

    /**
     * @var int
     */
    protected $limit = null;

    /**
     * @var int
     */
    protected $period = null;

    /**
     * @var string
     */
    protected $signupLink = null;

    /**
     * @var string
     */
    protected $upgradeLink = null;

    /**
     * @var string
     */
    protected $mimetype = null;

    /**
     * @var string
     */
    protected $filename = null;

    /**
     * @var string
     */
    protected $url = null;

    public function set($properties, $value = null)
    {
        if (!is_array($properties)) {
            $properties = array($properties => $value);
        }

        foreach ($properties as $property => $value) {
            if (property_exists($this, $property)) {
                $this->$property = $value;
            }
        }
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }

        return null;
    }
}

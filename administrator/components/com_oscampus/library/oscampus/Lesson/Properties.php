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

namespace Oscampus\Lesson;

use JHtml;
use Oscampus\Lesson\Type\AbstractType;
use OscampusFactory;

defined('_JEXEC') or die();

class Properties
{
    /**
     * @var int
     */
    public $id = null;

    /**
     * @var int
     */
    public $modules_id = null;

    /**
     * @var int
     */
    public $courses_id = null;

    /**
     * @var string
     */
    public $type = null;

    /**
     * @var string
     */
    public $title = null;

    /**
     * @var string
     */
    public $alias = null;

    /**
     * @var mixed
     */
    public $content = null;

    /**
     * @var string
     */
    public $description = null;

    /**
     * @var int
     */
    public $access = null;

    /**
     * @var bool
     */
    public $published = null;

    /**
     * @var null
     */
    public $icon = null;

    /**
     * @var bool
     */
    protected $authorised = null;

    public function __construct($data = null)
    {
        if ($data) {
            $this->load($data);
        }
    }

    public function __clone()
    {
        $properties = array_keys(get_object_vars($this));
        foreach ($properties as $property) {
            $this->$property = null;
        }
    }

    /**
     * Load properties. We trust that everything needed is being passed
     *
     * @param array|object $data
     *
     * @return Properties
     */
    public function load($data)
    {
        if ($data) {
            if (is_object($data)) {
                $data = get_object_vars($data);
            }

            foreach ($data as $property => $value) {
                if (property_exists($this, $property)) {
                    $this->$property = $value;
                }
            }

            if ($this->description) {
                $this->description = JHtml::_('content.prepare', $this->description);
            }

            $this->icon       = Type\AbstractType::getIcon($this->type);
            $this->authorised = $this->isAuthorised();
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isAuthorised()
    {
        if ($this->authorised === null) {
            $this->authorised = AbstractType::isAuthorised($this->access, $this->type);
        }

        return $this->authorised;
    }
}

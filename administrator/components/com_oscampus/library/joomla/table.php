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

use Joomla\CMS\Table\Table;

defined('_JEXEC') or die();

abstract class OscampusTable extends Table
{
    /**
     * @inheritDoc
     */
    public static function getInstance($type, $prefix = 'OscampusTable', $config = [])
    {
        return parent::getInstance($type, $prefix, $config);
    }

    /**
     * @param boolean $updateNulls [optional]
     *
     * @return boolean
     */
    public function store($updateNulls = false)
    {
        $date = OscampusFactory::getDate()->toSql();
        $user = OscampusFactory::getUser();

        // Update standard created fields if they exist
        $key = $this->_tbl_key;
        if ($key && empty($this->{$key})) {
            if (property_exists($this, 'created')) {
                if ($this->created instanceof DateTime) {
                    $this->created = $this->created->format('Y-m-d H:i:s');
                } elseif (!is_string($this->created) || empty($this->created)) {
                    $this->created = $date;
                }
            }

            if (!empty($user->id)) {
                if (property_exists($this, 'created_by')) {
                    $this->created_by = $this->created_by ?: $user->id;
                }
                if (property_exists($this, 'created_by_alias')) {
                    $this->created_by_alias = $this->created_by_alias ?: $user->name;
                }
            }
        }

        // Update modified fields if they exist
        if (!$key || !empty($this->{$key})) {
            if (property_exists($this, 'modified')) {
                $this->modified = $date;
                if (!empty($user->id) && property_exists($this, 'modified_by')) {
                    $this->modified_by = $user->id;
                }
            }
        }

        // Updates for publishing date fields
        $updateNulls = $this->setNull(['publish_up', 'publish_down']) || $updateNulls;

        return parent::store($updateNulls);
    }

    /**
     * @inheritDoc
     */
    public function bind($src, $ignore = '')
    {
        if (parent::bind($src, $ignore)) {
            // If a table has both alias and title fields, auto-fill an empty alias from the title
            if (property_exists($this, 'alias') && property_exists($this, 'title')) {
                if (empty($this->alias) && !empty($this->title)) {
                    $this->alias = $this->title;
                }
                $this->alias = OscampusApplicationHelper::stringURLSafe($this->alias);
            }
            return true;
        }

        return false;
    }

    /**
     * If property is empty, set to null and indicate null was found.
     * This was primarily created to handle null dates consistently
     *
     * @param string|string[] $properties
     *
     * @return bool
     */
    protected function setNull($properties): bool
    {
        if (is_string($properties)) {
            $properties = [$properties];
        }

        $handleNull = false;
        foreach ($properties as $property) {
            if (property_exists($this, $property) && empty($this->{$property})) {
                $this->{$property} = null;

                $handleNull = true;
            }
        }

        return $handleNull;
    }
}

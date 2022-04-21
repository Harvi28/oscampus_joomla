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

defined('_JEXEC') or die();


class OscampusModelTeacher extends OscampusModelAdmin
{
    protected function loadFormData()
    {
        if ($data = parent::loadFormData()) {
            if (is_object($data) && !empty($data->links) && is_string($data->links)) {
                $data->links = json_decode($data->links);
            }
        }

        return $data;
    }

    /**
     * @param JTable $table
     *
     * @return void
     */
    protected function prepareTable($table)
    {
        if (!empty($table->links) && !is_string($table->links)) {
            $table->links = json_encode($table->links);
        }
    }

    public function validate($form, $data, $group = null)
    {
        $fixedData = $data;
        if (isset($fixedData['links'])) {
            foreach ($fixedData['links'] as $type => $value) {
                $fixedData['links'][$type] = isset($value['link']) ? $value['link'] : '';
            }
        }
        if (parent::validate($form, $fixedData, $group)) {
            return $data;
        }
        return false;
    }
}

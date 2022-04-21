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

use Joomla\String\StringHelper;

defined('_JEXEC') or die();


class OscampusModelTag extends OscampusModelAdmin
{
    /**
     * @param JTable $table
     *
     * @return void
     * @throws Exception
     */
    protected function prepareTable($table)
    {
        if (empty($table->alias)) {
            $table->alias = $table->title;
        }

        $table->alias = strtolower(preg_replace('/[^a-z0-9\-]*/i', '', $table->alias));

        // Check if the alias and title already exists
        $table->alias = $this->generateNewAlias($table->id, $table->alias);
    }

    /**
     * Method to change the title & alias
     *
     * @param   integer $id    The id
     * @param   string  $alias The alias
     *
     * @return  string  The modified alias
     * @throws Exception
     */
    protected function generateNewAlias($id, $alias)
    {
        // Alter the title & alias
        $table = $this->getTable();

        while ($table->load(array('alias' => $alias)) && (@$table->id != $id)) {
            $alias = StringHelper::increment($alias, 'dash');
        }

        return $alias;
    }
}

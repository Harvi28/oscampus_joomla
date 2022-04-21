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

defined('_JEXEC') or die();

class PlgUserOscampus extends JPlugin
{
    /**
     * @return void
     */
    public function onUserAfterDelete()
    {
        if ($this->isEnabled()) {
            $this->garbageCollect();
        }
    }

    protected function garbageCollect()
    {
        if ($this->params->get('purge', 1)) {
            $db = JFactory::getDbo();

            $delete = array(
                'users_lessons' => 'users_id',
                'certificates'  => 'users_id',
                'teachers'      => 'users_id'
            );
            foreach ($delete as $table => $field) {
                $query = array(
                    'DELETE target',
                    sprintf('FROM #__oscampus_%s AS target', $table),
                    sprintf('LEFT JOIN #__users AS u ON u.id = %s', $field),
                    'WHERE u.id IS NULL'
                );

                try {
                    $db->setQuery(join(' ', $query))->execute();

                } catch (Throwable $e) {
                    // ignore errors
                }
            }
        }
    }

    /**
     * Checks that oscampus at least LOOKS installed
     *
     * @return bool
     */
    protected function isEnabled()
    {
        return is_dir(JPATH_ADMINISTRATOR . '/components/com_oscampus');
    }
}

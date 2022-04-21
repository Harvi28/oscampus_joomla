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

class OscampusTableLessons extends OscampusTable
{
    protected $_jsonEncode = array('metadata');

    /**
     * @param JDatabaseDriver $db
     */
    public function __construct(&$db)
    {
        parent::__construct('#__oscampus_lessons', 'id', $db);

        // Override Joomla global access defaulting
        $defaultAccess = OscampusFactory::getContainer()->params->get('access.lesson');
        $this->access  = $defaultAccess;
    }

    public function check()
    {
        if (!$this->modules_id) {
            $this->setError(JText::_('COM_OSCAMPUS_ERROR_LESSONS_REQUIRED_MODULE'));
            return false;
        }

        if (!$this->alias) {
            $this->setError(JText::_('COM_OSCAMPUS_ERROR_LESSONS_REQUIRED_ALIAS'));
            return false;

        } else {
            $db       = $this->getDbo();
            $subQuery = $db->getQuery(true)
                ->select('courses_id')
                ->from('#__oscampus_modules')
                ->where('id = ' . (int)$this->modules_id);

            $query = $db->getQuery(true)
                ->select('lesson.id')
                ->from('#__oscampus_modules AS module')
                ->innerJoin('#__oscampus_lessons AS lesson ON lesson.modules_id = module.id')
                ->where(
                    array(
                        "module.courses_id IN ({$subQuery})",
                        'lesson.alias = ' . $db->quote($this->alias),
                        'lesson.id != ' . (int)$this->id
                    )
                );

            $duplicates = $db->setQuery($query)->loadColumn();

            if ($duplicates) {
                $this->setError(JText::sprintf('COM_OSCAMPUS_ERROR_LESSONS_DUPLICATE_ALIAS', $this->alias));
                return false;
            }
        }

        return true;
    }
}

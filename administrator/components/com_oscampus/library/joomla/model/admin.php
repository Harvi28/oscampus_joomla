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

use Joomla\Registry\Registry as Registry;
use Joomla\String\StringHelper;
use Oscampus\String\Inflector;

defined('_JEXEC') or die();

abstract class OscampusModelAdmin extends JModelAdmin
{
    use OscampusModelTrait;

    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            if (!empty($item->metadata)) {
                $metadata       = new Registry($item->metadata);
                $item->metadata = $metadata->toArray();
            }
        }
        return $item;
    }

    public function getTable($type = '', $prefix = 'OscampusTable', $config = array())
    {
        if (empty($type)) {
            $inflector = Inflector::getInstance();
            $type      = $inflector->toPlural($this->name);
        }
        return OscampusTable::getInstance($type, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true)
    {
        $form = $this->loadForm(
            'com_oscampus.' . $this->name,
            $this->name,
            array('control' => 'jform', 'load_data' => $loadData)
        );
        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * @return object|array
     * @throws Exception
     */
    protected function loadFormData()
    {
        $data = OscampusFactory::getApplication()->getUserState("com_oscampus.edit.{$this->name}.data", array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Utility method for updating standard junction tables.
     *
     * @param string $baseField     Sting in the form table_name.field_name
     * @param int    $baseId        The id of the base ID for the junction
     * @param string $junctionField The field name that is the FK in the target table
     * @param int[]  $junctionIds   The ids for the target table
     *
     * @return bool
     */
    protected function updateJunctionTable($baseField, $baseId, $junctionField, array $junctionIds)
    {
        if ($baseId > 0) {
            $atoms     = explode('.', $baseField);
            $baseField = array_pop($atoms);
            $table     = array_pop($atoms);

            if (empty($table)) {
                $this->setError(JText::sprintf('COM_OSCAMPUS_ERROR_JUNCTION_UPDATE', __CLASS__, __METHOD__));
                return false;
            }

            $db = $this->getDbo();

            $db->setQuery(
                sprintf(
                    'DELETE FROM %s WHERE courses_id = %s',
                    $db->quoteName($table),
                    $baseId
                )
            )
                ->execute();
            if ($error = $db->getErrorMsg()) {
                $this->setError($error);
                return false;
            }

            if ($ids = array_unique(array_filter($junctionIds))) {
                $inserts = array_map(
                    function ($row) use ($baseId) {
                        return sprintf('%s, %s', $baseId, $row);
                    },
                    $ids
                );

                $query = $db->getQuery(true)
                    ->insert($table)
                    ->columns(array($baseField, $junctionField))
                    ->values($inserts);

                $db->setQuery($query)->execute();
                if ($error = $db->getErrorMsg()) {
                    $this->setError($error);
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @param array $data
     *
     * @return bool
     * @throws Exception
     */
    public function save($data)
    {
        $app = OscampusFactory::getApplication();

        if ($app->input->getCmd('task') == 'save2copy') {
            if (isset($data['title']) && isset($data['alias'])) {
                // save2copy is supported by this table
                $title = $data['title'];
                $alias = $data['alias'];
                $table = clone $this->getTable();

                while ($table->load(array('alias' => $alias))) {
                    if ($title == $table->title) {
                        $title = StringHelper::increment($title);
                    }
                    $alias = StringHelper::increment($alias, 'dash');
                }

                $data['title']     = $title;
                $data['alias']     = $alias;
                $data['published'] = 0;

            } else {
                $this->setError(JText::_('COM_OSCAMPUS_ERROR_SAVE2COPY_NOT_SUPPORTED'));

                return false;
            }
        }

        return parent::save($data);
    }
}

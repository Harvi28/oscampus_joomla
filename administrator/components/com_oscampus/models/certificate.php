<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2019 Joomlashack.com. All rights reserved
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

class OscampusModelCertificate extends OscampusModelAdmin
{
    /**
     * @var JObject
     */
    protected $item = null;

    /**
     * @param int $id
     *
     * @return object
     * @throws Exception
     */
    public function getItem($pk = null)
    {
        if ($this->item === null) {
            $this->item = parent::getItem($pk);
            if (is_string($this->item->movable)) {
                $this->item->movable = json_decode($this->item->movable);
            }
            if (empty($this->item->fontsize)) {
                $this->item->fontsize = '';
            }
        }

        return $this->item;
    }

    /**
     * @param OscampusTableCertificates $table
     */
    protected function prepareTable($table)
    {
        parent::prepareTable($table);

        if (!$table->default) {
            $db = $this->getDbo();

            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from('#__oscampus_certificates')
                ->where(
                    array(
                        $db->quoteName('default') . ' != 0',
                        'id != ' . (int)$table->id
                    )
                );

            $default = $db->setQuery($query)->loadResult();

            if (!$default) {
                $table->default = 1;
            }
        }
    }

    public function publish(&$pks, $value = 1)
    {
        if (!$value) {
            $pks = array_unique(array_filter(array_map('intval', (array)$pks)));

            if ($defaults = $this->findDefaults($pks)) {
                $pks = array_diff($pks, $defaults);

                JFactory::getApplication()->enqueueMessage(JText::_('COM_OSCAMPUS_NOTICE_CERTIFICATE_UNPUBLISH_DEFAULT'), 'notice');
            }

        }

        return parent::publish($pks, $value);
    }

    public function delete(&$pks)
    {
        $pks = array_unique(array_filter(array_map('intval', (array)$pks)));

        if ($defaults = $this->findDefaults($pks)) {
            $pks = array_diff($pks, $defaults);

            JFactory::getApplication()->enqueueMessage(JText::_('COM_OSCAMPUS_NOTICE_CERTIFICATE_DELETE_DEFAULT'), 'notice');
        }

        return parent::delete($pks);
    }

    /**
     * @param array $pks
     *
     * @return array
     */
    protected function findDefaults(array $pks = null)
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__oscampus_certificates')
            ->where($db->quoteName('default') . ' != 0');

        if ($pks) {
            $query->where(sprintf('id IN (%s)', join(',', $pks)));
        }

        $defaults = $db->setQuery($query)->loadColumn();

        return $defaults;
    }

    /**
     * @param int $id
     *
     * @throws Exception
     */
    public function setDefault($id)
    {
        if ($id) {
            $certificate = $this->getItem($id);

            if (!$certificate->published) {
                throw new Exception(JText::_('COM_OSCAMPUS_ERROR_CERTIFICATE_UNPUBLISHED_DEFAULT'));
            }

            $db = $this->getDbo();

            $query = $db->getQuery(true)
                ->update('#__oscampus_certificates')
                ->set($db->quoteName('default') . ' = 0');

            $db->setQuery($query)->execute();

            $query->clear('set')
                ->set($db->quoteName('default') . ' = 1')
                ->where('id = ' . $id);

            $db->setQuery($query)->execute();
            if ($db->getAffectedRows() !== 1) {
                throw new Exception(JText::_('COM_OSCAMPUS_ERROR_DEFAULT_NOT_SET'));
            }
        }
    }
}

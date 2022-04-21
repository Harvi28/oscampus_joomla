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

use Oscampus\String\Inflector;

defined('_JEXEC') or die();


abstract class OscampusViewAdminList extends OscampusViewAdmin
{
    /**
     * @var mixed[]
     */
    public $activeFilters = null;

    /**
     * @var OscampusModelAdminList
     */
    protected $model = null;

    /**
     * @var JForm
     */
    public $filterForm = null;

    /**
     * Default admin screen title
     *
     * @param string $sub
     * @param string $icon
     *
     * @return void
     * @throws Exception
     */
    protected function setTitle($sub = null, $icon = 'oscampus')
    {
        $name = strtoupper($this->getName());

        parent::setTitle('COM_OSCAMPUS_SUBMENU_' . $name, $icon);
    }

    protected function setup()
    {
        $this->model = $this->getModel();

        // get model state and ensure state is initialised
        $state = $this->model->getState();

        // Standard properties for Joomla list views
        $this->activeFilters = $this->model->getActiveFilters();
        $this->filterForm    = $this->model->getFilterForm();

        $ordering = array(
            'enabled'   => false,
            'field'     => null,
            'prefix'    => null,
            'order'     => $this->escape($state->get('list.ordering')),
            'direction' => $this->escape($state->get('list.direction'))
        );

        // Standard variables for use in twig templates
        $this->setVariable('items', $this->model->getItems());
        $this->setVariable('pagination', $this->model->getPagination());
        $this->setVariable('ordering', $ordering);

        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors));
        }

        parent::setup();
    }

    /**
     * Set the standard ordering object values for use in Twig
     *
     * @param string $field
     * @param string $prefix
     * @param bool   $enabled
     *
     * @return void
     */
    protected function setOrdering($field = null, $prefix = null, $enabled = false)
    {
        $ordering = array_merge(
            $this->getVariable('ordering', array()),
            array(
                'enabled' => $enabled,
                'field'   => $field,
                'prefix'  => $prefix
            )
        );

        $this->setVariable('ordering', $ordering);
    }

    /**
     * Complement to setOrdering()
     *
     * @return object
     */
    protected function getOrdering()
    {
        return $this->getVariable('ordering');
    }

    /**
     * Method to set default buttons to the toolbar
     *
     * @return void
     * @throws Exception
     */
    protected function setToolbar()
    {
        $inflector = Inflector::getInstance();

        $plural   = $this->getName();
        $singular = $inflector->toSingular($plural);

        OscampusToolbarHelper::addNew($singular . '.add');
        OscampusToolbarHelper::editList($singular . '.edit');

        $table = $this->getModel()->getTable();
        if (array_key_exists('published', get_object_vars($table))) {
            OscampusToolbarHelper::publish($plural . '.publish', 'JTOOLBAR_PUBLISH', true);
            OscampusToolbarHelper::unpublish($plural . '.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        }

        OscampusToolbarHelper::deleteList('COM_OSCAMPUS_DELETE_CONFIRM', $plural . '.delete');

        if ($this->getVariable('batchForm')) {
            OscampusToolbarHelper::batch();
        }

        parent::setToolbar();
    }

    /**
     * Return a group ID for determining if an item is at the top or bottom of a scoped sorting group
     *
     * @param object $item
     *
     * @return string
     */
    public function getSortGroupId($item)
    {
        return '';
    }

    /**
     * Determine if the selected item is at the top of a sorting group
     *
     * @param int   $index
     * @param array $items
     *
     * @return bool
     */
    public function isSortGroupTop($index, $items)
    {
        if ($index == 0) {
            return true;
        }

        if (isset($items[$index])) {
            $currentSortGroupId = $this->getSortGroupId($items[$index]);
            $lastSortGroupId    = isset($items[$index - 1]) ? $this->getSortGroupId($items[$index - 1]) : '';
            return $currentSortGroupId != $lastSortGroupId;
        }

        return false;
    }

    /**
     * Determine if the selected item is at the bottom of a sorting group
     *
     * @param int   $index
     * @param array $items
     *
     * @return bool
     */
    public function isSortGroupBottom($index, $items)
    {
        if (count($items) == ($index + 1)) {
            return true;
        }

        if (isset($items[$index])) {
            $currentSortGroupId = $this->getSortGroupId($items[$index]);
            $nextSortGroupId    = isset($items[$index + 1]) ? $this->getSortGroupId($items[$index + 1]) : '';
            return $currentSortGroupId != $nextSortGroupId;
        }

        return false;
    }

    /**
     * Render a batch form for use in twig templates
     *
     * @param string $body
     * @param string $footer
     * @param array  $params
     *
     * @return void
     * @throws Exception
     */
    protected function setBatchForm($body, $footer, $params = array())
    {
        $params = array_merge(
            $params,
            array(
                'footer' => $footer
            )
        );
        if (!isset($params['title'])) {
            $params['title'] = JText::_('COM_OSCAMPUS_' . $this->getName() . '_BATCH_OPTIONS');
        }

        if ($form = JHtml::_('bootstrap.renderModal', 'collapseModal', $params, $body)) {
            OscampusToolbarHelper::batch();
            $this->setVariable('batchForm', $form);
        }
    }
}

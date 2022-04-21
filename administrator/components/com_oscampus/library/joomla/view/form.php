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

abstract class OscampusViewForm extends OscampusViewAdmin
{
    /**
     * @var OscampusModelAdmin
     */
    protected $model = null;

    /**
     * @var JForm
     */
    protected $form = null;

    /**
     * @var OscampusTable
     */
    protected $item = null;

    protected function setup()
    {
        $this->model = $this->getModel();
        $this->form  = $this->model->getForm();
        $this->item  = $this->model->getItem();

        $this->setVariable(
            array(
                'form' => $this->form,
                'item' => $this->item
            )
        );

        parent::setup();
    }

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
        $isNew = empty($this->item->id);
        $name  = strtoupper($this->getName());
        $title = "COM_OSCAMPUS_PAGE_VIEW_{$name}_" . ($isNew ? 'ADD' : 'EDIT');

        parent::setTitle($title, $icon);
    }

    /**
     * Method to set default buttons to the toolbar
     *
     * @param bool $save2Copy
     *
     * @return  void
     * @throws Exception
     */
    protected function setToolbar($save2Copy = false)
    {
        OscampusFactory::getApplication()->input->set('hidemainmenu', true);

        $controller = $this->getName();

        OscampusToolbarHelper::apply($controller . '.apply');
        OscampusToolbarHelper::save($controller . '.save');
        OscampusToolbarHelper::save2new($controller . '.save2new');

        if ($save2Copy) {
            OscampusToolbarHelper::save2copy($controller . '.save2copy');
        }

        $isNew = empty($this->item->id);
        $alt   = $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE';
        OscampusToolbarHelper::cancel($controller . '.cancel', $alt);
    }
}

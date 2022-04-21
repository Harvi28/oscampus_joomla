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

use Joomla\CMS\Pagination\Pagination;

defined('_JEXEC') or die();

class OscampusViewPathway extends OscampusViewSite
{
    /**
     * @var OscampusModelPathway
     */
    protected $model = null;

    /**
     * @var object[]
     */
    protected $items = [];

    /**
     * @var Pagination
     */
    protected $pagination = null;

    /**
     * @var object
     */
    protected $pathway = null;

    public function display($tpl = null)
    {
        $this->model = $this->getModel();

        $this->setPathway();
        $this->items      = $this->model->getItems();
        $this->pagination = $this->model->getPagination();

        $this->setMetadata(
            $this->pathway->metadata,
            $this->pathway->title,
            $this->pathway->description
        );

        parent::display($tpl);
    }

    /**
     * Check for possible duplicate urls when individual pathway menus are created
     * Set breadcrumbs accordingly. Should be called exactly once
     *
     * @return void
     * @throws Exception
     */
    protected function setPathway()
    {
        if ($this->pathway === null) {
            $app = OscampusFactory::getApplication();

            $this->pathway = $this->model->getPathway();

            $layout    = $app->input->getCmd('layout');
            $pathwayId = $app->input->getInt('pid');

            $pathwayTarget = OscampusRoute::getInstance()->getQuery('pathway', $layout, $pathwayId);
            if (!empty($pathwayTarget['Itemid'])) {
                $targetId = $pathwayTarget['Itemid'];
                if ($targetId != $app->input->getInt('Itemid')) {
                    // There is a designated menu for this pathway so redirect to it
                    $app->redirect(JRoute::_('index.php?' . http_build_query($pathwayTarget)));

                }
            }

            if (!empty($pathwayTarget['view'])) {
                $pathway = OscampusFactory::getApplication()->getPathway();
                $pathway->addItem($this->pathway->title);
            }
        }
    }
}

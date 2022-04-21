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

use Oscampus\Pathway;

defined('_JEXEC') or die();


class OscampusModelPathway extends OscampusModelAdmin
{
    public function getForm($data = array(), $loadData = true)
    {
        $params = OscampusFactory::getContainer()->params;
        $form   = parent::getForm($data, $loadData);

        // @TODO: finish implementation of custom pathways
        if (!$params->get('advanced.pathowners', 0)) {
            $form->removeField('users_id');
        }

        return $form;
    }

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        if (!$item->id) {
            $item->access    = 1;
            $item->published = 1;
            $item->image     = Pathway::DEFAULT_IMAGE;
        }

        return $item;
    }

    public function save($data)
    {
        if (empty($data['image'])) {
            $data['image'] = Pathway::DEFAULT_IMAGE;
        }

        return parent::save($data);
    }
}

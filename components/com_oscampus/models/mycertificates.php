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

class OscampusModelMycertificates extends OscampusModelSiteList
{
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);

        // No pagination for this model
        $this->setState('list.limit', 0);
        $this->setState('list.start', 0);
    }

    public function getListQuery()
    {
        $user = OscampusFactory::getUser($this->getState('user.id'));

        $query = parent::getListQuery()
            ->select(
                array(
                    'certificate.*',
                    'course.difficulty',
                    'course.length',
                    'course.title',
                    'course.image'
                )
            )
            ->from('#__oscampus_courses_certificates AS certificate')
            ->innerJoin('#__oscampus_courses AS course ON course.id = certificate.courses_id')
            ->where(
                array(
                    'certificate.users_id = ' . $user->id,
                )
            )
            ->group('course.id')
            ->order('course.title ASC');

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();

        foreach ($items as $item) {
            if (!$item->date_earned instanceof DateTime) {
                $item->date_earned = OscampusFactory::getDate($item->date_earned);
            }
        }

        return $items;
    }
}

<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2017-2021 Joomlashack.com. All rights reserved
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

use Joomla\CMS\Plugin\PluginHelper;

defined('_JEXEC') or die();

trait OscampusTraitObservable
{
    /**
     * @var JObserverUpdater
     */
    protected $observers = null;

    /**
     * @var OscampusEventDispatcher
     */
    protected $dispatcher = null;

    protected function attachAllObservers(JObservableInterface $model)
    {
        $this->observers = new JObserverUpdater($model);
        JObserverMapper::attachAllObservers($model);
    }

    /**
     * @param JObserverInterface $observer
     */
    public function attachObserver(JObserverInterface $observer)
    {
        $this->observers->attachObserver($observer);
    }

    /**
     * @param string $event
     * @param array  $params
     *
     * @return void
     */
    protected function oscampusTrigger(string $event, array $params)
    {
        if ($this->observers) {
            $this->observers->update($event, $params);
        }

        if ($this->dispatcher === null) {
            $this->dispatcher = OscampusEventDispatcher::getInstance();
            PluginHelper::importPlugin('oscampus');
        }
        $this->dispatcher->trigger($event, $params);
    }
}

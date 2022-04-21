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

defined('_JEXEC') or die();

abstract class OscampusModelSite extends OscampusModel
{
    /**
     * @var string
     */
    protected $context = null;

    /**
     * OscampusModelSite constructor.
     *
     * @param array $config
     *
     * @return void
     * @throws Exception
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->context = $this->option . '.' . $this->name;
    }

    public function getState($property = null, $default = null)
    {
        $init  = !$this->__state_set;
        $state = parent::getState();
        if ($init) {
            $params = OscampusFactory::getContainer()->params;
            $state->set('parameters.component', $params);
        }
        return parent::getState($property, $default);
    }

    /**
     * Get component params merged with menu params
     *
     * @return Registry
     */
    public function getParams()
    {
        /** @var Registry $merged */
        $state  = $this->getState();
        $merged = $state->get('parameters.merged', null);
        if ($merged === null) {
            $merged = clone $state->get('parameters.component');
            $menu   = $state->get('parameters.menu');
            $merged->merge($menu);
            $state->set('parameters.merged', $merged);
        }

        return $merged;
    }
}

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

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Object\CMSObject;

defined('_JEXEC') or die();

class OscampusView extends HtmlView
{
    /**
     * @var CMSApplication
     */
    protected $app = null;

    /**
     * @inheritDoc
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->app = OscampusFactory::getApplication();
    }

    /**
     * @return void
     */
    protected function setup()
    {
        // For use in subclasses
    }

    /**
     * @inheritDoc
     */
    public function display($tpl = null)
    {
        $this->setup();

        echo $this->displayHeader();
        parent::display($tpl);
        echo $this->displayFooter();
    }

    /**
     * Display a header on admin pages
     *
     * @return string
     */
    protected function displayHeader()
    {
        // To be set in subclasses
        return '';
    }

    /**
     * Display a standard footer on all admin pages
     *
     * @return string
     */
    protected function displayFooter()
    {
        // To be set in subclassess
        return '';
    }

    /**
     * @param string $property
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getState($property = null, $default = null)
    {
        if ($model = $this->getModel()) {
            return $model->getState($property, $default);
        }

        if ($property === null) {
            return new CMSObject();
        }

        return $default;
    }

    /**
     * @param string $name
     * @param string $layout
     *
     * @return string
     * @throws Exception
     */
    public function loadDefaultTemplate($name, $layout = 'default')
    {
        $currentLayout = $this->setLayout($layout);

        $output = $this->loadTemplate($name);

        $this->setLayout($currentLayout);

        return $output;
    }
}

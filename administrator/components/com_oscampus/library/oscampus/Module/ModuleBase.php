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

namespace Oscampus\Module;

use Exception;
use JDatabaseDriver;
use JModuleHelper;
use Joomla\CMS\Cache\CacheController;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use OscampusFactory;

defined('_JEXEC') or die();

class ModuleBase
{
    /**
     * @var string
     */
    protected $id = null;

    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var Registry
     */
    protected $params = null;

    /**
     * @var object
     */
    protected $module = null;

    /**
     * @var string
     */
    protected $scope = null;

    /**
     * @var JDatabaseDriver
     */
    protected $db = null;

    /**
     * @var CacheController
     */
    protected $cache = null;

    /**
     * @var int[]
     */
    protected static $instanceCount = [];

    /**
     * ModuleBase constructor.
     *
     * @param Registry $params
     * @param object   $module
     *
     * @throws Exception
     */
    public function __construct(Registry $params, object $module)
    {
        if (empty($module->module)) {
            throw new Exception(Text::sprintf('COM_OSCAMPUS_ERROR_MODULEBASE', __CLASS__), 500);
        }

        $this->params = $params;
        $this->module = $module;
        $this->name   = $module->module;

        $this->db    = OscampusFactory::getDbo();
        $this->cache = OscampusFactory::getCache($this->name, '');

        if (!isset(static::$instanceCount[$this->name])) {
            static::$instanceCount[$this->name] = 0;
        }
        static::$instanceCount[$this->name]++;
        $this->id = $this->name . '_' . static::$instanceCount[$this->name];
    }

    /**
     * @param mixed $keys
     *
     * @return string
     */
    protected function getStoreId($keys): string
    {
        return md5(json_encode($keys));
    }

    /**
     * @param string $layout
     *
     * @return void
     */
    public function output($layout = null)
    {
        OscampusFactory::getContainer()->theme->loadCss();

        $layout = $layout ?: $this->params->get('layout') ?: 'default';
        include JModuleHelper::getLayoutPath($this->name, $layout);
    }

    /**
     * @param string      $template
     * @param string|null $layout
     *
     * @return string
     */
    protected function loadTemplate(string $template, string $layout = null): string
    {
        $output = '';
        $layout = $layout ?: $this->params->get('layout') ?: 'default';

        if ($file = JModuleHelper::getLayoutPath($this->name, $layout . '_' . $template)) {
            ob_start();

            include $file;

            $output = ob_get_contents();
            ob_end_clean();
        }

        return $output;
    }
}

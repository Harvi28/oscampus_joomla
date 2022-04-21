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

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Oscampus\String\Inflector;

defined('_JEXEC') or die();

class OscampusControllerBase extends BaseController
{
    use OscampusControllerTraitBase;

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->initOscampus($config);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function display($cachable = false, $urlparams = [])
    {
        if ($this->app->isClient('administrator')) {
            $inflector = Inflector::getInstance();
            $view      = $this->app->input->getCmd('view', $this->default_view);
            $layout    = $this->app->input->getCmd('layout', '');
            $id        = $this->app->input->getInt('id');

            // Check for edit form.
            if (
                $inflector->isSingular($view)
                && $layout == 'edit'
                && !$this->checkEditId('com_oscampus.edit.' . $view, $id)
            ) {
                // Somehow the person just went to the form - we don't allow that.
                $this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
                $this->setMessage($this->getError(), 'error');

                $listView = $inflector->toPlural($view);
                $this->setRedirect(Route::_('index.php?option=com_oscampus&view=' . $listView, false));

                return $this;
            }
        }

        return parent::display();
    }
}

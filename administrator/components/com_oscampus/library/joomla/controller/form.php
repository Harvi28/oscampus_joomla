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
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Oscampus\String\Inflector;

defined('_JEXEC') or die();

abstract class OscampusControllerForm extends FormController
{
    use OscampusControllerTraitBase;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->initOscampus($config);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function batch($model = null)
    {
        $this->checkToken();

        $inflector = Inflector::getInstance();
        $view      = $this->app->input->getCmd('view', $this->default_view);

        if ($inflector->isPlural($view)) {
            $modelName = $inflector->toSingular($view);

            /** @var OscampusModelAdmin $model */
            $model = $this->getModel($modelName, '', []);

            $linkQuery = http_build_query([
                'option' => $this->app->input->getCmd('option'),
                'view'   => $view
            ]);
            $this->setRedirect(Route::_('index.php?' . $linkQuery . $this->getRedirectToListAppend(), false));

            return parent::batch($model);
        }

        throw new Exception(Text::_('COM_OSCAMPUS_ERROR_BATCH_METHOD'), 500);
    }
}

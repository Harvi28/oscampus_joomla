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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\User\User;

defined('_JEXEC') or die();

class OscampusViewStatistics extends OscampusViewSite
{
    /**
     * @var User
     */
    protected $user = null;

    /**
     * @var OscampusModelStatistics
     */
    protected $model = null;

    /**
     * @var bool
     */
    protected $showLimitBox = true;

    /**
     * @var object[]
     */
    protected $items = null;

    /**
     * @var Pagination
     */
    protected $pagination = null;

    /**
     * @param string $tpl
     *
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $this->user = OscampusFactory::getUser();

        if (!$this->user->authorise('core.tools', 'com_oscampus')) {
            throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 401);
        }

        $this->model        = $this->getModel();
        $this->showLimitBox = $this->model->getState('list.limitbox');

        if ($tpl = $this->prepareDataDisplay()) {
            $tpl = is_string($tpl) ? $tpl : null;
            try {
                parent::display($tpl);

            } catch (Throwable $e) {
                echo $e->getMessage();
            }
        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function prepareStatistics()
    {
        $this->setLayout('statistics');

        $app = OscampusFactory::getApplication();

        // Do this to set the default
        if ($reportKey = $app->input->getCmd('fullname')) {
            $app->input->set('fullname', $reportKey);
            $this->model->setState('report', $reportKey);

            $this->items = $this->model->getItems();
        }

        return true;
    }

    /**
     * Checks if this is a data/ajax display and does necessary setup
     *
     * @return bool|string
     * @throws Exception
     */
    protected function prepareDataDisplay()
    {
        $app      = OscampusFactory::getApplication();
        $doc      = OscampusFactory::getDocument();
        $success  = true;
        $template = null;

        switch ($doc->getType()) {
            case 'html':
                $this->addJavascript();
                break;

            case 'raw':
                // Ensure model state is initialized
                $this->model->getState();

                try {
                    $reportKeys = explode('.', $this->model->getState('report'));
                    $report     = array_shift($reportKeys);
                    $template   = join('_', $reportKeys);

                    if ($report) {
                        $method = 'prepare' . ucfirst(strtolower($report));
                        if (method_exists($this, $method)) {
                            $success = $this->$method();

                        } else {
                            $this->setLayout($report);

                            $this->items      = $this->model->getItems();
                            $this->pagination = $this->model->getPagination();
                        }

                        // Joomla 3.8.x still using legacy error methods in core :(
                        if ($errors = $this->model->getErrors()) {
                            throw new Exception(join('<br/>', $errors));
                        }

                    } else {
                        throw new Exception(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
                    }

                } catch (Throwable $e) {
                    $app->enqueueMessage($e->getMessage(), 'error');
                    $success = false;
                }

                break;

            default:
                $app->enqueueMessage(
                    Text::sprintf('COM_OSCAMPUS_ERROR_DOCUMENT_INVALID_FORMAT', $doc->getType()),
                    'error'
                );
                $success = false;
                break;
        }

        echo $this->loadDefaultTemplate('messages');

        return $template ?: $success;
    }

    /**
     * Add javascript support for the main display
     */
    protected function addJavascript()
    {
        HTMLHelper::_('osc.jquery');
        HTMLHelper::_('bootstrap.tooltip');
        HTMLHelper::_('script', 'com_oscampus/statistics.min.js', ['relative' => true]);

        $options = json_encode([
            'loading' => HTMLHelper::_('image', 'com_oscampus/loading.gif', 'loading', null, true)
        ]);
        Text::script('COM_OSCAMPUS_LOADING');
        Text::script('COM_OSCAMPUS_ERROR_GENERIC_PREFIX');
        HTMLHelper::_('osc.onready', "$.Oscampus.statistics.init({$options});");
    }
}

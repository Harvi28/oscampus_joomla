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

use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\User\User;

defined('_JEXEC') or die();

class OscampusViewStatistics extends OscampusViewAdminRaw
{
    /**
     * @var OscampusModelStatistics
     */
    protected $model = null;

    /**
     * @var object[]
     */
    protected $items = null;

    /**
     * @var Pagination
     */
    protected $pagination = null;

    /**
     * @var CMSObject
     */
    protected $state = null;

    /**
     * @var Form
     */
    public $filterForm = null;

    /**
     * @var array
     */
    public $activeFilters = null;

    /**
     * @var string
     */
    protected $loadingImage = null;

    /**
     * @var OscampusTableCourses
     */
    protected $course = null;

    /**
     * @var User
     */
    protected $student = null;

    /**
     * @inheritDoc
     */
    public function display($tpl = null)
    {
        $report = explode('.', $this->app->input->getCmd('report'), 2);
        $layout = array_shift($report);
        $tpl    = array_shift($report);

        $this->setLayout($layout);

        try {
            $this->model        = $this->getModel();
            $this->items        = $this->model->getItems();
            $this->loadingImage = HTMLHelper::_(
                'image',
                'com_oscampus/loading.gif',
                Text::_('COM_OSCAMPUS_LOADING'),
                null,
                true
            );

            $this->pagination    = $this->model->getPagination();
            $this->state         = $this->model->getState();
            $this->filterForm    = $this->model->getFilterForm();
            $this->activeFilters = $this->model->getActiveFilters();

            if ($courseId = $this->model->getState('course.id')) {
                $this->course = OscampusTable::getInstance('Courses');
                $this->course->load(['id' => $courseId]);
            }

            if ($studentId = $this->model->getState('user.id')) {
                $this->student = User::getInstance($studentId);
            }

            parent::display($tpl);

        } catch (Throwable $e) {
            echo Text::sprintf('COM_OSCAMPUS_ERROR_GENERIC_PREFIX', $e->getMessage());
            echo '<pre>' . print_r($e, 1) . '</pre>';
        }
    }
}

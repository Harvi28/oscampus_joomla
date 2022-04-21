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
use JHtml;
use Joomla\Registry\Registry as Registry;
use Joomla\Utilities\ArrayHelper;
use JText;
use OscampusFactory;
use OscampusModel;
use OscampusModelSearch;

defined('_JEXEC') or die();

class Search extends ModuleBase
{
    /**
     * @var OscampusModelSearch
     */
    protected $model = null;

    /**
     * @var bool
     */
    protected static $javascriptLoaded = false;

    /**
     * Search constructor.
     *
     * @param Registry $params
     * @param object   $module
     *
     * @return void
     * @throws Exception
     */
    public function __construct(Registry $params, $module)
    {
        parent::__construct($params, $module);

        $this->model = OscampusModel::getInstance('Search');
        OscampusFactory::getContainer()->theme->loadCss();
    }

    /**
     * Wrapper for the model state
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     * @throws Exception
     */
    protected function getState($name, $default = null)
    {
        return $this->model->getState($name, $default);
    }

    /**
     * Get an input filter by name
     *
     * @param string $name
     *
     * @return string|null
     */
    protected function getFilter($name)
    {
        $method = 'getFilter' . ucfirst(strtolower($name));
        if (method_exists($this, $method)) {
            if ($filter = $this->$method()) {
                return $filter;
            }
        }

        return null;
    }

    /**
     * Tag filter
     *
     * @return string
     * @throws Exception
     */
    protected function getFilterTag()
    {
        $tagQuery = $this->db->getQuery(true)
            ->select(
                array(
                    'tag.id AS ' . $this->db->quote('value'),
                    'tag.title AS ' . $this->db->quote('text')
                )
            )
            ->from('#__oscampus_tags AS tag')
            ->innerJoin('#__oscampus_courses_tags AS ct ON ct.tags_id = tag.id')
            ->innerJoin('#__oscampus_courses AS course ON course.id = ct.courses_id')
            ->where(
                array(
                    'course.published = 1',
                    $this->model->whereAccess('course.access'),
                    'course.publish_up <= UTC_TIMESTAMP()'
                )
            )
            ->group('tag.id')
            ->order('tag.title ASC');

        $tags = $this->db->setQuery($tagQuery)->loadObjectList();
        array_unshift(
            $tags,
            JHtml::_('select.option', '', JText::_('COM_OSCAMPUS_OPTION_SELECT_TAG'))
        );

        $selected = $this->model->getState('filter.tag');
        $class    = $this->getStateClass($selected);

        $html = JHtml::_(
            'select.genericlist',
            $tags,
            'tag',
            array(
                'list.select' => $selected,
                'list.attr'   => sprintf('class="%s"', $class)
            )
        );

        return $html;
    }

    /**
     * Difficulty Filter
     *
     * @return string
     * @throws Exception
     */
    protected function getFilterDifficulty()
    {
        $difficulty = JHtml::_('osc.options.difficulties');
        array_unshift(
            $difficulty,
            JHtml::_('select.option', '', JText::_('COM_OSCAMPUS_OPTION_SELECT_DIFFICULTY'))
        );

        $selected = $this->model->getState('filter.difficulty');
        $class    = $this->getStateClass($selected);

        $html = JHtml::_(
            'select.genericlist',
            $difficulty,
            'difficulty',
            array(
                'list.select' => $selected,
                'list.attr'   => sprintf('class="%s"', $class)
            )
        );

        return $html;
    }

    /**
     * User progress filter. Only available for logged in users
     *
     * @return string
     * @throws Exception
     */
    protected function getFilterProgress()
    {
        $progress = JHtml::_('osc.options.progress');
        array_unshift(
            $progress,
            JHtml::_('select.option', '', JText::_('COM_OSCAMPUS_OPTION_SELECT_COMPLETION'))
        );

        $selected = $this->model->getState('filter.progress');
        $class    = $this->getStateClass($selected);

        $html = JHtml::_(
            'select.genericlist',
            $progress,
            'progress',
            array(
                'list.select' => $selected,
                'list.attr'   => sprintf('class="%s"', $class)
            )
        );

        return $html;
    }

    /**
     * Teacher filter
     *
     * @return string
     * @throws Exception
     */
    protected function getFilterTeacher()
    {
        $teacherQuery = $this->db->getQuery(true)
            ->select(
                array(
                    'teacher.id AS ' . $this->db->quote('value'),
                    'user.name AS ' . $this->db->quote('text')
                )
            )
            ->from('#__users AS user')
            ->innerJoin('#__oscampus_teachers AS teacher ON teacher.users_id = user.id')
            ->innerJoin('#__oscampus_courses AS course ON course.teachers_id = teacher.id')
            ->innerJoin('#__oscampus_courses_pathways AS cp ON cp.courses_id = course.id')
            ->innerJoin('#__oscampus_pathways AS pathway ON pathway.id = cp.pathways_id')
            ->where(
                array(
                    'pathway.users_id = 0',
                    'pathway.published = 1',
                    $this->model->whereAccess('pathway.access'),
                    'course.published = 1',
                    $this->model->whereAccess('course.access'),
                    'course.publish_up <= UTC_TIMESTAMP()'
                )
            )
            ->group('teacher.id')
            ->order('user.name ASC');

        $teachers = $this->db->setQuery($teacherQuery)->loadObjectList();
        array_unshift(
            $teachers,
            JHtml::_('select.option', '', JText::_('COM_OSCAMPUS_OPTION_SELECT_TEACHER'))
        );

        $selected = $this->model->getState('filter.teacher');
        $class    = $this->getStateClass($selected);

        $html = JHtml::_(
            'select.genericlist',
            $teachers,
            'teacher',
            array(
                'list.select' => $selected,
                'list.attr'   => sprintf('class="%s"', $class)
            )
        );

        return $html;
    }

    /**
     * @param string $class
     *
     * @return string
     * @throws Exception
     */
    protected function getTypes($class = null)
    {
        $show = $this->model->getState('filter.types');

        $types = array(
            'P' => 'MOD_OSCAMPUS_SEARCH_TYPE_PATHWAY',
            'C' => 'MOD_OSCAMPUS_SEARCH_TYPE_COURSE',
            'L' => 'MOD_OSCAMPUS_SEARCH_TYPE_LESSON'
        );

        $html = array('<span class="osc-types-filter">');
        foreach ($types as $type => $label) {
            $attribs = array(
                'type'  => 'checkbox',
                'value' => $type
            );
            if ($class) {
                $attribs['class'] .= ' ' . $class;
            }
            if (strpos($show, $type) !== false) {
                $attribs['checked'] = 'checked';
            }

            $html[] = '<div><input ' . ArrayHelper::toString($attribs) . '/> ';
            $html[] = JText::_($label) . '</div>';
        }
        $html[] = sprintf('<input type="hidden" name="types" value="%s"/>', $show);
        $html[] = '</span>';

        return join("\n", $html);
    }

    /**
     * Add any scripts needed for module operation
     *
     * @return void
     */
    public function addScript()
    {
        if (!static::$javascriptLoaded) {
            JHtml::_('osc.jquery');

            $inputClasses = json_encode(
                array(
                    'active'   => $this->getStateClass(true),
                    'inactive' => $this->getStateClass(false)
                )
            );

            $js = <<< JSCRIPT
(function($) {
    $(document).ready(function() {
        var forms = $('form[name=oscampusFilter]');

        forms
            .find('.osc-types-filter')
            .find(':checkbox')
            .on('change', function(evt) {
                var target = $(this).parents('.osc-types-filter').find('input[name=types]')

                var selected = target.val().replace(this.value, '');
                if (this.checked) {
                    selected += this.value;
                }
                target.val(selected);
            });

        forms.find('select, input:checkbox').on('change', function(evt) {
            this.form.submit();
        });

        var inputClasses = {$inputClasses};
        forms.find('input:text')
            .on('keypress', function(evt) {
                if (evt.keyCode === 13) {
                 this.form.submit();
                }
            })
            .on('focusin', function(evt) {
                $(this).removeClass(inputClasses.inactive).addClass(inputClasses.active);
            })
            .on('focusout', function(evt) {
                if ($(this).val()) {
                    $(this).removeClass(inputClasses.inactive).addClass(inputClasses.active);
                } else {
                    $(this).removeClass(inputClasses.active).addClass(inputClasses.inactive);
                }
            });

        $('.osc-clear-filters').on('click', function(evt) {
            evt.preventDefault();
            $(this.form)
                .find(':input')
                .not(':button')
                .each(function (index, element) {
                    $(element).val(null);
                });

            this.form.submit();
        });
    });
})(jQuery);
JSCRIPT;

            OscampusFactory::getDocument()->addScriptDeclaration($js);

            static::$javascriptLoaded = true;
        }
    }

    /**
     * Get the classname for active vs inactive fields
     *
     * @param mixed $state
     *
     * @return string
     */
    protected function getStateClass($state)
    {
        return 'osc-formfield-' . ($state == '' ? 'inactive' : 'active');
    }

    public function output($layout = null)
    {
        $this->addScript();
        parent::output($layout);
    }
}

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

defined('_JEXEC') or die();

/**
 * Because the JHtml classes do not handle namespacing very well,
 * we must load it here or the JHtmlSelect class will not be available.
 */
$paths = array(
    JPATH_LIBRARIES . '/cms/html',
    JPATH_LIBRARIES . '/joomla/html/html'
);
foreach ($paths as $path) {
    if (is_file($path . '/select.php')) {
        require_once $path . '/select.php';
        break;
    }
}

abstract class OscSelect
{
    protected static $cache = array();

    protected static function joomla_version()
    {
        $version = explode('.', JVERSION);
        return (string)array_shift($version);
    }

    /**
     * Create a publishing dropdown selector
     *
     * @param string $name
     * @param mixed  $selected
     * @param string $blankOption
     * @param mixed  $attribs
     * @param string $id
     *
     * @return string
     */
    public static function published($name, $selected, $blankOption = null, $attribs = null, $id = null)
    {
        $options = array(
            JHtml::_('select.option', '0', JText::_('JUNPUBLISHED')),
            JHtml::_('select.option', '1', JText::_('JPUBLISHED'))
        );
        if ($blankOption) {
            array_unshift($options, JHtml::_('select.option', '', JText::_($blankOption)));
        }

        return JHtml::_('select.genericlist', $options, $name, $attribs, 'value', 'text', $selected, $id);
    }

    /**
     * @param string       $name
     * @param int          $selected
     * @param string|array $addOptions
     * @param array|string $attribs
     * @param string       $id
     *
     * @return string
     */
    public static function pathway($name, $selected, $addOptions = null, $attribs = null, $id = null)
    {
        $options = array_merge(static::createAddOptions($addOptions), JHtml::_('osc.options.pathways'));

        return JHtml::_('select.genericlist', $options, $name, $attribs, 'value', 'text', $selected, $id);
    }

    /**
     * @param string       $name
     * @param int          $selected
     * @param array|string $addOptions
     * @param array|string $attribs
     * @param string       $id
     *
     * @return string
     */
    public static function tag($name, $selected, $addOptions = null, $attribs = null, $id = null)
    {
        $options = array_merge(static::createAddOptions($addOptions), JHtml::_('osc.options.tags'));

        return JHtml::_('select.genericlist', $options, $name, $attribs, 'value', 'text', $selected, $id);

    }

    /**
     * @param string       $name
     * @param string       $selected
     * @param array|string $addOptions
     * @param array|string $attribs
     * @param string       $id
     *
     * @return string
     */
    public static function difficulty($name, $selected, $addOptions = null, $attribs = null, $id = null)
    {
        $options = array_merge(static::createAddOptions($addOptions), JHtml::_('osc.options.difficulties'));

        return JHtml::_('select.genericlist', $options, $name, $attribs, 'value', 'text', $selected, $id);
    }

    /**
     * @param string       $name
     * @param string       $selected
     * @param array|string $addOptions
     * @param array|string $attribs
     * @param string       $id
     *
     * @return string
     */
    public static function access($name, $selected, $addOptions = null, $attribs = null, $id = null)
    {
        $options = array_merge(static::createAddOptions($addOptions), JHtml::_('osc.options.access'));

        return JHtml::_('select.genericlist', $options, $name, $attribs, 'value', 'text', $selected, $id);
    }

    /**
     * @param string       $name
     * @param string       $selected
     * @param array|string $addOptions
     * @param array|string $attribs
     * @param string       $id
     *
     * @return string
     */
    public static function teacher($name, $selected, $addOptions = null, $attribs = null, $id = null)
    {
        $options = array_merge(static::createAddOptions($addOptions), JHtml::_('osc.options.teachers'));

        return JHtml::_('select.genericlist', $options, $name, $attribs, 'value', 'text', $selected, $id);
    }

    /**
     * @param string       $name
     * @param string       $selected
     * @param array|string $addOptions
     * @param array|string $attribs
     * @param string       $id
     *
     * @return string
     */
    public static function lessontype($name, $selected, $addOptions = null, $attribs = null, $id = null)
    {
        $options = array_merge(static::createAddOptions($addOptions), JHtml::_('osc.options.lessontypes'));

        return JHtml::_('select.genericlist', $options, $name, $attribs, 'value', 'text', $selected, $id);
    }

    /**
     * @param string       $name
     * @param string|int   $selected
     * @param array|string $addOptions
     * @param array|string $attribs
     * @param string       $id
     *
     * @return mixed
     */
    public static function pathwayowner($name, $selected, $addOptions = null, $attribs = null, $id = null)
    {
        if ($users = JHtml::_('osc.options.pathwayowners')) {
            $options = array_merge(static::createAddOptions($addOptions), $users);

        } else {
            $options = array(
                JHtml::_('select.option', null, JText::_('COM_OSCAMPUS_OPTION_NO_PATHWAY_OWNERS'), 'value', 'text',
                    true)
            );
        }

        return JHtml::_('select.genericlist', $options, $name, $attribs, 'value', 'text', $selected, $id);
    }

    /**
     * @param array|string $texts
     *
     * @return array
     */
    protected static function createAddOptions($texts)
    {
        $options = array();
        if ($texts) {
            foreach ((array)$texts as $value => $text) {
                $options[] = JHtml::_('select.option', $value, JText::_($text));
            }
        }

        return $options;
    }
}

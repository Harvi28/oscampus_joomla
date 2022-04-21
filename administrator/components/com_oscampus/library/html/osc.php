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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die();

abstract class JHtmlOsc
{
    /**
     * @var bool
     */
    protected static $utilitiesLoaded = false;

    /**
     * Load jQuery core
     *
     * @param bool $utilities
     * @param bool $noConflict
     * @param bool $debug
     *
     * @return void
     * @throws Exception
     */
    public static function jquery(bool $utilities = true, bool $noConflict = true, bool $debug = false)
    {
        $app    = OscampusFactory::getApplication();
        $params = OscampusFactory::getContainer()->params;

        $load   = $params->get('advanced.jquery', 1);
        $client = $app->getName();
        if ($load == $client || $load == 1) {
            $jqueryLoaded = $app->get('jquery', false);
            // Only load once
            if (!$jqueryLoaded) {
                HTMLHelper::_('jquery.framework', $noConflict, $debug);
            }
            $app->set('jquery', true);
        }

        if ($utilities && !static::$utilitiesLoaded) {
            HTMLHelper::_('script', 'com_oscampus/utilities.min.js', ['relative' => true]);
            static::$utilitiesLoaded = true;
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    public static function jui()
    {
        static::jquery();
        HTMLHelper::_('script', 'com_oscampus/jquery-ui.min.js', ['relative' => true]);
    }

    /**
     * Make a collection of elements sortable by dragging
     *
     * @param string $selector
     *
     * @return void
     * @throws Exception
     */
    public static function sortable(string $selector)
    {
        static::jquery();
        static::jui();

        $options = json_encode(['selector' => $selector]);
        static::onready("$.Oscampus.sortable({$options})");
    }

    /**
     * Setup tabbed areas
     *
     * @param string       $selector jQuery selector for tab headers
     * @param array|string $options  Associative array or JSON string of tabber options
     *
     * @return void
     * @throws Exception
     */
    public static function tabs(string $selector, $options = null)
    {
        static::jquery();

        if ($options && is_string($options)) {
            $options = json_decode($options, true);
        }
        if (!is_array($options)) {
            $options = [];
        }
        $options['selector'] = $selector;

        $options = json_encode($options);
        static::onready("$.Oscampus.tabs({$options});");
    }

    /**
     * Setup simple sliders
     *
     * @param string $selector
     * @param bool   $visible
     *
     * @return void
     * @throws Exception
     */
    public static function sliders(string $selector, ?bool $visible = false)
    {
        static::jquery();

        $options = json_encode([
            'selector' => $selector,
            'visible'  => (bool)$visible
        ]);

        static::onready("$.Oscampus.sliders({$options});");
    }

    /**
     * Add a script to run when dom ready. You can choose between echoing a
     * script tag into the output or passing that to the current document.
     *
     * @param string $js
     * @param bool   $echoScriptTag
     *
     * @return void
     */
    public static function onready(string $js, bool $echoScriptTag = false)
    {
        $js = ";jQuery(document).ready(function ($) { {$js} });";

        if ($echoScriptTag) {
            echo '<script>' . $js . '</script>';

        } else {
            OscampusFactory::getDocument()->addScriptDeclaration($js);
        }
    }

    /**
     * Format a duration into human text
     *
     * @param ?int $duration
     *
     * @return string
     */
    public static function duration(?int $duration): string
    {
        if ($duration) {
            $start = new DateTime();
            $end   = clone $start;
            $end->modify('+' . $duration . ' min');

            $interval = $end->diff($start);

            $format = [];
            if ($interval->d) {
                $format[] = Text::plural('COM_OSCAMPUS_COURSE_LENGTH_DAYS', $interval->d);
            }
            if ($interval->h) {
                $format[] = Text::plural('COM_OSCAMPUS_COURSE_LENGTH_HOURS', $interval->h);
            }
            if ($interval->i) {
                $format[] = Text::plural('COM_OSCAMPUS_COURSE_LENGTH_MINUTES', $interval->i);
            }

            return $interval->format(join(', ', $format));
        }

        return '';
    }

    /**
     * @return void
     * @throws Exception
     */
    public static function fontawesome()
    {
        $params = OscampusFactory::getContainer()->params;

        $load   = $params->get('themes.fontAwesome', 1);
        $client = OscampusFactory::getApplication()->getName();
        if ($load == $client || $load == 1) {
            HTMLHelper::_('stylesheet', 'com_oscampus/awesome/css/font-awesome.min.css', ['relative' => true]);
        }
    }

    /**
     * @param numeric $score
     *
     * @return string
     */
    public static function progressbar($score): string
    {
        $attribs = [
            'style' => sprintf('width: %s%%;', $score),
        ];

        $progress = Text::sprintf('COM_OSCAMPUS_SCORE', $score);
        if ($score < 30) {
            $attribs['class'] = 'hasTooltip';
            $attribs['title'] = HTMLHelper::_('tooltipText', '', $progress);
            $progress         = '';

        } elseif ($score == 100) {
            $attribs['class'] = 'osc-progress-bar-completed';
        }

        $attribs = ArrayHelper::toString($attribs);

        return <<<PROGRESS
<span class="osc-progress-bar">
    <span {$attribs}>
        <span>{$progress}</span>
    </span>
</span>
PROGRESS;
    }
}

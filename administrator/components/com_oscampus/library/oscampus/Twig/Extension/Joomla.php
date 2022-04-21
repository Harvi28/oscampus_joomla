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

namespace Oscampus\Twig\Extension;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use OscampusFactory;
use OscampusFilterInput;
use Throwable;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

defined('_JEXEC') or die();

class Joomla extends AbstractExtension
{
    /**
     * @var CMSApplication
     */
    protected $app = null;

    /**
     * @return void
     * @throws \Exception
     *
     */
    public function __construct()
    {
        $this->app = OscampusFactory::getApplication();
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'joomla';
    }

    /**
     * @inheritDoc
     */
    public function getGlobals()
    {
        return [
            'joomla_version' => JVERSION,
            'input'          => $this->app->input,
            'uri'            => Uri::getInstance()->toString()
        ];
    }

    /**
     * @inheritDoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('html', '\\Joomla\\CMS\\HTML\\HTMLHelper::_'),
            new TwigFunction('scriptText', [$this, 'functionScriptText']),
            new TwigFunction('linkto', [$this, 'functionLinkto']),
            new TwigFunction('layout', [$this, 'functionLayout']),
            new TwigFunction('value', [$this, 'functionValue']),
            new TwigFunction('print', [$this, 'functionPrint']),
        ];
    }

    /**
     * @param mixed $variable
     *
     * @return string
     */
    public function functionPrint($variable)
    {
        return print_r($variable, 1);
    }

    /**
     * Allows getting values from magic/virtual properties
     *
     * @param object $object
     * @param string $property
     *
     * @return mixed
     */
    public function functionValue(object $object, string $property)
    {
        try {
            return $object->{$property};

        } catch (Throwable $e) {
            return '{!! ' . get_class($e) . ': ' . $e->getMessage() . ' !!}';
        }
    }

    /**
     * Link to an oscampus page by specifying urlvars
     *
     * @param array $urlVars
     *
     * @return string
     */
    public function functionLinkto(array $urlVars): string
    {
        if (!isset($urlVars['option'])) {
            $urlVars = array_merge(
                ['option' => 'com_oscampus'],
                $urlVars
            );
        }

        return Route::_('index.php?' . http_build_query($urlVars));
    }

    /**
     * Wrapper for use of layouts in Twig
     *
     * @param string  $layoutFile
     * @param ?mixed  $displayData
     * @param ?string $basePath
     * @param ?mixed  $options
     *
     * @return string
     */
    public function functionLayout(
        string $layoutFile,
        $displayData = null,
        ?string $basePath = '',
        $options = null
    ): string {
        return LayoutHelper::render($layoutFile, $displayData, $basePath, $options);
    }

    /**
     * @inheritDoc
     */
    public function getFilters()
    {
        return [
            new TwigFilter('route', '\\Joomla\\CMS\\Router\\Route::_'),
            new TwigFilter('lang', '\\Joomla\\CMS\\Language\\Text::_'),
            new TwigFilter('sprintf', '\\Joomla\\CMS\\Language\\Text::sprintf'),
            new TwigFilter('clean', [$this, 'filterFilter'])
        ];
    }

    /**
     * Wrapper to string input filter
     *
     * @param string $string
     * @param string $command
     *
     * @return string
     */
    public function filterFilter(string $string, string $command): string
    {
        $filter = OscampusFilterInput::getInstance();

        return $filter->clean($string, $command);
    }

    /**
     * @param string $string
     *
     * @return void
     */
    public function functionScriptText(string $string)
    {
        Text::script($string);
    }
}

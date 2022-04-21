<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2018-2021 Joomlashack.com. All rights reserved
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

namespace Oscampus;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;
use OscampusFactory;

defined('_JEXEC') or die();

class Theme
{
    /**
     * @var array[] - recognized fonts
     */
    public $fontFamilies = [
        'arial'           => [
            'font'  => 'Arial, Helvetica',
            'serif' => 'sans-serif'
        ],
        'droid_sans'      => [
            'font'   => 'Droid Sans',
            'serif'  => 'sans-serif',
            'weight' => '400,700'
        ],
        'lato'            => [
            'font'   => 'Lato',
            'serif'  => 'sans-serif',
            'weight' => '400,400italic,700'
        ],
        'old_standard'    => [
            'font'   => 'Old Standard TT',
            'serif'  => 'serif',
            'weight' => '400,700,400italic'
        ],
        'open_sans'       => [
            'font'   => 'Open Sans',
            'serif'  => 'sans-serif',
            'weight' => '400,400italic,600,700'
        ],
        'times_new_roman' => [
            'font'  => 'Times New Roman',
            'serif' => 'serif'
        ],
        'ubuntu'          => [
            'font'   => 'Ubuntu',
            'serif'  => 'sans-serif',
            'weight' => '400,400italic,700,500'
        ]
    ];

    /**
     * @var string[] - css selectors for applying font styles globally
     */
    protected $tags = [
        '.osc-container p,',
        '.osc-container h1,',
        '.osc-container h2,',
        '.osc-container h3,',
        '.osc-container div,',
        '.osc-container li,',
        '.osc-container span,',
        '.osc-container label,',
        '.osc-container td,',
        '.osc-container input,',
        '.osc-container button,',
        '.osc-container textarea,',
        '.osc-container select',
    ];

    /**
     * @var string
     */
    protected $googleFonts = 'https://fonts.googleapis.com';

    /**
     * @var Registry
     */
    protected $params = null;

    /**
     * @var bool
     */
    protected static $cssLoaded = false;

    /**
     * Theme constructor.
     *
     * @param Registry $params
     */
    public function __construct(Registry $params)
    {
        $this->params = $params;
    }

    public function __get($property)
    {
        return $this->get($property);
    }

    public function get($property, $default = null)
    {
        switch ($property) {
            case 'name':
                return $this->params->get('themes.theme');

            default:
                if (property_exists($this, $property)) {
                    return $this->{$property};
                }
        }

        return $this->params->get('themes.' . $property);
    }

    /**
     * Load all font/styling items
     * - Awesome Icon font
     * - Google Font
     * - Selected Theme css
     *
     * @param null $theme
     */
    public function loadCss($theme = null)
    {
        if (!static::$cssLoaded) {
            static::$cssLoaded = true;

            $fontName = $this->params->get('themes.fontFamily');

            if (!empty($this->fontFamilies[$fontName])) {
                $font = $this->fontFamilies[$fontName];

                // Load Google fonts files when font-weight exists
                if (!empty($font['weight'])) {
                    $href = sprintf('%s/css?family=%s:%s', $this->googleFonts, $font['font'], $font['weight']);
                    HTMLHelper::_('stylesheet', $href);
                }

                // Assign font-family to selected tags
                $style = sprintf(
                    "\n%s {\nfont-family: %s, %s\n}\n",
                    join("\n", $this->tags),
                    $font['font'],
                    $font['serif']
                );
                OscampusFactory::getDocument()->addStyleDeclaration($style);
            }

            HTMLHelper::_('osc.fontawesome');

            // Load responsive grids
            HTMLHelper::_('stylesheet', 'com_oscampus/grid.min.css', ['relative' => true]);
            HTMLHelper::_('stylesheet', 'com_oscampus/grid-responsive.min.css', ['relative' => true]);
            HTMLHelper::_('stylesheet', 'com_oscampus/style.min.css', ['relative' => true]);

            // Load the selected theme's stylesheet
            $theme = $theme ?: $this->get('name') ?: 'default';

            $linkTemplate = 'com_oscampus/themes/%s.css';
            $themeCheck   = HTMLHelper::_(
                'stylesheet',
                sprintf($linkTemplate, $theme),
                ['relative' => true, 'pathOnly' => true]
            );

            if (!$themeCheck) {
                /*
                 * Probably a template-specific theme with a different active template
                 * This should cleanly fallback to the default theme.
                 */
                $theme = 'default';
            }
            HTMLHelper::_('stylesheet', sprintf($linkTemplate, $theme), ['relative' => true]);
        }
    }
}

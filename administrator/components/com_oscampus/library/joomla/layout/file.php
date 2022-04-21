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

defined('_JEXEC') or die();

/**
 * Class OscampusLayoutFile
 *
 * @method string[] getPath
 */
class OscampusLayoutFile extends JLayoutFile
{
    /**
     * @var string[]
     */
    protected $legacyLayouts = array(
        'course'     => 'listing.course',
        'lesson'     => 'listing.lesson',
        'pagination' => 'listing.pagination',
        'pathway'    => 'listing.pathway'
    );

    /**
     * No one is unhappier than me about this. Due to the way Joomla
     * builds the include path list, this is the simplest way to insert
     * our theme override paths when needed.
     *
     * @return array
     * @throws Exception
     */
    public function getDefaultIncludePaths()
    {
        $paths = parent::getDefaultIncludePaths();

        if ($this->options->get('client') == 0) {
            $theme = OscampusFactory::getContainer()->theme;

            $pathsAmended = array();
            $component    = $this->options->get('component');
            foreach ($paths as $idx => $path) {
                if (strpos($path, $component) > 0) {
                    if (stripos($path, OSCAMPUS_SITE) === 0) {
                        $addedPath = OSCAMPUS_SITE;

                    } else {
                        $addedPath = str_replace('/layouts/', '/', $path);
                    }
                    $pathsAmended[] = $addedPath . "/themes/{$theme->name}/layouts";
                }

                $pathsAmended[] = $path;
            }

            $paths = $pathsAmended;
        }

        return $paths;
    }

    /**
     * We made an early mistake in the location of layout files that made them
     * invisible to the template manager. This should help cover any template
     * overrides that are using the legacy layout ids
     *
     * @param array $displayData
     *
     * @return string
     */
    public function render($displayData = array())
    {
        if (!$this->getPath() && !empty($this->legacyLayouts[$this->layoutId])) {
            $this->setLayoutId($this->legacyLayouts[$this->layoutId]);
        }

        return parent::render($displayData);
    }
}

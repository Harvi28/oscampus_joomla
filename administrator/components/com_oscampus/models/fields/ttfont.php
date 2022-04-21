<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2019 Joomlashack.com. All rights reserved
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
use Oscampus\Font\Manager;

defined('_JEXEC') or die();

if (!defined('OSCAMPUS_LOADED') || !OSCAMPUS_LOADED) {
    $path = JPATH_ADMINISTRATOR . '/components/com_oscampus/include.php';
    if (is_file($path)) {
        require_once $path;
    }
}

JFormHelper::loadFieldClass('List');

class OscampusFormFieldTtfont extends JFormFieldList
{
    /**
     * @var Manager
     */
    protected $fontManager = null;

    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        $this->fontManager = OscampusFactory::getContainer()->fonts;

        if ($default = (string)$element['default']) {
            $fontNames = explode(':', $default);
            $family    = array_shift($fontNames);
            $subfamily = array_shift($fontNames);

            if ($font = $this->fontManager->getFont($family, $subfamily)) {
                $this->default = $font->hash;
                if ($value == $default) {
                    $value = $font->hash;
                }
            }
        }

        return parent::setup($element, $value, $group);
    }

    protected function getOptions()
    {
        $fonts = OscampusFactory::getContainer()->fonts->getFonts();

        $options = [];
        foreach ($fonts as $font) {
            $options[] = HTMLHelper::_(
                'select.option',
                $font->hash,
                sprintf('%s (%s)', $font->family, $font->subfamily)
            );
        }

        return array_merge(parent::getOptions(), $options);
    }
}

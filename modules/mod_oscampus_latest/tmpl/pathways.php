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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Oscampus\Module\Latest;

defined('_JEXEC') or die();

/**
 * @var Latest $this
 */
$pathways = $this->getPathways();

if ($pathways) : ?>
    <p><?php echo Text::plural('MOD_OSCAMPUS_LATEST_PATHWAYS_FOUND', count($pathways)); ?></p>
    <ul>
        <?php
        foreach ($pathways as $pathway) {
            echo sprintf(
                '<li>%s</li>',
                Text::plural(
                    'MOD_OSCAMPUS_LATEST_PATHWAYS_COURSES',
                    count($pathway->courses),
                    HTMLHelper::_('osc.pathway.link', $pathway)
                )
            );
        }
        ?>
    </ul>
<?php endif;

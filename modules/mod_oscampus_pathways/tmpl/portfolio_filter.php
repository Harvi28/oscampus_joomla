<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2019-2021 Joomlashack.com. All rights reserved
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

use Oscampus\Module\Pathways;

defined('_JEXEC') or die();

/**
 * @var Pathways $this
 * @var string   $layout
 */

JHtml::_('jquery.framework');

// Isotope jQuery plugin
JHtml::_('script', 'mod_oscampus_pathways/isotope.pkgd.min.js', array('relative' => true));
JHtml::_('script', 'mod_oscampus_pathways/portfolio.min.js', array('relative' => true));

$pathways = $this->getItems();

?>
<div id="osc-portfolio-filters" class="osc-portfolio-filters--<?php echo $this->params->get('portfolio_filter', 'top'); ?>">
    <?php
    if (count($pathways) > 1) :
        ?>
        <button class="osc-btn osc-btn-active" data-filter="*">
            <?php echo JText::_('MOD_OSCAMPUS_PATHWAYS_PORTFOLIO_ALL'); ?>
        </button>
        <?php
    endif;
    foreach ($pathways as $pathway) :
        ?>
        <button class="osc-btn" data-filter="<?php echo '.pathway-' . $pathway->id; ?>">
            <?php echo $pathway->title; ?>
        </button>
        <?php
    endforeach;
    ?>
</div>

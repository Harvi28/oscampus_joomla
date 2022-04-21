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

use Oscampus\Module\Pathways;

defined('_JEXEC') or die();

/**
 * @var Pathways $this
 * @var string   $layout
 */

$pathways = $this->getItems();

$OscColumns    = $this->params->get('portfolio_columns', 3);
$OscColumnSize = round(12 / $OscColumns);

$OscStyle = sprintf(
    '.oscampus_portfolio_items > .block%s:nth-child(%sn+1) {margin-left:0;}',
    $OscColumnSize,
    $OscColumns
);

JFactory::getDocument()->addStyleDeclaration($OscStyle);

$filterPosition = $this->params->get('portfolio_filter', 'top');

?>
<div class="osc-module-container osc-module-pathways-portfolio">
    <?php
    if ($filterPosition == 'top') :
        echo $this->loadTemplate('filter');
    endif;
    ?>
    <div class="osc-container">
        <div class="oscampus_portfolio_items osc-section">
            <?php
            foreach ($pathways as $pathway) :
                if ($pathway->courses) :
                    foreach ($pathway->courses as $course) :
                        $OscClasses = 'block' . $OscColumnSize . ' pathway-' . $pathway->id;
                        ?>
                        <div class="osc-portfolio-element-item <?php echo $OscClasses; ?>">
                            <?php
                            $item  = $course;
                            $link  = JRoute::_(JHtml::_('osc.course.link', $item, null, null, true));
                            $image = JHtml::_('image', $item->image, $item->title);
                            echo JHtml::_('link', $link, $image);
                            ?>
                            <h3>
                                <?php echo JHtml::_('osc.course.link', $course); ?>
                            </h3>
                        </div>
                    <?php
                    endforeach;
                endif;
            endforeach;
            ?>
        </div>
    </div>
    <?php
    if ($filterPosition == 'bottom') :
        echo $this->loadTemplate('filter');
    endif;
    ?>
</div>


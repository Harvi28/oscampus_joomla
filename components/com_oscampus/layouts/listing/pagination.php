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

use Joomla\Registry\Registry;

defined('_JEXEC') or die();

/**
 * @var JLayoutFile $this
 * @var array       $displayData
 * @var string      $layoutOutput
 * @var string      $path
 */

$options = empty($displayData['options']) ? null : $displayData['options'];
$options = new Registry($options);
$options->loadObject($this->getOptions()->toObject());

if (!empty($displayData['list'])) {
    $list = $displayData['list'];
    if (!empty($list['pages'])) {
        $pages = $list['pages'];
    }

    static $limitBoxDisplayed = false;
    if (!empty($list['limitfield']) && !$limitBoxDisplayed && $options->get('showLimitBox')) {
        $limitField = $list['limitfield'];
    }
}

$displayLink = function (JPaginationObject $page, $title = null) {
    $text = $title ?: $page->text;
    if ($page->active) {
        return '<li class="osc-page-number"><span>' . $text . '</span></li>';
    }

    return '<li class="osc-page-number">' . JHtml::_('link', $page->link, $text) . '</li>';
};

if (!empty($pages) || !empty($limitField)) :
    ?>
    <div class="osc-pagination">
        <?php
        if (!empty($pages)) :
            ?>
            <ul class="osc-pagination-list">
                <?php
                if ($pages['start']['active']) :
                    echo $displayLink($pages['start']['data'], '<i class="fa fa-angle-double-left"></i>');
                endif;

                if ($pages['previous']['active']) :
                    echo $displayLink($pages['previous']['data'], '<i class="fa fa-chevron-left"></i>');
                endif;

                foreach ($pages['pages'] as $page) :
                    echo $displayLink($page['data']);
                endforeach;

                if ($pages['next']['active']) :
                    echo $displayLink($pages['next']['data'], '<i class="fa fa-chevron-right"></i>');
                endif;

                if ($pages['end']['active']) :
                    echo $displayLink($pages['end']['data'], '<i class="fa fa-angle-double-right"></i>');
                endif;
                ?>
            </ul>
        <?php
        endif;

        if (!empty($limitField)) :
            $limitBoxDisplayed = true;
            ?>
            <div class="osc-float-right-desktop"><?php echo $limitField; ?></div>
        <?php
        endif;
        ?>
    </div>
<?php
endif;

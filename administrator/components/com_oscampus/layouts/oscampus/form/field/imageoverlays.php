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

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Layout\LayoutHelper;

defined('_JEXEC') or die();

/**
 * @var FileLayout $this
 * @var array      $displayData
 * @var string     $layoutOutput
 * @var string     $path
 */

/**
 * @var string      $autocomplete
 * @var boolean     $autofocus
 * @var string      $class
 * @var string      $description
 * @var boolean     $disabled
 * @var FormField   $field
 * @var string      $group
 * @var boolean     $hidden
 * @var string      $hint
 * @var string      $id
 * @var string      $label
 * @var string      $labelclass
 * @var boolean     $multiple
 * @var string      $name
 * @var string      $onchange
 * @var string      $onclick
 * @var string      $pattern
 * @var string      $validationtext
 * @var boolean     $readonly
 * @var boolean     $repeat
 * @var boolean     $required
 * @var integer     $size
 * @var boolean     $spellcheck
 * @var string      $validate
 * @var string      $value
 * @var FormField[] $movable
 * @var FormField   $imageField
 * @var string      $defaultImage
 */
extract($displayData);

if ($imageField->value) {
    $image = HTMLHelper::_('image', $imageField->value, null);
} else {
    $image = sprintf('<img src="%s">', $defaultImage);
}

HTMLHelper::_(
    'osc.certificate.designer',
    [
        'imageField'   => '#' . $imageField->id,
        'defaultImage' => $defaultImage
    ]
);
?>
<div class="row-fluid osc-image-overlays">
    <div class="span6 osc-certificate-design-area">
        <div id="certificate-image">
            <?php echo $image; ?>
        </div>
    </div>
    <div class="span6 osc-certificate-fields-area">
        <div id="positions">
            <?php
            foreach ($movable as $id => $field) :
                switch ($field->group) :
                    case 'movable':
                        echo $field->input;
                        break;

                    case 'movable.overlays':
                        $fieldName = $field->fieldname;
                        $fieldBody = $field->input;
                        $textFieldId = str_replace('overlays', 'text', $id);
                        if (isset($movable[$textFieldId])) :
                            $fieldBody .= $movable[$textFieldId]->input;
                        else :
                            $fieldBody .= Text::_('COM_OSCAMPUS_CERTIFICATE_' . $fieldName);
                        endif;

                        ?>
                        <div class="data-box" data-item="<?php echo $fieldName; ?>">
                            <span class="btn osc-certificate-drag-button">
                               <i class="icon-move"></i>
                            </span>
                            <?php echo $fieldBody; ?>
                        </div>
                        <?php

                        break;
                endswitch;
            endforeach; ?>
        </div>
        <?php echo LayoutHelper::render('oscampus.certificate.preview', $displayData); ?>
    </div>
</div>

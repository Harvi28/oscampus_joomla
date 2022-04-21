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
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die();

/**
 * @var FileLayout $this
 * @var array      $displayData
 * @var string     $layoutOutput
 * @var string     $path
 *
 * @var FormField  $field
 */

$id    = 'ModalPreview' . (isset($displayData['id']) ? $displayData['id'] : null);
$field = isset($displayData['field']) ? $displayData['field'] : null;

HTMLHelper::_('jquery.framework');
HTMLHelper::_('script', 'system/modal-fields.js', ['version' => 'auto', 'relative' => true]);

echo HTMLHelper::_(
    'bootstrap.renderModal',
    $id,
    [
        'title'      => Text::_('COM_OSCAMPUS_CERTIFICATE_PREVIEW_TITLE'),
        'modalWidth' => 40,
        'footer'     => sprintf(
            '<button type="button" class="btn" data-dismiss="modal">%s</button>',
            Text::_('JLIB_HTML_BEHAVIOR_CLOSE')
        )
    ]
);

$btnAttribs = [
    'id'          => $id . '_previewButton',
    'type'        => 'button',
    'class'       => 'btn btn-info osc-certificate-preview',
    'data-toggle' => 'modal',
    'data-target' => '#' . $id
];

$jScript = <<<JSCRIPT
jQuery(document).ready(function($) {
    let \$modal     = $('#{$id}'),
        \$modalBody = \$modal.find('.modal-body');

    \$modal
        .on('show.bs.modal', function() {
            let \$form  = $('#adminForm'),
                \$task  = \$form.find('input[name=task]'),
                \$html  = $('<div style="padding: 5px;"/>'),
                \$image = $('<img>');

            \$task.val('certificate.preview');

            let imageSrc = \$form.attr('action') + '&format=raw&' + \$form.serialize();

            \$image.attr('src', imageSrc);
            \$html.html(\$image);

            \$modalBody.html(\$html);
        });
});
JSCRIPT;

OscampusFactory::getDocument()->addScriptDeclaration($jScript);

?>
<div>
    <button <?php echo ArrayHelper::toString($btnAttribs); ?>>
        <i class="icon-eye"></i> <?php echo Text::_('COM_OSCAMPUS_CERTIFICATE_PREVIEW'); ?>
    </button>
</div>

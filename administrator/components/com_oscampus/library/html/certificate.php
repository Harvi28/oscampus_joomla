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

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die();

abstract class OscCertificate
{
    /**
     * @param int    $certificate
     * @param string $dateEarned
     *
     * @return string
     */
    public static function icon($certificate, $dateEarned = null)
    {
        if (is_object($certificate)) {
            $id         = empty($certificate->id) ? null : $certificate->id;
            $dateEarned = empty($certificate->date_earned) ? null : $certificate->date_earned;
        } else {
            $id = $certificate;
        }

        if ($id && $dateEarned) {
            $text = HTMLHelper::_(
                'osc.link.certificate',
                $id,
                '<i class="fa fa-download hasTip hasTooltip" title="%s"></i>'
            );

        } else {
            $text = '<i class="fa osc-bg-yellow fa-fw hasTip hasTooltip" title="%s"></i>';
        }

        return sprintf(
            $text,
            HTMLHelper::_('tooltipText', JText::_('COM_OSCAMPUS_CERTIFICATE_EARNED'), $dateEarned, false)
        );
    }

    /**
     * Load the overlay editing features for certificate design
     *
     * @param array $options
     *
     * @return void
     */
    public static function designer(array $options = [])
    {
        HTMLHelper::_('osc.jui');
        HTMLHelper::_('script', 'com_oscampus/admin/overlays.min.js', ['relative' => true]);

        $defaults = [
            'baseUri'     => trim(JUri::root(), '/') . '/',
            'closeButton' => '<span class="btn osc-certificate-remove-button"><i class="icon-unpublish"></i></span>'
        ];

        $options = json_encode(array_replace_recursive($defaults, $options));

        HTMLHelper::_('osc.onready', "$.Oscampus.admin.overlays.init({$options});");

        HTMLHelper::_('stylesheet', 'com_oscampus/jquery-ui.min.css', ['relative' => true]);
        HTMLHelper::_('stylesheet', 'com_oscampus/admin.css', ['relative' => true]);
    }
}

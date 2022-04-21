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

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();

FormHelper::loadFieldClass('Url');

class OscampusFormFieldEmbed extends JFormFieldUrl
{
    protected static $javascriptLoaded = false;

    /**
     * @inheritDoc
     */
    protected function getInput()
    {
        $this->addJavascript();

        return '<div class="input-append">'
            . parent::getInput()
            . $this->getPreview()
            . '</div>';
    }

    /**
     * @return string
     */
    protected function getPreview(): string
    {
        $button = sprintf(
            '<a id="%s_btn" class="%s" title="%s" data-target="#%s"><span class="%s"></span></a>',
            $this->id,
            'btn btn-primary btn-select',
            Text::_('COM_OSCAMPUS_EMBED_PREVIEW_BUTTON_TEXT'),
            $this->id,
            'icon-eye-open'
        );

        $previewPane = sprintf(
            '<div id="%s_preview" style="%s"></div>',
            $this->id,
            'clear: both; margin-top: 5px; font-size: 13px;'
        );

        return $button . $previewPane;
    }

    /**
     * Add all js needed for drag/drop ordering
     *
     * @rfeturn void
     */
    protected function addJavascript()
    {
        $classes     = preg_split('/\s+/', $this->class);
        $classes[]   = 'osc-url-embed-field';
        $this->class = join(' ', array_unique($classes));

        if (!static::$javascriptLoaded) {
            JHtml::_('osc.jquery');
            JHtml::_('script', 'com_oscampus/admin/embed.min.js', ['relative' => true]);

            $options = json_encode([
                'urlbase' => JUri::root(true) ?: '/',
            ]);

            JHtml::_('osc.onready', "$.Oscampus.admin.embed.init({$options});");

            static::$javascriptLoaded = true;
        }
    }
}

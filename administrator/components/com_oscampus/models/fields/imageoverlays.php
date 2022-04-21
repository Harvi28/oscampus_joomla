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

defined('_JEXEC') or die();

class OscampusFormFieldImageoverlays extends FormField
{
    protected $layout = 'oscampus.form.field.imageoverlays';

    /**
     * @var FormField
     */
    protected $imageField = null;

    /**
     * @inheritDoc
     */
    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        if (parent::setup($element, $value, $group)) {
            $imageFieldName   = (string)$this->element['imageField'] ?: 'image';
            $this->imageField = $this->form->getField($imageFieldName);

            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    protected function getInput()
    {
        if ($this->imageField instanceof FormField) {
            return parent::getInput();
        }

        return sprintf(
            '<span class="alert alert-warning">%s</span>',
            Text::_('COM_OSCAMPUS_ERROR_IMAGEOVERLAYS_IMAGE')
        );
    }

    /**
     * @inheritDoc
     */
    protected function getLabel()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    protected function getLayoutData()
    {
        $layoutData = parent::getLayoutData();
        $params     = OscampusComponentHelper::getParams();

        if ($defaultImage = $params->get('certificates.image')) {
            $defaultImage = HTMLHelper::image($defaultImage, null, null, false, 1);

        } else {
            $defaultImage = HTMLHelper::_('image', 'com_oscampus/default-certificate.jpg', null, null, true, 1);
        }

        $layoutData = array_replace_recursive(
            $layoutData,
            [
                'movable'      => $this->form->getFieldset('movable'),
                'imageField'   => $this->imageField,
                'defaultImage' => $defaultImage
            ]
        );

        return $layoutData;
    }
}

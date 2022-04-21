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

defined('_JEXEC') or die();

/**
 * @property int    $id
 * @property string $title
 * @property int    $default
 * @property string $image
 * @property string $font
 * @property int    $fontsize
 * @property string $fontcolor
 * @property string $dateformat
 * @property string $movable
 * @property int    $published
 * @property string $created
 * @property int    $created_by
 * @property string $created_by_alias
 * @property string $modified
 * @property int    $modified_by
 * @property int    $checked_out
 * @property string $checked_out_time
 */
class OscampusTableCertificates extends OscampusTable
{
    /**
     * @inheritdoc
     */
    protected $_jsonEncode = ['movable'];

    /**
     * @inheritDoc
     */
    public function __construct(&$db)
    {
        parent::__construct('#__oscampus_certificates', 'id', $db);
    }

    /**
     * @inheritDoc
     */
    public function check()
    {
        $this->fontcolor = str_replace('#', '', $this->fontcolor);

        return parent::check();
    }
}

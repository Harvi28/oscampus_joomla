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

defined('_JEXEC') or die();

$includePath = realpath(__DIR__ . '/../../../include.php');
if (!is_file($includePath)) {
    throw new Exception('Unable to load module in ' . __FILE__);
}
require_once $includePath;

$fieldPath = OSCAMPUS_ADMIN . '/models/fields/pathways.php';
if (!is_file($fieldPath)) {
    throw new Exception(JText::sprintf('MOD_OSCAMPUS_PATHWAYS_ERROR_FIELD_MISSING', basename($fieldPath)), 500);
}
require_once $fieldPath;

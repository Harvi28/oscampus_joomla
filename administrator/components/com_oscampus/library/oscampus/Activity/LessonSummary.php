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

namespace Oscampus\Activity;

use DateTime;
use Oscampus\AbstractPrototype;

defined('_JEXEC') or die();

class LessonSummary extends AbstractPrototype
{
    /**
     * @var int
     */
    public $id = null;

    /**
     * @var int
     */
    public $users_id = null;

    /**
     * @var int
     */
    public $certificates_id = null;

    /**
     * @var int
     */
    public $awarded_id = null;

    /**
     * @var int
     */
    public $lessons = null;

    /**
     * @var int
     */
    public $viewed = null;

    /**
     * @var int
     */
    public $visits = null;

    /**
     * @var DateTime
     */
    public $completed = null;

    /**
     * @var DateTime
     */
    public $certificate = null;

    /**
     * @var DateTime
     */
    public $first_visit = null;

    /**
     * @var DateTime
     */
    public $last_visit = null;

    protected $dateProperties = array(
        'completed',
        'certificate',
        'first_visit',
        'last_visit'
    );
}
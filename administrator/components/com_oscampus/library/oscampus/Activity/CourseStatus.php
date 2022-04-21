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

/**
 * Class CourseStatus
 *
 * @package Oscampus\Activity
 *
 * @property-read float $progress
 * @property-read int   $courses_id
 */
class CourseStatus extends AbstractPrototype
{
    // Progress statuses (stati?)
    public const NOT_STARTED = 0;
    public const IN_PROGRESS = 1;
    public const COMPLETED   = 2;

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
     * @var string
     */
    public $title = null;

    /**
     * @var int
     */
    public $lesson_count = null;

    /**
     * @var int
     */
    public $lessons_viewed = null;

    /**
     * @var int
     */
    public $lessons_completed = null;

    /**
     * @var DateTime
     */
    public $first_visit = null;

    /**
     * @var DateTime
     */
    public $last_visit = null;

    /**
     * @var int
     */
    public $last_lesson = null;

    /**
     * @var string
     */
    public $scores = null;

    /**
     * @var DateTime
     */
    public $date_earned = null;

    protected $dateProperties = [
        'first_visit',
        'last_visit',
        'date_earned'
    ];

    public function __get($name)
    {
        switch ($name) {
            case 'courses_id':
                return $this->id;

            case 'progress':
                if ($this->lesson_count > 0) {
                    return round(($this->lessons_completed / $this->lesson_count) * 100, 0);
                }

                return 0;
        }

        return null;
    }
}

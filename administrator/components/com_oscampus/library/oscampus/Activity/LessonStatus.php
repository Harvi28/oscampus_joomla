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
use Oscampus\Lesson\Type\AbstractType;

defined('_JEXEC') or die();

class LessonStatus extends AbstractPrototype
{
    public const VIEWED      = 'viewed';
    public const IN_PROGRESS = 'in-progress';
    public const FAILED      = 'failed';
    public const PASSED      = 'passed';

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
    public $lessons_id = null;

    /**
     * @var int
     */
    public $courses_id = null;

    /**
     * @var string
     */
    public $type = null;

    /**
     * @var DateTime
     */
    public $completed = null;

    /**
     * @var int
     */
    public $score = 0;

    /**
     * @var int
     */
    public $visits = 0;

    /**
     * @var mixed
     */
    public $data = null;

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
        'last_visit',
        'first_visit'
    );

    /**
     * @return string
     */
    public function getId()
    {
        if ($renderer = AbstractType::getClassName($this->type)) {
            return $renderer::getStatusId($this);
        }

        return AbstractType::getStatusId($this);
    }
}

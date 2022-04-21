<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2015-2021 Joomlashack.com. All rights reserved
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

namespace Oscampus\Lesson\Type;

use JHtml;
use Joomla\Registry\Registry as Registry;
use Oscampus\Activity\LessonStatus;
use OscampusFactory;

defined('_JEXEC') or die();

class Text extends AbstractType
{
    /**
     * @inheritdoc
     */
    protected static $icon = 'fa-align-left';

    /**
     * @inheritdoc
     */
    protected $overlayImage = 'com_oscampus/lesson-overlay-text.png';

    /**
     * @inheritDoc
     */
    public function render()
    {
        if ($this->lesson->isAuthorised()) {
            return JHtml::_('content.prepare', $this->lesson->content);
        }

        return $this->getThumbnail();
    }

    /**
     * @inheritDoc
     */
    protected function prepareAdminData(Registry $data)
    {
        // This lesson type produces text-only content
    }

    /**
     * @inheritDoc
     */
    public function prepareActivityProgress(LessonStatus $status, $score = null, $data = null)
    {
        $status->score = 100;
        if (!$status->completed) {
            $status->completed = OscampusFactory::getDate();
        }
    }
}

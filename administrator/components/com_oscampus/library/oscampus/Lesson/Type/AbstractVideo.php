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

namespace Oscampus\Lesson\Type;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Oscampus\Activity\LessonStatus;
use OscampusFactory;

defined('_JEXEC') or die();

abstract class AbstractVideo extends AbstractDownloadable
{
    protected $overlayImage = 'com_oscampus/lesson-overlay-video.png';

    /**
     * @inheritDoc
     */
    protected function init()
    {
        parent::init();

        HTMLHelper::_('osc.jquery');

        Text::script('COM_OSCAMPUS_VIDEO_RESUME');
        HTMLHelper::_('script', 'com_oscampus/video.min.js', ['relative' => true]);

        $this->downloadEnabled = $this->params->get('videos.download.enabled', false);

        $this->download->set([
            'limit'       => (int)$this->params->get('videos.download.limit', 20),
            'period'      => (int)$this->params->get('videos.download.period', 7),
            'signupLink'  => $this->params->get('videos.download.new'),
            'upgradeLink' => $this->params->get('videos.download.upgrade')
        ]);
    }

    /**
     * @param ?array $addOptions
     *
     * @return array
     */
    protected function getOptions(?array $addOptions = []): array
    {
        return array_merge(
            [
                'formToken' => Session::getFormToken(),
                'accuracy'  => $this->params->get('videos.accuracy', 1),
                'lessonId'  => $this->lesson->current->id
            ],
            (array)$addOptions
        );
    }

    /**
     * @inheritDoc
     */
    public static function getStatusId(LessonStatus $status = null)
    {
        if ($status && $status->completed) {
            return parent::getStatusId();
        }

        return LessonStatus::IN_PROGRESS;
    }

    /**
     * @inheritDoc
     */
    public function isDownloadAuthorised()
    {
        if ($this->downloadAuthorised === null) {
            $this->downloadAuthorised = $this->downloadEnabled
                && parent::isDownloadAuthorised()
                && OscampusFactory::getUser()->authorise('video.download', 'com_oscampus');
        }

        return $this->downloadAuthorised;
    }

    /**
     * @inheritDoc
     */
    public function prepareActivityProgress(LessonStatus $status, $score = null, $duration = null)
    {
        if ($this->params->get('videos.accuracy', 1) == 0) {
            $status->score = 100;
            if (!$status->completed) {
                $status->completed = OscampusFactory::getDate();
            }

        } elseif ($duration > 0) {
            $data = $this->getVideoActivityData($status->data, $duration);

            $status->score = $this->getVideoPercentWatched($data->log, $score);

            if (!$status->completed) {
                $completion = max((int)$this->params->get('videos.completion'), 0);

                if ($status->score >= $completion) {
                    $status->completed = OscampusFactory::getDate();
                }
            }

            $status->data = json_encode($data);
        }
    }

    /**
     * @param object|string $data
     * @param float         $duration
     *
     * @return object
     */
    protected function getVideoActivityData($data, $duration)
    {
        if (is_string($data)) {
            $data = json_decode($data);
        }

        if (!is_object($data)) {
            $data = (object)['log' => null];
        }

        if (
            empty($data->log)
            || ($duration
                && $duration != $data->log->duration
            )
        ) {
            $data->log = (object)[
                'duration' => $duration,
                'mask'     => $this->checkBitmask()
            ];
        }

        return $data;
    }

    /**
     * Verify and adjust as needed the logged bitmask to the desired percent accuracy
     *
     * @param ?string $source
     *
     * @return ?string
     */
    protected function checkBitmask(?string $source = null): ?string
    {
        if ($accuracy = $this->params->get('videos.accuracy', 1)) {
            $toLength = 100 / $accuracy;

            if (!$source) {
                // Initialize a new bitmask
                return str_repeat('0', $toLength);
            }

            $fromLength = strlen($source);
            if ($fromLength == $toLength) {
                // Bitmask matches desired accuracy
                return $source;
            }

            // Need to adjust the bitmask to a new length
            $amount = abs($toLength - strlen($source));
            $each   = $fromLength < $toLength
                ? $amount / $fromLength
                : $amount / $toLength;

            if (!is_integer($each)) {
                // This is kinda bad and shouldn't happen. But since we're here
                // we'll just have to start from scratch
                return str_repeat('0', $toLength);
            }

            if ($fromLength < $toLength) {
                // Expand to improved accuracy
                $new = str_split($source);
                foreach ($new as &$bit) {
                    $bit = str_repeat($bit, $each + 1);
                }

            } else {
                // Shrink to decreased accuracy
                $new = [];
                $old = str_split($source, $each + 1);
                foreach ($old as $oldBit) {
                    // We'll count a segment as viewed only if the whole segment has been viewed
                    $new[] = (int)(strlen($oldBit) == array_sum(str_split($oldBit)));
                }
            }
        }

        return empty($new) ? '1' : join('', $new);
    }

    /**
     * Calculate the percent viewed from the selected logging bitmask
     * Optionally setting a new bit first
     *
     * @param object  $logData
     * @param ?string $mask
     *
     * @return float
     */
    protected function getVideoPercentWatched(object $logData, ?string $mask = null): float
    {
        if (isset($logData->mask)) {
            if ($logData->mask = $this->checkBitmask($logData->mask)) {
                if ($mask != $logData->mask) {
                    $logData->mask = $this->orMask($logData->mask, $mask);
                }

                $percent = 100 / strlen($logData->mask);

                return array_sum(str_split($logData->mask)) * $percent;
            }
        }

        return 100;
    }

    /**
     * @param string $a
     * @param string $b
     *
     * @return string
     */
    protected function orMask($a, $b): string
    {
        $result = '';

        foreach (str_split($a) as $i => $x) {
            $result .= ($x || !empty($b[$i])) ? '1' : '0';
        }

        return $result;
    }
}

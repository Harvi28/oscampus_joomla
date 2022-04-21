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

use Exception;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;
use Oscampus\Activity\LessonStatus;
use Oscampus\Api;
use OscampusFactory;
use Throwable;

defined('_JEXEC') or die();

class Vimeo extends AbstractVideo
{
    /**
     * @var string
     */
    protected $id = null;

    /**
     * @var Registry
     */
    protected $embedData = null;

    /**
     * @var Api\Vimeo
     */
    protected $api = null;

    /**
     * @var Registry
     */
    protected $videoInfo = null;

    /**
     * @inheritDoc
     */
    protected function init()
    {
        parent::init();

        $content = json_decode($this->lesson->content);

        $this->id = empty($content->id) ? null : $content->id;

        $this->downloadEnabled = $this->downloadEnabled && $this->getApi();
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        if (!$this->lesson->isAuthorised()) {
            return $this->getThumbnail();
        }

        $options = $this->getOptions();

        HTMLHelper::_('script', 'https://player.vimeo.com/api/player.js');

        HTMLHelper::_('script', 'com_oscampus/vimeo.min.js', ['relative' => true]);

        $jsonOptions = json_encode($options);
        HTMLHelper::_('osc.onready', "$.Oscampus.vimeo.init({$jsonOptions});");

        return sprintf('<div id="%s" class="osc-video-responsive"></div>', $options['player']['player_id']);
    }

    /**
     * @inheritDoc
     */
    public function getDownload()
    {
        $download = parent::getDownload();

        if ($download->url === null) {
            try {
                if ($videoInfo = $this->getVideoInfo()) {
                    $title = array_filter([
                        $videoInfo->get('parent_folder.name'),
                        $videoInfo->get('name')
                    ]);
                    $download->set([
                        'id'    => $this->id,
                        'title' => join('/', $title)
                    ]);

                    $downloadOptions = $videoInfo->get('download');
                    if (is_array($downloadOptions) && !empty($downloadOptions[0])) {
                        // Sort list with smallest first
                        usort($downloadOptions, function ($a, $b) {
                            return $a['size'] === $b['size'] ? 0 : (($a['size'] > $b['size']) ? 1 : -1);
                        });

                        $download->set('url', $downloadOptions[0]['link']);
                    }
                }

            } catch (Throwable $error) {
                // Ignore errors
            }
        }

        return $download;
    }

    /**
     * @inheritDoc
     */
    public function getThumbnail($pathOnly = false, $attribs = null)
    {
        if ($api = $this->getApi()) {
            if ($url = $api->getThumbnail($this->id)) {
                return HTMLHelper::_(
                    'image',
                    $url,
                    $this->lesson->title,
                    $attribs,
                    false,
                    (int)$pathOnly
                );
            }
        }

        return parent::getThumbnail($pathOnly, $attribs);
    }

    /**
     * @inheritDoc
     */
    public function prepareActivityProgress(LessonStatus $status, $score = null, $duration = null)
    {
        if ($videoInfo = $this->getVideoInfo()) {
            $duration = $videoInfo->get('duration', $duration);
        }

        parent::prepareActivityProgress($status, $score, $duration);
    }

    /**
     * @inheritDoc
     */
    protected function getOptions(?array $addOptions = []): array
    {
        $options = parent::getOptions([
            'player' => [
                'id'        => $this->id,
                'player_id' => 'osc-vimeo-' . $this->id
            ]
        ]);

        if ($videoInfo = $this->getVideoInfo()) {
            $options['duration'] = $videoInfo->get('duration');
        }

        if ($color = trim(str_replace('#', '', $this->params->get('vimeo.color')))) {
            if (preg_match('/^[\da-f]*$/i', $color)) {
                $options['player']['color'] = $color;
            }
        }

        return $options;
    }

    /**
     * @return Registry
     * @throws Exception
     */
    protected function getEmbed(): Registry
    {
        if ($this->id && $this->embedData === null) {
            $this->embedData = new Registry();

            try {
                $embedOptions = array_merge(
                    ['url' => 'https://vimeo.com/' . $this->id],
                    $this->getOptions()
                );

                $url = 'https://vimeo.com/api/oembed.json?' . http_build_query($embedOptions);

                $data = file_get_contents($url);
                $this->embedData->loadString($data);

            } catch (Throwable $e) {
                OscampusFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        return $this->embedData;
    }

    /**
     * @return Api\Vimeo
     */
    protected function getApi()
    {
        if ($this->api === null) {
            $this->api = \Oscampus\Api\Vimeo::getInstance();
        }

        return $this->api;
    }

    /**
     * @return ?Registry
     */
    protected function getVideoInfo(): ?Registry
    {
        if ($this->videoInfo === null && ($vimeoApi = $this->getApi())) {
            try {
                $this->videoInfo = $vimeoApi->getVideo($this->id);

            } catch (Throwable $error) {
                // Fail silently
            }
        }

        return $this->videoInfo;
    }
}

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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Utility\Utility;
use Oscampus\Activity\LessonStatus;
use Oscampus\Lesson\Type\Wistia\Video;
use OscampusFactory;
use Throwable;

defined('_JEXEC') or die();

class Wistia extends AbstractVideo
{
    /**
     * @inheritdoc
     */
    protected static $icon = 'fa-play';

    /**
     * @inheritdoc
     */
    protected $overlayImage = 'com_oscampus/lesson-overlay-video.png';

    /**
     * @var string
     */
    protected $endpoint = 'https://api.wistia.com/v1/medias/';

    /**
     * @var string
     */
    protected $apiKey = null;

    /**
     * @var string
     */
    protected $id = null;

    protected $media = null;

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
        try {
            if (!$this->lesson->isAuthorised()) {
                return $this->getThumbnail();
            }

            $video = $this->getVideo();

            HTMLHelper::_('script', 'https://fast.wistia.com/assets/external/E-v1.js', [], ['async' => true]);
            HTMLHelper::_('script', 'com_oscampus/wistia.min.js', ['relative' => true]);

            $options = $this->getOptions();
            if ($video) {
                $options['duration'] = $video->duration;
            }
            $options = json_encode($options);

            $wistiaOptions = json_encode([
                'videoFoam'        => true,
                'playerColor'      => '#CCCCCC',
                'fullscreenButton' => true,
                'volumeControl'    => true,
                'playbar'          => true,
                'smallPlayButton'  => true,
                'autoPlay'         => false,
                'focus'            => false
            ]);

            $jScript = <<<JSCRIPT
;jQuery(document).ready(function($) {
    window._wq = window._wq || [];

    _wq.push({'{$this->id}': {$wistiaOptions}});
    _wq.push({
        id     : '{$this->id}',
        onReady: function(video) {
            $.Oscampus.wistia.init(video, {$options});
        }
    });
});
JSCRIPT;
            OscampusFactory::getDocument()->addScriptDeclaration($jScript);

            $class = [
                'wistia_embed',
                'wistia_async_' . $this->id,
                'videoFoam=true'
            ];

            $attribs = [
                sprintf('id="wistia_%s"', $this->id),
                sprintf('class="%s"', join(' ', $class))
            ];
            if ($video) {
                $attribs[] = sprintf('style="width:%spx; height:%spx;"', $video->width, $video->height);
            }

            $html = sprintf('<div %s></div>', join(' ', $attribs));

        } catch (Throwable $e) {
            $html = '<div class="osc-alert-warning">' . $e->getMessage() . '</div>';
        }

        return $html;
    }

    /**
     * @inheritDoc
     */
    public function getThumbnail($pathOnly = false, $attribs = null): string
    {
        if ($thumb = $this->getMediaAsset('StillImageFile')) {
            if ($pathOnly) {
                return $thumb->url;
            }

            if (is_string($attribs)) {
                $attribs = Utility::parseAttributes($attribs);
            }

            $attribs = array_merge(
                [
                    'width'  => $thumb->width,
                    'height' => $thumb->height
                ],
                (array)$attribs
            );

            return HTMLHelper::_('image', $thumb->url, $this->lesson->title, array_filter($attribs));
        }

        return parent::getThumbnail($pathOnly, $attribs);
    }

    /**
     * @inheritDoc
     */
    public function prepareActivityProgress(LessonStatus $status, $score = null, $duration = null)
    {
        if ($video = $this->getVideo()) {
            $duration = $video->duration;
        }

        parent::prepareActivityProgress($status, $score, $duration);
    }

    /**
     * @param string $type
     *
     * @return ?Video
     */
    protected function getMediaAsset(string $type): ?Video
    {
        if ($media = $this->getMedia()) {
            foreach ($media->assets as $index => $asset) {
                if ($asset->type == $type) {
                    return new Video($media, $index);
                }
            }
        }

        return null;
    }

    /**
     * @return ?Video
     */
    protected function getVideo(): ?Video
    {
        if ($media = $this->getMedia()) {
            foreach ($media->assets as $index => $asset) {
                if ($asset->contentType == 'video/mp4') {
                    return new Video($media, $index);
                }
            }
        }

        return null;
    }

    /**
     * @return object
     */
    protected function getMedia(): ?object
    {
        if ($this->media === null) {
            $apiKey = $this->params->get('wistia.apikey');

            $request = $this->endpoint . $this->id . '.json';
            if ($apiKey) {
                $request .= '?access_token=' . $apiKey;
            }

            $response = HttpFactory::getHttp()->get($request);

            $body = json_decode($response->body ?? false);

            if ($response->code >= 400) {
                $body = false;
            }

            $this->media = $body;
        }

        return $this->media ?: null;
    }

    /**
     * @return string
     */
    protected function getApi(): string
    {
        if ($this->apiKey === null) {
            $this->apiKey = $this->params->get('wistia.apikey', '');
        }

        return $this->apiKey;
    }
}

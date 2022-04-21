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

namespace Oscampus\Api;

use Exception;
use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Cache\Controller\CallbackController;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use OscampusFactory;
use Throwable;

defined('_JEXEC') or die();

class Vimeo extends \Vimeo\Vimeo
{
    /**
     * @var static[]
     */
    protected static $instance = [];

    /**
     * @var CallbackController
     */
    protected static $cache = null;

    /**
     * @param ?string $clientId
     * @param ?string $clientSecret
     * @param ?string $accessToken
     *
     * @return Vimeo
     */
    public static function getInstance(
        ?string $clientId = null,
        ?string $clientSecret = null,
        ?string $accessToken = null
    ): ?Vimeo {
        $params = OscampusFactory::getContainer()->params;

        $clientId     = $clientId ?: $params->get('vimeo.id');
        $clientSecret = $clientSecret ?: $params->get('vimeo.secret');
        $accessToken  = $accessToken ?: $params->get('vimeo.token');

        $key = md5(join(':', [$clientId, $clientSecret, $accessToken]));
        if (empty(static::$instance[$key])) {
            if ($clientId && $clientSecret && $accessToken) {
                static::$instance[$key] = new static($clientId, $clientSecret, $accessToken);

            } else {
                static::$instance[$key] = false;
            }

        }

        return static::$instance[$key] ?: null;
    }

    /**
     * @param string $id
     *
     * @return array
     */
    public function getAllThumbnails(string $id): array
    {
        try {
            $response = $this->getResponse('/videos/' . $id . '/pictures');

        } catch (Throwable $e) {
            // ignore
        }

        if (!empty($response['data'])) {
            $data = array_shift($response['data']);
            if (!empty($data['sizes'])) {
                return $data['sizes'];
            }
        }

        return [];
    }

    /**
     * @param string $id
     * @param int    $maxWidth
     *
     * @return ?string
     */
    public function getThumbnail(string $id, int $maxWidth = 300): ?string
    {
        $thumbs = $this->getAllThumbnails($id);
        uasort($thumbs, function ($a, $b) {
            return $a['width'] == $b['width']
                ? 0
                : ($a['width'] < $b['width'] ? 1 : -1);
        });

        foreach ($thumbs as $thumb) {
            if ($thumb['width'] <= $maxWidth) {
                return $thumb['link'];
            }
        }

        return null;
    }

    /**
     * @param string $id
     *
     * @return Registry
     * @throws Exception
     */
    public function getVideo(string $id): Registry
    {
        try {
            $response = $this->getResponse('/videos/' . $id);

            return new Registry($response);

        } catch (Throwable $error) {
            // Handle below
        }

        if ($error->getCode() == 404) {
            throw new Exception(Text::sprintf('COM_OSCAMPUS_ERROR_VIMEO_VIDEO_NOT_FOUND', $id), 404);
        }

        throw new Exception($error->getMessage(), $error->getCode());
    }

    /**
     * @param ?string $userId
     *
     * @return array
     * @throws Exception
     */
    public function getProjects(?string $userId = null): array
    {
        $next = ($userId ? '/users/' . $userId : '/me') . '/projects';

        $projects = [];
        while ($next) {
            $page     = $this->getResponse($next);
            $projects = array_merge($projects, $page['data']);
            $next     = $page['paging']['next'];
        }

        foreach ($projects as &$project) {
            $uri           = explode('/', $project['uri']);
            $project['id'] = array_pop($uri);
        }

        return $projects;
    }

    /**
     * @param ?string $projectId
     * @param ?string $userId
     *
     * @return array[]
     * @throws Exception
     */
    public function getVideos(?string $projectId = null, ?string $userId = null): array
    {
        $next = ($userId ? '/users/' . $userId : '/me')
            . ($projectId ? '/projects/' . $projectId : '')
            . '/videos';

        $videos = [];
        while ($next) {
            $page   = $this->getResponse($next);
            $videos = array_merge($videos, $page['data']);
            $next   = $page['paging']['next'];
        }

        return $videos;
    }

    /**
     * @param string $endPoint
     * @param array  $params
     *
     * @return object
     * @throws Exception
     */
    protected function getResponse(string $endPoint, array $params = []): object
    {
        $key = md5(json_encode(func_get_args()));

        $response = $this->getCache()->get(
            [$this, 'request'],
            [$endPoint, $params],
            $key
        );

        if ($response['status'] == 200) {
            return $response['body'];
        }

        $headers    = $response['headers'];
        $rateLimit  = $headers['X-RateLimit-Limit'] ?? null;
        $remaining  = $headers['X-RateLimit-Remaining'] ?? null;
        $limitReset = $headers['X-RateLimit-Reset'] ?? null;
        if ($limitReset) {
            try {
                $limitReset = new Date($limitReset);
                $limitReset = $limitReset
                    ->setTimezone(OscampusFactory::getUser()->getTimezone())
                    ->format('Y-m-d H:i T');

            } catch (Throwable $e) {
                // ignore
            }
        }

        if ($rateLimit > 0 && $remaining == 0) {
            if ($limitReset) {
                throw new Exception(
                    Text::sprintf('COM_OSCAMPUS_ERROR_VIMEO_LIMIT_REACHED', $limitReset),
                    428
                );
            }

            throw new Exception(Text::_('COM_OSCAMPUS_ERROR_VIMEO_LIMIT_REACHED_WAIT'), 428);
        }

        throw new Exception(Text::_('COM_OSCAMPUS_ERROR_DOWNLOAD_FAILED'), $response['status'], 500);
    }

    /**
     * @return CallbackController
     * @throws Exception
     */
    protected function getCache(): CallbackController
    {
        if (static::$cache === null) {
            static::$cache = Cache::getInstance(
                'callback',
                [
                    'defaultgroup' => 'com_oscampus.vimeo',
                    'caching'      => true,
                    'lifetime'     => OscampusFactory::getApplication()->get('cachetime')
                ]
            );
        }

        return static::$cache;
    }
}

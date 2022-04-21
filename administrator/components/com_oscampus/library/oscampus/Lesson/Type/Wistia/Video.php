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

namespace Oscampus\Lesson\Type\Wistia;

use DateTime;
use OscampusFactory;

defined('_JEXEC') or die();

class Video
{
    /**
     * @var int
     */
    public $id = null;

    /**
     * @var string
     */
    public $embedCode = null;

    /**
     * @var string
     */
    public $name = null;

    /**
     * @var string
     */
    public $project = null;

    /**
     * @var DateTime
     */
    public $created = null;

    /**
     * @var DateTime
     */
    public $updated = null;

    /**
     * @var int
     */
    public $duration = null;

    /**
     * @var string
     */
    public $description = null;

    /**
     * @var string
     */
    public $thumbUrl = null;

    /**
     * @var array
     */
    public $thumbSize = [];

    /**
     * @var string
     */
    public $url = null;

    /**
     * @var int
     */
    public $width = null;

    /**
     * @var int
     */
    public $height = null;

    /**
     * @var int
     */
    public $fileSize = null;

    /**
     * @var string
     */
    public $mimeType = null;

    public function __construct($media, $assetIndex)
    {
        if (isset($media->assets[$assetIndex])) {
            $this->id          = $media->hashed_id;
            $this->embedCode   = $media->embedCode;
            $this->name        = $media->name;
            $this->project     = $media->project->name;
            $this->created     = OscampusFactory::getDate($media->created);
            $this->updated     = OscampusFactory::getDate($media->updated);
            $this->duration    = $media->duration;
            $this->description = $media->description;
            $this->thumbUrl    = $media->thumbnail->url;
            $this->thumbSize   = [$media->thumbnail->width, $media->thumbnail->height];

            $asset          = $media->assets[$assetIndex];
            $this->url      = $asset->url;
            $this->width    = $asset->width;
            $this->height   = $asset->height;
            $this->fileSize = $asset->fileSize;
            $this->mimeType = $asset->contentType;

            if ($_SERVER['HTTPS'] ?? false) {
                // Convert to ssl
                $this->url = str_replace(
                    ['http:', 'embed.wistia'],
                    ['https:', 'embed-ssl.wistia'],
                    $this->url
                );
            }
        }
    }
}

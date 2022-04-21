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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Utility\Utility;
use Oscampus\Lesson\Download;
use OscampusFactory;
use OscampusHelper;

defined('_JEXEC') or die();

abstract class AbstractDownloadable extends AbstractType
{
    /**
     * @var bool
     */
    protected $downloadEnabled = false;

    /**
     * @var Download
     */
    protected $download = null;

    /**
     * @var bool
     */
    protected $downloadAuthorised = null;

    protected function init()
    {
        parent::init();

        $this->download = OscampusFactory::getContainer()->download;
    }

    /**
     * @return Download
     */
    public function getDownload()
    {
        if ($this->download->type === null && $this->lesson) {
            $this->download->set('type', $this->lesson->type);
        }

        return $this->download;
    }

    /**
     * @return bool
     */
    public function isDownloadAuthorised()
    {
        if ($this->downloadAuthorised === null) {
            $this->downloadAuthorised = static::isAuthorised($this->lesson->access);
        }

        return $this->downloadAuthorised;
    }

    /**
     * @return bool
     */
    public function exceededDownloadLimit(): bool
    {
        if ($this->isDownloadAuthorised()) {
            if ($download = $this->getDownload()) {
                $count = $this->getDownloadCount();

                return $count >= $download->limit;
            }
        }

        return true;
    }

    /**
     * Log that this lesson was downloaded
     *
     * @return void
     * @throws Exception
     */
    protected function logDownload()
    {
        $download = $this->getDownload();
        if ($this->user->id && $download->id) {
            $app = OscampusFactory::getApplication();
            $db  = OscampusFactory::getContainer()->dbo;

            $insertRow = (object)[
                'users_id'    => $this->user->id,
                'downloaded'  => OscampusFactory::getDate()->toSql(),
                'ip'          => $app->input->server->get('REMOTE_ADDR'),
                'download_id' => $download->id,
                'lesson_type' => $this->lesson->type,
                'title'       => $download->title
            ];

            $db->insertObject('#__oscampus_downloads', $insertRow);
        }
    }

    /**
     * Returns number of downloads the user has made
     *
     * @param int $period
     *
     * @return int
     */
    public function getDownloadCount($period = null): int
    {
        if ($userId = $this->user->id) {
            if ($period === null) {
                $period = $this->getDownload()->period;
            }

            $query = $this->dbo->getQuery(true)
                ->select('COUNT(DISTINCT CONCAT(lesson_type, download_id))')
                ->from('#__oscampus_downloads')
                ->where('users_id = ' . $userId);

            if ($period) {
                $query->where(sprintf('downloaded >= TIMESTAMP(DATE_SUB(UTC_TIMESTAMP(), INTERVAL %s day))', $period));
            }

            return $this->dbo->setQuery($query)->loadResult();
        }

        return 0;
    }

    /**
     * @param ?string|array $attribs
     * @param ?string       $text
     *
     * @return string
     */
    public function getDownloadButton($attribs = null, ?string $text = null): string
    {
        $link = $this->getDownloadSignupUrl();

        if (
            $this->downloadEnabled
            && $this->lesson
            && ($this->isDownloadAuthorised() || $link)
        ) {
            if (!$text) {
                $texts = [
                    strtoupper($this->lesson->type),
                    'LESSON'
                ];

                $lang = OscampusFactory::getLanguage();
                foreach ($texts as $text) {
                    $text = sprintf('COM_OSCAMPUS_%s_DOWNLOAD', $text);
                    if ($lang->hasKey($text)) {
                        break;
                    }
                }

                $downloadIcon = 'fa-cloud-download';
                $text         = sprintf(
                    '<i class="fa %s"></i> <span class="osc-hide-tablet">%s</span>',
                    $downloadIcon,
                    Text::_($text)
                );
            }

            if ($attribs && is_string($attribs)) {
                $attribs = Utility::parseAttributes($attribs);

            } elseif (!is_array($attribs)) {
                $attribs = [];
            }

            $attribs['class'] = (empty($attribs['class']) ? '' : $attribs['class'] . ' ')
                . 'osc-lesson-download';

            $attribs['data-lesson-id'] = $this->lesson->id;

            return HTMLHelper::_('link', Route::_($link), $text, $attribs);
        }

        return '';
    }

    /**
     * For users not authorised to use downloads, returns a url to sign up / upgrade
     *
     * @return string
     */
    public function getDownloadSignupUrl(): string
    {
        if ($this->downloadEnabled && !$this->isDownloadAuthorised()) {
            if ($this->user->guest) {
                $url = $this->download->signupLink;

            } else {
                $url = $this->download->upgradeLink;
            }

            return $url ? OscampusHelper::normalizeUrl($url) : '';
        }

        return '';
    }

    /**
     * @return void
     * @throws Exception
     */
    public function sendDownload()
    {
        if ($this->downloadEnabled && $this->isDownloadAuthorised()) {
            $download = $this->getDownload();

            if ($this->exceededDownloadLimit()) {
                if ($download->period) {
                    throw new Exception(
                        Text::plural('COM_OSCAMPUS_ERROR_DOWNLOAD_LIMIT_DAYS', $download->period),
                        500
                    );

                } else {
                    throw new Exception(Text::_('COM_OSCAMPUS_ERROR_DOWNLOAD_LIMIT'), 500);
                }
            }

            if ($download->url) {
                $this->logDownload();

                if ($download->mimetype && $download->filename) {
                    // Send via CURL
                    $type      = explode('/', $download->mimeType);
                    $extension = array_pop($type);
                    $filename  = $download->filename . '.' . $extension;

                    header('Content-Type: ' . $download->mimeType);
                    header('Content-Disposition: attachment; filename="' . $filename . '"');

                    // Try to avoid memory issues
                    ini_set('memory_limit', -1);
                    $ch = curl_init($download->url);
                    curl_exec($ch);
                    curl_close($ch);
                    jexit();

                } else {
                    // Use redirect
                    OscampusFactory::getApplication()->redirect($download->url);
                }
            }

            throw new Exception(Text::_('COM_OSCAMPUS_ERROR_DOWNLOAD_NOT_AVAILABLE'), 500);
        }

        // Not authorised
        throw new Exception(Text::_('COM_OSCAMPUS_ERROR_DOWNLOAD_NO_ACCESS'), 500);
    }
}

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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

defined('_JEXEC') or die();

class OscampusControllerLesson extends OscampusControllerJson
{
    /**
     * @return void
     * @throws Exception
     */
    public function progress()
    {
        try {
            $this->checkToken();

            $app       = OscampusFactory::getApplication();
            $container = OscampusFactory::getContainer();

            $lessonId = $app->input->getInt('lessonId');
            $score    = $app->input->getAlnum('score');
            $data     = $app->input->getString('data') ?: null;

            if ($lessonId) {
                $lesson = $container->lesson->loadById($lessonId);

                $container->activity->recordProgress($lesson, $score, $data);
                $this->returnJson(Text::sprintf('COM_OSCAMPUS_ACTIVITY_UPDATED', $lessonId));
            }

        } catch (Throwable $e) {
            $this->returnJson($e);
        }

        $this->returnJson(Text::_('COM_OSCAMPUS_ACTIVITY_NOCHANGE'));
    }

    /**
     * Check if user can download the selected lesson
     *
     * @return void
     * @throws Exception
     */
    public function downloadable()
    {
        try {
            $this->checkToken();

            $app  = OscampusFactory::getApplication();
            $user = OscampusFactory::getUser();

            $lessonId = $app->input->getInt('lessonId');

            $lesson = OscampusFactory::getContainer()->lesson->loadById($lessonId);

            if (!$lesson->isDownloadable()) {
                $this->returnDownloadStatus(true, Text::_('COM_OSCAMPUS_ERROR_DOWNLOAD_NOT_AVAILABLE'));

            } elseif (!$lesson->isDownloadAuthorised()) {
                $url = $lesson->getDownloadSignupUrl();
                if ($url) {
                    $message = $user->guest
                        ? Text::_('COM_OSCAMPUS_VIDEO_DOWNLOAD_SIGNUP')
                        : Text::_('COM_OSCAMPUS_VIDEO_DOWNLOAD_UPGRADE');

                    $linkText = $user->guest
                        ? Text::_('COM_OSCAMPUS_VIDEO_DOWNLOAD_SIGNUP_LINK')
                        : Text::_('COM_OSCAMPUS_VIDEO_DOWNLOAD_UPGRADE_LINK');

                } else {
                    $message  = Text::_('COM_OSCAMPUS_ERROR_DOWNLOAD_NO_ACCESS');
                    $linkText = null;
                }

                $this->returnDownloadStatus(true, $message, $url, $linkText);

            } elseif ($lesson->exceededDownloadLimit()) {
                $period  = $lesson->download->period;
                $message = $period
                    ? Text::plural('COM_OSCAMPUS_ERROR_DOWNLOAD_LIMIT_DAYS', $period)
                    : Text::_('COM_OSCAMPUS_ERROR_DOWNLOAD_LIMIT');
                $this->returnDownloadStatus(true, $message);

            }

            PluginHelper::importPlugin('oscampus');
            $dispatcher    = OscampusEventDispatcher::getInstance();
            $pluginResults = $dispatcher->trigger('oscampusDownloadLimit');
            $errors        = [];
            foreach ($pluginResults as $error) {
                if ($error) {
                    $errors[] = $error;
                }
            }
            if ($errors) {
                $this->returnDownloadStatus(true, join('<br>', $errors));
            }

        } catch (Throwable $error) {
            $this->returnDownloadStatus(true, $error->getMessage());
        }

        $this->returnDownloadStatus();
    }

    /**
     * Outputs standard json for download status requests.
     * Does not return, will end execution of the application
     *
     * @param ?bool   $error
     * @param ?string $message
     * @param ?string $link
     * @param ?string $text
     */
    protected function returnDownloadStatus(
        ?bool $error = false,
        ?string $message = '',
        ?string $link = '',
        ?string $text = ''
    ) {
        $return = [
            'error'   => $error,
            'message' => $message ?: '[OK]',
            'link'    => $link,
            'text'    => $text
        ];

        header('Content-Type: application/json');

        echo json_encode($return);
        jexit();
    }
}

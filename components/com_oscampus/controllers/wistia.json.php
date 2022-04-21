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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Oscampus\Lesson\Type\Wistia\Download;

defined('_JEXEC') or die();

class OscampusControllerWistia extends OscampusControllerJson
{
    /**
     * Toggle the autoplay state
     *
     * @return void
     */
    public function toggleAutoPlayState()
    {
        $session = OscampusFactory::getSession();
        $state   = !((bool)$session->get('oscampus.video.autoplay', true));
        $session->set('oscampus.video.autoplay', $state);

        echo json_encode($state);
    }

    /**
     * Toggle the focus state
     *
     * @return void
     */
    public function toggleFocusState()
    {
        $session = OscampusFactory::getSession();
        $state   = !((bool)$session->get('oscampus.video.focus', true));
        $session->set('oscampus.video.focus', $state);

        echo json_encode($state);
    }

    /**
     * Store the volume level on the session
     *
     * @return void
     * @throws Exception
     */
    public function setVolumeLevel()
    {
        $level = (float)OscampusFactory::getApplication()->input->get('level', 1);

        OscampusFactory::getSession()->set('oscampus.video.volume', $level);

        echo json_encode($level);
    }

    /**
     * Check if user has exceeded their download limit
     *
     * @return void
     * @throws Exception
     */
    public function downloadLimit()
    {
        $result = [
            'authorised' => true,
            'period'     => null,
            'error'      => null
        ];

        try {
            $user     = OscampusFactory::getUser();
            $download = new Download();

            $result['period'] = Text::plural('COM_OSCAMPUS_VIDEO_DOWNLOAD_LIMIT_PERIOD', $download->period);

            // Only usable by authorised users
            if (!$user->authorise('video.download', 'com_oscampus')) {
                $result['authorised'] = false;
                $result['error']      = Text::_('JERROR_ALERTNOAUTHOR');

            } elseif ($download->userExceededLimit()) {
                $result['authorised'] = false;
                $result['error']      = Text::sprintf('COM_OSCAMPUS_ERROR_DOWNLOAD_LIMIT', $result['period']);

            } else {
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
                    $result['authorised'] = false;
                    $result['error']      = join('<br/>', $errors);
                }
            }

        } catch (Throwable $error) {
            $result['authorised'] = false;
            $result['error']      = $error->getMessage();
        }

        echo json_encode($result);
    }
}

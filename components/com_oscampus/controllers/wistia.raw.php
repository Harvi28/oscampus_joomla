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

use Oscampus\Lesson\Type\Wistia\Download;

defined('_JEXEC') or die();

class OscampusControllerWistia extends OscampusControllerBase
{
    /**
     * @return void
     * @throws Exception
     */
    public function download()
    {
        try {
            $this->checkToken();

            $user = OscampusFactory::getUser();

            // Only usable by authorised users
            if (!$user->authorise('video.download', 'com_oscampus')) {
                throw new Exception(JText::_('JERROR_ALERTNOAUTHOR', 401));
            }

            // Check for over download limit
            $download = new Download();
            if ($download->userExceededLimit($user)) {
                throw new Exception(JText::sprintf('COM_OSCAMPUS_ERROR_DOWNLOAD_LIMIT_DAYS', $download->period), 401);
            }

            $id = $this->input->getAlnum('id', null);
            if (empty($id)) {
                throw new Exception(JText::sprintf('COM_OSCAMPUS_ERROR_MISSING_ARG', 'id'), 500);
            }

            // Load the selected Media resource
            $download->send($id);

        } catch (Throwable $error) {
            // Ignore here
        }

        $errorMessage = empty($error) ? JText::_('COM_OSCAMPUS_ERROR_DOWNLOAD_FAILED') : $error->getMessage();
        $referrer = $this->input->server->getString('HTTP_REFERER');
        if (!JUri::isInternal($referrer)) {
            $referrer = 'index.php';
        }

        $app = JFactory::getApplication();
        $app->enqueueMessage($errorMessage, 'warning');
        $app->redirect($referrer);
    }
}

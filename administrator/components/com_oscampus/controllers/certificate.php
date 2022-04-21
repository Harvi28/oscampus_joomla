<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2019 Joomlashack.com. All rights reserved
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

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class OscampusControllerCertificate extends OscampusControllerForm
{
    public function preview()
    {
        try {
            if (!$this->checkToken('get', false)) {
                $this->displayImageError(Text::_('JINVALID_TOKEN_NOTICE'));
                return;
            }

            $inputFilter = OscampusFilterInput::getInstance();
            $certificate = $this->container->certificate;
            $formData    = $inputFilter->clean($this->app->input->get('jform', [], 'array'), 'array_keys');

            $user = OscampusFactory::getUser();

            $template = OscampusTable::getInstance('Certificates');
            $template->bind($formData);

            $course              = OscampusTable::getInstance('Courses');
            $course->title       = 'Sample Course Title';
            $course->teachers_id = $user->id;

            $certificate->createProvisional($course, $user, $template)->render();

        } catch (Throwable $error) {
            $this->displayImageError($error->getMessage());
        }
    }

    public function export()
    {
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__oscampus_certificates');

        $c = $db->setQuery($query, 0, 1)->loadAssoc();

        $c['movable']             = json_decode($c['movable'], true);
        $c['movable']['editBase'] = json_decode($c['movable']['editBase'], true);
        foreach ($c['movable']['overlays'] as &$overlay) {
            $overlay = json_decode($overlay, true);
        }

        echo '<pre>' . print_r($c, 1) . '</pre>';
    }

    /**
     * @param string $message
     *
     * @return void
     */
    protected function displayImageError(string $message)
    {
        $fontFile = $this->container->fonts->getFont('roboto')->filepath;

        $messageWidth = strlen($message);
        do {
            $message = wordwrap($message, $messageWidth);
            $box     = imageftbbox(24, 0, $fontFile, $message);
            $width   = $box[2] - $box[0];
            $height  = $box[3] - $box[5];

            $messageWidth = $messageWidth / 2;
        } while ($width > 800);

        $image = imagecreate($width, $height * 2);

        $backgroundColor = imagecolorallocate($image, 0xff, 0xff, 0xff);
        imagefill($image, 0, 0, $backgroundColor);

        $textColor = imagecolorallocate($image, 0xff, 0, 0);
        imagefttext($image, 24, 0, 0, $height, $textColor, $fontFile, $message);

        header('Content-Type: image/png');
        header('Cache-Control: no-store, max-age=0');
        imagepng($image, null, 0);
        imagedestroy($image);

        jexit();
    }
}

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

use Alledia\Installer\AbstractScript;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();

$includePath = __DIR__ . '/admin/library';
if (!is_dir($includePath)) {
    $includePath = __DIR__ . '/library';
}

if (is_file($includePath . '/Installer/include.php')) {
    require_once $includePath . '/Installer/include.php';
} else {
    throw new Exception('[OSCampus] Joomlashack Installer not found');
}

class com_oscampusInstallerScript extends AbstractScript
{
    /**
     * @var CMSApplication
     */
    protected $app = null;

    /**
     * @var string
     */
    protected $gdVersion = null;

    /**
     * @inheritDoc
     *
     * @return void
     * @throws Exception
     */
    public function __construct($parent)
    {
        parent::__construct($parent);

        $this->app = Factory::getApplication();
    }

    /**
     * @inheritDoc
     */
    public function preFlight($type, $parent)
    {
        return parent::preFlight($type, $parent) && $this->preChecks($type);
    }

    /**
     * @inheritDoc
     */
    public function postFlight($type, $parent)
    {
        parent::postFlight($type, $parent);

        try {
            $this->setDefaultParams();

            if ($type == 'update') {
                $this->moveLegacyLayouts();
                $this->updateDownloadTable();
            }

        } catch (Throwable $e) {
            $this->setMessage($e->getMessage(), 'error');
        }
    }

    /**
     * Update component default parameters as needed
     */
    protected function setDefaultParams()
    {
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('extension_id, params')
            ->from('#__extensions')
            ->where([
                $db->quoteName('type') . '=' . $db->quote('component'),
                $db->quoteName('element') . '=' . $db->quote('com_oscampus')
            ]);

        $oscampus = $db->setQuery($query)->loadObject();

        $params    = json_decode($oscampus->params);
        $newParams = json_decode($oscampus->params);

        /*
         * @since v1.1.6 - Set formerly hardcoded quiz options in config
         */
        if (!isset($newParams->quizzes)) {
            $newParams->quizzes = [
                'passingScore' => 70,
                'timeLimit'    => 600,
                'limitAlert'   => 60
            ];
        }

        /*
         * @since v1.1.6 - Move legacy video download parameters
         */
        if (isset($newParams->videos)) {
            $videos = $newParams->videos;
            if (isset($videos->downloadLimit)) {
                $videos->download->limit = $videos->downloadLimit;
                unset($videos->downloadLimit);
            }
            if (isset($videos->downloadLimitPeriod)) {
                $videos->download->period = $videos->downloadLimitPeriod;
                unset($videos->downloadLimit, $videos->downloadLimitPeriod);
            }
        }

        /*
         * @since v1.1.6 - Move legacy certificate parameters
         */
        if (!empty($newParams->certificateImage)) {
            $newParams->certificates = [
                'image' => $newParams->certificateImage
            ];
            unset($newParams->certificateImage);
        }

        /*
         * @since v2.0.0 - Create a legacy default certificate from original defaults
         *
         * NOTE:
         *    - This MUST be checked AFTER the v1.1.6 legacy certificate parameter
         *    - This will almost certainly mess up certificates with certificate view overrides
         */
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from('#__oscampus_certificates');

        $certificates = $db->setQuery($query)->loadResult();
        if ($certificates == 0) {
            // Assume this is a new install or pre v2.x upgrade
            $sourceFolder           = $this->installer->getPath('source');
            $defaultCertificateFile = $sourceFolder . '/admin/sql/install/mysql/default_certificate.json';

            if (is_file($defaultCertificateFile)) {
                $defaultCertificate = json_decode(file_get_contents($defaultCertificateFile));
                if ($defaultCertificate) {
                    if (empty($newParams->certificates)) {
                        $newParams->certificates = (object)[];
                    }
                    if (!isset($newParams->certificates->enabled)) {
                        $newParams->certificates->enabled = (int)!empty($this->gdVersion);
                    }

                    $user = Factory::getUser();

                    $defaultCertificate->created          = gmdate('Y-m-d H:i:s');
                    $defaultCertificate->created_by       = $user->id;
                    $defaultCertificate->created_by_alias = $user->name;

                    try {
                        $db->insertObject('#__oscampus_certificates', $defaultCertificate, 'id');
                        $id = (int)$db->insertid();

                        $this->setMessage(Text::_('COM_OSCAMPUS_INSTALL_CERTIFICATE_CREATED'), 'notice');

                        if (!empty($newParams->certificates->font)) {
                            $newParams->certificates->font = null;
                        }

                    } catch (Throwable $error) {
                        $this->setMessage(
                            Text::sprintf('COM_OSCAMPUS_INSTALL_CERTIFICATE_ERROR_CREATED', $error->getMessage()),
                            'error'
                        );
                    }

                } else {
                    $this->setMessage(
                        Text::sprintf(
                            'COM_OSCAMPUS_INSTALL_CERTIFICATE_ERROR_INVALID_JSON',
                            str_replace($sourceFolder, '', $defaultCertificateFile)
                        ),
                        'warning'
                    );
                }

            } else {
                $this->setMessage(
                    Text::sprintf(
                        'COM_OSCAMPUS_INSTALL_CERTIFICATE_ERROR_NOT_FOUND',
                        str_replace($sourceFolder, '', $defaultCertificateFile)
                    ),
                    'warning'
                );
            }
        }

        if ($params != $newParams) {
            // Apply changes
            $oscampus->params = json_encode($newParams);
            $db->updateObject('#__extensions', $oscampus, 'extension_id');
        }
    }

    /**
     * Check for layout overrides in the site templates and move them
     * to the new location
     *
     * @since v1.2.6
     */
    protected function moveLegacyLayouts()
    {
        $folders = Folder::folders(JPATH_SITE . '/templates', 'com_oscampus', true, true);
        foreach ($folders as $folder) {
            if (strpos($folder, '/layouts/com_oscampus')) {
                $files = Folder::files($folder, '.', false, true);
                foreach ($files as $file) {
                    $baseFolder = dirname($file) . '/listing/';
                    $fileName   = basename($file);

                    if (!is_dir($baseFolder)) {
                        Folder::create($baseFolder);
                    }
                    File::move($file, $baseFolder . $fileName);
                }
            }
        }
    }

    /**
     * Finalize updates to download logging table.
     * Move any old logs from the wistia table to the new downloads table
     *
     * @return void
     * @throws Exception
     *
     * @since v1.3.0
     */
    protected function updateDownloadTable()
    {
        $db = Factory::getDbo();

        $tables = $db->setQuery(
            sprintf(
                'SHOW TABLES LIKE %s',
                $db->quote($db->replacePrefix('#__oscampus_wistia_downloads'))
            )
        )->loadColumn();

        if ($tables) {
            $columns = [
                'downloaded'  => 'downloaded',
                'ip'          => 'ip',
                'download_id' => 'media_hashed_id',
                'title'       => sprintf(
                    'TRIM(BOTH %1$s FROM CONCAT_WS(%1$s, media_project_name, media_name))',
                    $db->quote('/')
                ),
                'lesson_type' => $db->quote('wistia'),
                'users_id'    => 'users_id'
            ];
            $db->setQuery(
                sprintf(
                    'INSERT INTO #__oscampus_downloads (%s) SELECT %s from #__oscampus_wistia_downloads',
                    join(',', array_keys($columns)),
                    join(',', $columns)
                )
            );

            $db->execute();
            $db->setQuery('DROP TABLE #__oscampus_wistia_downloads')->execute();
        }
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    protected function preChecks(string $type): bool
    {
        $ready = true;

        if (function_exists('gd_info')) {
            $this->gdVersion = gd_info();

        } elseif ($this->previousManifest && version_compare($this->previousManifest->version, '2', 'lt')) {
            // GD Library not available, check for existing awarded certificates
            $db = Factory::getDbo();

            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from('#__oscampus_certificates');

            if ($legacyCertificates = (int)$db->setQuery($query)->loadResult()) {
                // Fail the upgrade due to existing certificates
                $this->app->enqueueMessage(Text::_('COM_OSCAMPUS_INSTALL_ERROR_GD_REQUIRED'), 'error');
                $this->app->enqueueMessage(Text::_('COM_OSCAMPUS_WARNING_GD_CONTACT_HOST'), 'error');

                $ready = false;

            } else {
                $this->app->enqueueMessage(Text::_('COM_OSCAMPUS_WARNING_GD_CONTACT_HOST'), 'warning');
            }

        } elseif ($type !== 'update') {
            $this->app->enqueueMessage(Text::_('COM_OSCAMPUS_WARNING_GD_CONTACT_HOST'), 'warning');
        }

        return $ready;
    }
}

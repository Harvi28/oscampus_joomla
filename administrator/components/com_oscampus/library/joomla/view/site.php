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
use Joomla\Registry\Registry as Registry;

defined('_JEXEC') or die();

abstract class OscampusViewSite extends OscampusView
{
    /**
     * @var Registry
     */
    protected $params = null;

    /**
     * @var int
     */
    protected $step = 1;

    /**
     * OscampusViewSite constructor.
     *
     * @param array $config
     *
     * @return void
     * @throws Exception
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $theme     = OscampusFactory::getContainer()->theme;
        $name      = empty($config['theme']) ? $theme->name : $config['theme'];
        $component = OscampusApplicationHelper::getComponentName();
        $view      = $this->getName();

        $pathsAmended = [];
        foreach ($this->_path['template'] as $path) {
            if (strpos($path, OSCAMPUS_SITE) === 0) {
                $pathsAmended[] = OSCAMPUS_SITE . "/themes/{$name}/{$view}";

            } elseif (strpos($path, $component) > 0) {
                $pathsAmended[] = str_replace($component, $component . '/themes/' . $name, $path);
            }

            $pathsAmended[] = $path;
        }

        $this->_path['template'] = $pathsAmended;
    }

    /**
     * Display an incrementing step header. Each subsequent
     * use adds one to the step number
     *
     * @param $text
     *
     * @return string
     */
    protected function stepHeading($text)
    {
        $step = Text::sprintf('COM_OSCAMPUS_HEADING_STEP', $this->step++);

        return '<h3><span>' . $step . '</span>' . $text . '</h3>';
    }

    /**
     * @return Registry
     */
    protected function getParams()
    {
        if ($this->params === null) {
            /** @var OscampusModel $model */
            $model = $this->getModel();
            if ($model && method_exists($model, 'getParams')) {
                $this->params = $model->getParams();

            } else {
                $this->params = new Registry();
            }
        }

        return $this->params;
    }

    /**
     * Get the page heading from the menu definition if set
     *
     * @param string $default
     * @param bool   $translate
     *
     * @return string
     */
    protected function getHeading($default = null, $translate = true)
    {
        $params = $this->getParams();

        if ($params->get('show_page_heading')) {
            $heading = $params->get('page_heading');
        } else {
            $heading = $translate ? Text::_($default) : $default;
        }
        return $heading;
    }

    /**
     * Append page class suffix if specified
     *
     * @param string $base
     *
     * @return string
     */
    protected function getPageClass($base = '')
    {
        $suffix = $this->getParams()->get('pageclass_sfx');
        return trim($base . ' ' . $suffix);
    }

    /**
     * Set document title and metadata
     *
     * @param array|object|Registry $metadata
     * @param string                $defaultTitle
     * @param string                $defaultDescription
     */
    protected function setMetadata($metadata, $defaultTitle = null, $defaultDescription = null)
    {
        if (!$metadata instanceof Registry) {
            $metadata = new Registry($metadata);
        }
        $doc = OscampusFactory::getDocument();

        $title = $metadata->get('title') ?: $defaultTitle;
        if ($title) {
            $doc->setTitle($title);
        }

        $description = $metadata->get('description');
        if (!$description && $defaultDescription) {
            $filter = OscampusFilterInput::getInstance();

            $description = $filter->clean($defaultDescription);
            if (strlen($description) > 150) {
                $description = preg_replace('/\s*\w*$/', '', substr($description, 0, 160)) . '...';
            }
        }
        if ($description) {
            $doc->setMetaData('description', $description);
        }
    }

    /**
     * Load a template from a different view
     *
     * @param string $view
     * @param string $name
     *
     * @return string
     * @throws Exception
     */
    protected function loadViewTemplate($view, $name)
    {
        $path = OSCAMPUS_SITE . '/views/' . strtolower($view) . '/tmpl';
        if (!is_dir($path)) {
            throw new Exception(Text::sprintf('COM_OSCAMPUS_ERROR_VIEW_NAME_INVALID', $view));
        }

        // Add include path. This will override any local version
        $originalPaths = $this->_path['template'];
        $this->addTemplatePath($path);

        $output = $this->loadTemplate($name);

        $this->_path['template'] = $originalPaths;

        return $output;
    }
}

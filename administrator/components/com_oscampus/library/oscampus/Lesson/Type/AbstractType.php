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

use Exception;
use JDatabaseDriver;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use JUser;
use JUtility;
use Oscampus\Activity\LessonStatus;
use Oscampus\Course;
use Oscampus\Lesson;
use OscampusFactory;
use ReflectionClass;
use SimpleXMLElement;
use Throwable;

defined('_JEXEC') or die();

abstract class AbstractType
{
    /**
     * @var string
     */
    protected static $icon = 'fa-check';

    /**
     * @var string
     */
    protected $thumbnail = 'com_oscampus/default-lesson.jpg';

    /**
     * @var null
     */
    protected $overlayImage = 'com_oscampus/lesson-overlay-text.png';

    /**
     * @var Lesson
     */
    protected $lesson = null;

    /**
     * @var Registry
     */
    protected $params = null;

    /**
     * @var JDatabaseDriver
     */
    protected $dbo = null;

    /**
     * @var JUser
     */
    protected $user = null;

    public function __construct(Lesson $lesson, Registry $params, JDatabaseDriver $dbo)
    {
        $this->lesson = $lesson;
        $this->params = $params;
        $this->dbo    = $dbo;
        $this->user   = OscampusFactory::getUser();

        $this->init();
    }

    /**
     * @param string          $type
     * @param Lesson          $lesson
     * @param Registry        $params
     * @param JDatabaseDriver $dbo
     *
     * @return ?AbstractType
     */
    public static function getInstance($type, Lesson $lesson, Registry $params, JDatabaseDriver $dbo)
    {
        if ($className = static::getClassName($type)) {
            return new $className($lesson, $params, $dbo);
        }

        return null;
    }

    /**
     * @param string $type
     *
     * @return AbstractType A string containing the FQN of a Lesson Type class
     */
    final public static function getClassName($type)
    {
        $type = ucfirst($type) ?: 'DefaultType';

        /** @var AbstractType $className */
        $className = __NAMESPACE__ . '\\' . $type;

        return class_exists($className) ? $className : null;
    }

    /**
     * Optional processing for subclasses on instance loading
     *
     * @return void
     */
    protected function init()
    {
        // to be customized by subclasses
    }

    /**
     * Each lesson type must provide the output, loading of
     * js, etc needed for their particular needs. Return
     * whatever template code needs for output
     *
     * @return mixed
     */
    abstract public function render();

    /**
     * Returns the base thumbnail image for the lesson
     *
     * @param bool         $pathOnly
     * @param string|array $attribs
     *
     * @return string
     */
    public function getThumbnail($pathOnly = false, $attribs = null)
    {
        if ($this->thumbnail) {
            $relative = $this->thumbnail[0] !== '/';

            return HTMLHelper::_('image', $this->thumbnail, $this->lesson->title, $attribs, $relative, $pathOnly);
        }

        return Course::getImage(null, $this->lesson->title, $attribs, $pathOnly);
    }

    /**
     * Returns an <img> tag using the lesson thumbnail overlaid by an icon
     *
     * @param string|array $attribs
     *
     * @return string
     */
    public function getOverlayImage($attribs = [])
    {
        if (is_string($attribs)) {
            $attribs = JUtility::parseAttributes($attribs);
        }

        if ($overlay = $this->overlayImage) {
            if ($absolute = ($overlay[0] === '/')) {
                $overlay = substr($overlay, 1);
            }

            if (HTMLHelper::_('image', $overlay, null, null, !$absolute, true)) {
                if (empty($attribs['style'])) {
                    $attribs['style'] = '';
                }
                $attribs['style'] .= sprintf(
                    'background-image:url(%s); background-size: 100%% auto;',
                    $this->getThumbnail(true)
                );

                return HTMLHelper::_('image', $overlay, $this->lesson->title, $attribs, !$absolute);
            }
        }

        return $this->getThumbnail(false, $attribs);
    }

    /**
     * @param ?string $type
     *
     * @return string
     */
    public static function getIcon($type = null)
    {
        if ($type) {
            if ($renderClass = static::getClassName($type)) {
                return $renderClass::getIcon();
            }
        }

        return static::$icon;
    }

    /**
     * @param ?LessonStatus $status
     *
     * @return string
     */
    public static function getStatusId(LessonStatus $status = null)
    {
        return LessonStatus::VIEWED;
    }

    /**
     * get the current user state from a cookie
     *
     * @param string  $name
     * @param ?string $default
     * @param ?string $filter
     *
     * @return mixed
     */
    public function getUserState($name, $default = null, $filter = 'string')
    {
        return OscampusFactory::getApplication()
            ->input
            ->cookie
            ->get($name, $default, $filter);
    }

    /**
     * Save a user state in a session cookie, returning the original value
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return mixed
     */
    public function setUserState($name, $value)
    {
        $oldValue = $this->getUserState($name);

        setcookie($name, $value);

        return $oldValue;
    }

    /**
     * Prepare an LessonStatus for recording user progress.
     *
     * @param LessonStatus $status
     * @param numeric      $score
     * @param mixed        $data
     *
     * @return void
     */
    abstract public function prepareActivityProgress(LessonStatus $status, $score = null, $data = null);

    /**
     * Prepare data for use in lesson admin UI.
     *
     * @param Registry $data
     *
     * @return void
     */
    protected function prepareAdminData(Registry $data)
    {
        $content = $data->get('content');
        if ($content && is_string($content)) {
            $data->set('content', json_decode($content, true));
        }
    }

    /**
     * Safely load xml file from the expected file for the lesson type
     * Used for lesson editing
     *
     * @param Registry $data
     *
     * @return SimpleXMLElement
     */
    public function getLessonForm(Registry $data)
    {
        try {
            $typeClass   = new ReflectionClass($this);
            $classPath   = $typeClass->getFileName();
            $xmlFilePath = dirname($classPath) . '/' . strtolower(basename($classPath, 'php')) . 'xml';

            if (is_file($xmlFilePath)) {
                $xml = simplexml_load_file($xmlFilePath);

            } else {
                $classSpaces = explode('\\', get_class($this));
                throw new Exception(
                    Text::sprintf('COM_OSCAMPUS_ERROR_LESSON_TYPE_MISSING_FORM', array_pop($classSpaces))
                );
            }

        } catch (Throwable $e) {
            $xml     = simplexml_load_file(__DIR__ . '/default_type.xml');
            $content = $xml->xpath("//fieldset[@name='content']");

            $content[0]['description'] = $e->getMessage();
        }

        $this->prepareAdminData($data);

        return $xml;
    }

    /**
     * The default procedure to vet the lesson content on saving
     * changes in admin. Note passing of data object allowing
     * modification of any of the form POST data
     *
     * @param Registry $data
     *
     * @throws Exception
     */
    public function saveAdminChanges(Registry $data)
    {
        $content = $data->get('content');
        if (!is_string($content)) {
            $content = json_encode($content);
        }
        $data->set('content', $content);
    }

    /**
     * Permit any custom authorisation checks based on lesson type
     *
     * @param string  $access
     * @param ?string $type
     *
     * @return bool
     */
    public static function isAuthorised($access, $type = null)
    {
        if ($type) {
            $subClass = static::getClassName($type) ?: static::class;

            return $subClass::isAuthorised($access);
        }

        $user       = OscampusFactory::getUser();
        $viewLevels = $user->getAuthorisedViewLevels();

        return $user->authorise('core.manager') || in_array($access, $viewLevels);
    }

    public function __toString()
    {
        return get_class($this);
    }
}

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

namespace Oscampus;

use Exception;
use JDatabaseDriver;
use JDatabaseQuery;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Oscampus\Activity\LessonStatus;
use Oscampus\Lesson\Download;
use Oscampus\Lesson\Properties;
use Oscampus\Lesson\Type\AbstractDownloadable;
use Oscampus\Lesson\Type\AbstractType;
use OscampusModelTrait;

defined('_JEXEC') or die();

/**
 * Class Lesson
 *
 * @package Oscampus
 *
 * @property-read int          $index
 * @property-read int          $id
 * @property-read int          $modules_id
 * @property-read int          $courses_id
 * @property-read string       $type
 * @property-read string       $title
 * @property-read string       $alias
 * @property-read mixed        $content
 * @property-read string       $description
 * @property-read string       $icon
 * @property-read int          $access
 * @property-read bool         $published
 * @property-read bool         $authorised
 * @property-read Properties   $previous
 * @property-read Properties   $current
 * @property-read Properties   $next
 * @property-read object[]     $files
 * @property-read AbstractType $renderer
 * @property-read Download     $download
 *
 * @method bool   isDownloadAuthorised
 * @method string getDownloadSignupUrl
 * @method bool   exceededDownloadLimit
 * @method void   sendDownload
 */
class Lesson extends AbstractBase
{
    use OscampusModelTrait;

    /**
     * @var string
     */
    public $courseTitle = null;

    /**
     * @var string
     */
    public $moduleTitle = null;

    /**
     * @var Registry
     */
    public $metadata = null;

    /**
     * @var int
     */
    protected $index = null;

    /**
     * @var Properties
     */
    protected $previous = null;

    /**
     * @var Properties
     */
    protected $current = null;

    /**
     * @var Properties
     */
    protected $next = null;

    /**
     * @var AbstractType
     */
    protected $renderer = null;

    /**
     * @var bool
     */
    protected $authorised = null;

    /**
     * @var Registry
     */
    protected $params = null;

    /**
     * @var object[]
     */
    protected $files = [];

    /**
     * @inheritDoc
     */
    public function __construct(JDatabaseDriver $dbo, Properties $properties, Registry $params)
    {
        $this->params = $params;

        $this->previous = $properties;
        $this->current  = clone $properties;
        $this->next     = clone $properties;

        parent::__construct($dbo);
    }

    /**
     * We allow access to protected and virtual properties in this order
     *    $this get method
     *    $this is method
     *    $this property
     *    $this->current property (current lesson property)
     *
     *    $this->renderer get method
     *    $this->>renderer is method
     *    $this->renderer property
     *
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        $getMethod = 'get' . ucfirst($name);
        $isMethod  = 'is' . ucfirst($name);

        if (method_exists($this, $getMethod)) {
            return $this->$getMethod();

        } elseif (property_exists($this->current, $name)) {
            return $this->current->{$name};

        } elseif (method_exists($this, $isMethod)) {
            return $this->$isMethod();

        } elseif (property_exists($this, $name)) {
            return $this->{$name};

        }

        $renderer = $this->getRenderer();
        if (method_exists($renderer, $getMethod)) {
            return $renderer->$getMethod();

        } elseif (method_exists($renderer, $isMethod)) {
            return $renderer->$isMethod();

        } elseif (property_exists($renderer, $name)) {
            return $renderer->{$name};
        }

        return null;
    }

    /**
     * Present public renderer methods as self
     *
     * @param $name
     * @param $arguments
     *
     * @return string
     */
    public function __call($name, $arguments)
    {
        $renderer = $this->getRenderer();
        if (method_exists($renderer, $name)) {
            return call_user_func_array([$renderer, $name], $arguments);
        }

        return null;
    }

    /**
     * Use a zero-based index number to retrieve a lesson
     *
     * @param int $index
     * @param int $courseId
     *
     * @return Lesson
     */
    public function loadByIndex(int $index, int $courseId): Lesson
    {
        $query = $this->getQuery()
            ->where('course.id = ' . $courseId);

        $offset = max(0, $index - 1);
        $limit  = $index ? 3 : 2;
        $data   = $this->dbo->setQuery($query, $offset, $limit)->loadObjectList();

        if (count($data) == 1) {
            // Only one lesson found - no previous or next
            array_unshift($data, null);
            $data[] = null;

        } elseif (count($data) == 2) {
            if ($offset == 0) {
                // No previous lesson
                array_unshift($data, null);

            } else {
                // No next lesson
                $data[] = null;
            }
        }

        $this->setLessons($index, $data);

        return $this;
    }

    /**
     * Load lesson using its ID.
     *
     * @param ?int $lessonId
     *
     * @return Lesson
     * @throws Exception
     */
    public function loadById(?int $lessonId): Lesson
    {
        $query = $this->dbo->getQuery(true)
            ->select('course.id')
            ->from('#__oscampus_lessons AS lesson')
            ->innerJoin('#__oscampus_modules AS module ON module.id = lesson.modules_id')
            ->innerJoin('#__oscampus_courses AS course ON course.id = module.courses_id')
            ->where([
                'lesson.id = ' . (int)$lessonId,
                'lesson.published = 1',
                'course.published = 1',
                $this->whereAccess('course.access')
            ]);

        if ($courseId = $this->dbo->setQuery($query)->loadResult()) {
            $query = $this->getQuery()->where('course.id = ' . $courseId);

            $lessons = $this->dbo->setQuery($query)->loadObjectList();

            foreach ($lessons as $index => $lesson) {
                if ($lesson->id == $lessonId) {
                    $data = [
                        ($index > 0) ? $lessons[$index - 1] : null,
                        $lesson,
                        $lessons[$index + 1] ?? null
                    ];

                    $this->setLessons($index, $data);
                }
            }

            return $this;
        }

        throw new Exception(Text::_('COM_OSCAMPUS_ERROR_COURSE_NOT_FOUND'), 404);
    }

    /**
     * Wrapper to pass request to properties class.
     *
     * @return bool
     */
    public function isAuthorised(): bool
    {
        if ($this->authorised === null && $renderer = $this->getRenderer()) {
            $this->authorised = $renderer::isAuthorised($this->access, $this->type);
        }

        return $this->authorised;
    }

    /**
     * Determines if this lesson has downloadable methods and options
     *
     * @return bool
     */
    public function isDownloadable(): bool
    {
        return $this->isAuthorised()
            && $this->getRenderer() instanceof AbstractDownloadable;
    }

    /**
     * The primary rendering function for lesson content
     *
     * @return mixed
     */
    public function render()
    {
        if ($renderer = $this->getRenderer()) {
            return $renderer->render();
        }

        return null;
    }

    /**
     * Get a thumbnail for the lesson
     *
     * @param bool         $pathOnly
     * @param string|array $attribs
     *
     * @return ?string
     */
    public function getThumbnail(bool $pathOnly = false, $attribs = null): ?string
    {
        if ($renderer = $this->getRenderer()) {
            return $renderer->getThumbnail($pathOnly, $attribs);
        }

        return null;
    }

    /**
     * @param string|array $attribs
     *
     * @return ?string
     */
    public function getOverlayImage($attribs = []): ?string
    {
        if ($renderer = $this->getRenderer()) {
            return $renderer->getOverlayImage($attribs);
        }

        return null;
    }

    public function loadAdminForm(Form $form, Registry $data)
    {
        $renderer = $this->getRenderer($data->get('type'));

        $xml = $renderer ? $renderer->getLessonForm($data) : null;
        if ($xml) {
            $form->load($xml, true, 'form');
        }
    }

    /**
     * Opportunity for Lesson Types to verify and massage content data
     * as needed
     *
     * @param Registry $data
     *
     * @return void
     * @throws Exception
     */
    public function saveAdminChanges(Registry $data)
    {
        $renderer = $this->getRenderer($data->get('type'));
        if ($renderer) {
            $renderer->saveAdminChanges($data);
        }
    }

    /**
     * Get the base query for finding lessons
     *
     * @return JDatabaseQuery
     */
    protected function getQuery(): JDatabaseQuery
    {
        return $this->dbo->getQuery(true)
            ->select([
                'lesson.*',
                'module.courses_id',
                'module.title AS module_title',
                'course.title AS course_title'
            ])
            ->from('#__oscampus_lessons AS lesson')
            ->innerJoin('#__oscampus_modules AS module ON module.id = lesson.modules_id')
            ->innerJoin('#__oscampus_courses AS course ON course.id = module.courses_id')
            ->where([
                'lesson.published = 1',
                'course.published = 1',
                $this->whereAccess('course.access')
            ])
            ->order('module.ordering, lesson.ordering');
    }

    /**
     * @param int              $index
     * @param array[]|object[] $data
     */
    protected function setLessons(int $index, array $data)
    {
        $this->index = $index;

        $currentValues     = (object)$data[1];
        $this->courseTitle = $currentValues->course_title;
        $this->moduleTitle = $currentValues->module_title;
        $this->metadata    = new Registry($currentValues->metadata);

        $this->previous->load($data[0]);
        $this->current->load($data[1]);
        $this->next->load($data[2]);

        $this->renderer = $this->getRenderer();
    }

    /**
     * @param ?string $type
     *
     * @return ?AbstractType
     */
    protected function getRenderer(?string $type = null): ?AbstractType
    {
        if ($type) {
            return AbstractType::getInstance($type, $this, $this->params, $this->dbo);
        }

        if (
            $this->renderer === null
            && $this->current
            && $this->current->type
        ) {
            $this->renderer = AbstractType::getInstance($this->current->type, $this, $this->params, $this->dbo);
        }

        return $this->renderer;
    }

    /**
     * @param LessonStatus $status
     * @param ?string      $score
     * @param ?mixed       $data
     *
     * @return void
     */
    public function prepareActivityProgress(LessonStatus $status, ?string $score = null, $data = null)
    {
        if ($renderer = $this->getRenderer()) {
            $renderer->prepareActivityProgress($status, $score, $data);
        }
    }
}

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

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Oscampus\Course;

defined('_JEXEC') or die();


class OscampusModelCourse extends OscampusModelAdmin
{
    /**
     * @var CMSObject
     */
    protected $item = null;

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getItem($pk = null)
    {
        if ($this->item === null) {
            $this->item = parent::getItem($pk);

            if (!$this->item->id) {
                $this->item->setProperties([
                    'publish_up' => date('Y-m-d H:i'),
                    'access'     => 1,
                    'published'  => 1,
                    'image'      => ltrim(Course::getImage(), '/'),
                    'pathways'   => [],
                    'tags'       => []
                ]);

            } else {
                $this->item->pathways = $this->getPathways($this->item->id);
                $this->item->tags     = $this->getTags($this->item->id);
                $this->item->files    = $this->getFiles($this->item->id);

                if ($this->item->introtext && $this->item->description) {
                    $this->item->description = trim($this->item->introtext)
                        . '<hr id="system-readmore" />'
                        . trim($this->item->description);
                }
            }
        }

        return $this->item;
    }

    /**
     * @param int $courseId
     *
     * @return string[]
     * @throws Exception
     */
    public function getPathways($courseId = null): array
    {
        if ($courseId = (int)($courseId ?: $this->getState($this->getName() . '.id'))) {
            $db = $this->getDbo();

            $query = $db->getQuery(true)
                ->select('pathways_id')
                ->from('#__oscampus_courses_pathways')
                ->where('courses_id = ' . $courseId);

            $pathways = $db->setQuery($query)->loadColumn();
            if ($error = $db->getErrorMsg()) {
                $this->setError($error);
            } else {
                return $pathways;
            }
        }

        return [];
    }

    /**
     * @param int $courseId
     *
     * @return string[]
     * @throws Exception
     */
    public function getTags($courseId = null): array
    {
        if ($courseId = (int)($courseId ?: $this->getState($this->getName() . '.id'))) {
            $db    = $this->getDbo();
            $query = $db->getQuery(true)
                ->select('tags_id')
                ->from('#__oscampus_courses_tags')
                ->where('courses_id = ' . $courseId);

            $tags = $db->setQuery($query)->loadColumn();
            if ($error = $db->getErrorMsg()) {
                $this->setError($error);
            } else {
                return $tags;
            }
        }

        return [];
    }

    /**
     * @param int $courseId
     *
     * @return object[]
     * @throws Exception
     */
    public function getFiles($courseId = null): array
    {
        if ($courseId = (int)($courseId ?: $this->getState($this->getName() . '.id'))) {
            $db    = $this->getDbo();
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__oscampus_files AS file')
                ->where('file.courses_id = ' . $courseId)
                ->order([
                    'file.ordering ASC',
                    'file.path ASC'
                ]);

            $files = $db->setQuery($query)->loadObjectList();
            if ($error = $db->getErrorMsg()) {
                $this->setError($error);
            } else {
                return $files;
            }
        }

        return [];
    }

    /**
     * Due to m:m relationship to pathways, we do a special reordering here
     *
     * @inheritDoc
     * @throws Exception
     */
    public function saveorder($pks = null, $order = null)
    {
        $app       = OscampusFactory::getApplication();
        $filters   = $app->input->get('filter', [], 'array');
        $pathwayId = isset($filters['pathway']) ? (int)$filters['pathway'] : 0;

        if ($pathwayId) {
            $db  = $this->getDbo();
            $sql = 'UPDATE #__oscampus_courses_pathways SET ordering = %s WHERE courses_id = %s AND pathways_id = ' . $pathwayId;
            foreach ($pks as $index => $courseId) {
                $db->setQuery(sprintf($sql, $order[$index], $courseId))->execute();
                if ($error = $db->getErrorMsg()) {
                    $this->setError($error);
                    return false;
                }
            }

            return true;
        }

        $this->setError(Text::_('COM_OSCAMPUS_ERROR_COURSE_REORDER_PATHWAY'));

        return false;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function getReorderConditions($table)
    {
        $app = OscampusFactory::getApplication();

        if ($pathwayId = $app->input->getInt('filter_pathway')) {
            return ['pathways_id = ' . $pathwayId];
        }

        return [];
    }

    /**
     * @inheritDoc
     */
    protected function prepareTable($table)
    {
        $descriptions = preg_split('#<hr\s+id=(["\'])system-readmore(["\'])\s*/*>#i', $table->description, 2);

        $table->description = trim(array_pop($descriptions));
        $table->introtext   = trim(array_pop($descriptions));

        parent::prepareTable($table);
    }

    /**
     * @inheritDoc
     */
    public function save($data)
    {
        try {
            if (empty($data['image'])) {
                $data['image'] = ltrim(Course::getImage(), '/');
            }
            $success = parent::save($data);

            // Handle additional update tasks
            $courseId = (int)$this->getState($this->getName() . '.id');
            $pathways = empty($data['pathways']) ? [] : $data['pathways'];
            $tags     = empty($data['tags']) ? [] : $data['tags'];
            $ordering = empty($data['lessons']) ? [] : $data['lessons'];

            $this->updatePathways($courseId, $pathways);
            $this->updateJunctionTable('#__oscampus_courses_tags.courses_id', $courseId, 'tags_id', $tags);
            $this->setLessonOrder($ordering);
            $this->updateFiles($courseId, $data);

        } catch (Throwable $e) {
            $this->setError($e->getMessage());
            $success = false;
        }

        return $success;
    }

    /**
     * Special handling for pathways junction due to ordering being stored there
     *
     * @param ?int  $courseId
     * @param int[] $pathways
     *
     * @return void
     * @throws Exception
     */
    protected function updatePathways(?int $courseId, array $pathways)
    {
        $courseId = (int)$courseId;

        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__oscampus_courses_pathways')
            ->where('courses_id = ' . $courseId);

        $oldPathways = $db->setQuery($query)->loadObjectList('pathways_id');
        $newPathways = array_flip($pathways);

        // Remove from unselected pathways
        if ($removePathways = array_diff_key($oldPathways, $newPathways)) {
            $query = $db->getQuery(true)
                ->delete('#__oscampus_courses_pathways')
                ->where([
                    'courses_id = ' . $courseId,
                    'pathways_id IN (' . join(',', array_keys($removePathways)) . ')'
                ]);
            $db->setQuery($query)->execute();
        }

        // Add to new pathways at bottom of list
        if ($addPathways = array_diff_key($newPathways, $oldPathways)) {
            $query = $db->getQuery(true)
                ->select('pathway.id, max(cp.ordering) AS lastOrder')
                ->from('#__oscampus_pathways AS pathway')
                ->leftJoin('#__oscampus_courses_pathways AS cp ON cp.pathways_id = pathway.id')
                ->where('pathway.id IN (' . join(',', array_keys($addPathways)) . ')')
                ->group('pathway.id');

            // Find last ordering # and verify pathway exists
            $ordering = $db->setQuery($query)->loadObjectList('id');

            $insertValues = [];
            foreach ($addPathways as $pid => $null) {
                if (isset($ordering[$pid])) {
                    $insertValues[] = join(',', [
                        $courseId,
                        (int)$pid,
                        (int)$ordering[$pid]->lastOrder + 1
                    ]);
                } else {
                    throw new Exception(Text::sprintf('COM_OSCAMPUS_ERROR_MISSING_ADD_PATHWAY', $pid));
                }
            }

            $query = $db->getQuery(true)
                ->insert('#__oscampus_courses_pathways')
                ->columns(['courses_id', 'pathways_id', 'ordering'])
                ->values($insertValues);

            $db->setQuery($query)->execute();
        }
    }

    /**
     * Process the attached files
     *
     * @param ?int  $courseId
     * @param array $data
     *
     * @return void
     * @throws Exception
     */
    protected function updateFiles(?int $courseId, array $data)
    {
        $app = OscampusFactory::getApplication();
        $db  = OscampusFactory::getDbo();

        $fileFields = $app->input->files->get('jform', [], 'raw');
        $uploads    = empty($fileFields['files']['upload']) ? [] : $fileFields['files']['upload'];
        $files      = $this->collectFiles($courseId, $data);

        // Load all currently attached files
        $query      = $db->getQuery(true)
            ->select('id')
            ->from('#__oscampus_files')
            ->where('courses_id = ' . $courseId);
        $deleteList = $db->setQuery($query)->loadColumn();

        foreach ($files as $index => $file) {
            $deleteIndex = array_search($file->id, $deleteList);
            if ($deleteIndex !== false) {
                unset($deleteList[$deleteIndex]);
            }

            // Check for new uploaded files
            if (!empty($uploads[$index]['name'])) {
                $upload = $uploads[$index];

                $path = Course::getFilePath($upload['name']);
                // @TODO: allowing all unsafe files. Consider reviewing for more control
                if (!File::upload($upload['tmp_name'], JPATH_SITE . '/' . $path, false, true)) {
                    throw new Exception(Text::sprintf('COM_OSCAMPUS_ERROR_COURSE_FILE_UPLOAD', $path));
                }

                $file->path = $path;
            }
            if ($file->title && $file->path) {
                // Update the file table
                $table = OscampusTable::getInstance('Files');

                $table->setProperties($file);
                if (!$table->store()) {
                    die($table->getError());
                }

            } elseif ($file->id) {
                $message = [];
                if (!$file->title) {
                    $message[] = Text::_('COM_OSCAMPUS_ERROR_COURSE_FILE_TITLE_REQUIRED');
                }
                if (!$file->path) {
                    $message[] = Text::_('COM_OSCAMPUS_ERROR_COURSE_FILE_PATH_REQUIRED');
                }
                throw new Exception(join(', ', $message));
            }
        }

        // Delete any files not referenced
        if ($deleteList) {
            $deleteQuery = $db->getQuery(true)
                ->delete('#__oscampus_files')
                ->where(sprintf('id IN (%s)', join(',', $deleteList)));

            $db->setQuery($deleteQuery)->execute();
        }
    }

    /**
     * Gather the file inputs into an easier structure for processing
     *
     * @param int   $courseId
     * @param array $data
     *
     * @return object[]
     */
    protected function collectFiles(int $courseId, array $data): array
    {
        $files = [];
        if ($rawFiles = empty($data['files']) ? [] : $data['files']) {
            foreach ($rawFiles['id'] as $index => $fileId) {
                $file = (object)[
                    'courses_id'  => $courseId,
                    'lessons_id'  => (int)$rawFiles['lessons_id'][$index],
                    'title'       => $rawFiles['title'][$index],
                    'description' => $rawFiles['description'][$index],
                    'path'        => $rawFiles['path'][$index],
                    'ordering'    => (int)$index + 1
                ];

                if ($fileId) {
                    $file->id = (int)$fileId;
                }

                $files[] = $file;
            }
        }

        return $files;
    }

    /**
     * Do a forced/manual ordering update for all modules and lessons.
     * We are trusting that all ids are appropriate to this course.
     *
     * @param array $ordering
     *
     * @return void
     * @throws Exception
     */
    protected function setLessonOrder(array $ordering)
    {
        $db = $this->getDbo();

        $moduleOrder = 1;
        foreach ($ordering as $moduleId => $lessons) {
            $setModule = (object)[
                'id'       => $moduleId,
                'ordering' => $moduleOrder++
            ];
            $db->updateObject('#__oscampus_modules', $setModule, ['id']);

            foreach ($lessons as $lessonOrder => $lessonId) {
                $set = (object)[
                    'id'       => $lessonId,
                    'ordering' => $lessonOrder + 1
                ];
                $db->updateObject('#__oscampus_lessons', $set, ['id']);
            }
        }
    }
}

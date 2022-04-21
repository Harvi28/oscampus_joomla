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

defined('_JEXEC') or die();

class OscampusRoute
{
    const SLUG_PATHWAY = 'pathways';
    const SLUG_COURSE  = 'courses';
    const SLUG_LESSON  = 'lessons';

    /**
     * @var static
     */
    protected static $instance = null;

    /**
     * @var array
     */
    protected $items = null;

    /**
     * @var array
     */
    protected $articleItems = null;

    protected $idVars = array(
        'pathway' => 'pid'
    );

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Get a raw url for the selected view,layout
     *
     * @param string $view
     * @param string $layout
     * @param int    $id
     *
     * @return string
     * @throws Exception
     */
    public function get($view, $layout = null, $id = null)
    {
        if ($query = $this->getQuery($view, $layout, $id)) {
            return 'index.php?' . http_build_query($query);
        }
        return null;
    }

    /**
     * Find an OSCampus menu item to use as a base for the selected view/layout
     *
     * @param string $view
     * @param string $layout
     * @param int    $id
     *
     * @return object
     * @throws Exception
     */
    public function getMenu($view, $layout = null, $id = null)
    {
        // Use active menu if it matches what we're looking for
        if ($activeMenu = OscampusFactory::getApplication()->getMenu()->getActive()) {
            $activeQuery = $activeMenu->query;
            if ($this->matchesQuery($activeQuery, $view, $layout, $id)) {
                return $activeMenu;
            }
        }

        if ($this->items === null) {
            $menu        = JMenu::getInstance('site');
            $this->items = $menu->getItems(array('component', 'access'), array('com_oscampus', true));
        }

        $user       = OscampusFactory::getUser();
        $isRoot     = $user->authorise('core.manager');
        $viewLevels = $user->getAuthorisedViewLevels();

        $default = null;
        foreach ($this->items as $item) {
            $menuAccess = $isRoot || in_array($item->access, $viewLevels);

            if ($menuAccess) {
                if ($this->matchesQuery($item->query, $view, $layout, $id)) {
                    // Found an exact match
                    return $item;

                } elseif ($view == 'pathway' && $this->matchesQuery($item->query, 'pathways')) {
                    // The pathways view can be used as a base for pathway view
                    $default = $item;
                }
            }
        }

        return $default;
    }

    /**
     * Build the correct link to a com_content article
     *
     * @param $articleId
     *
     * @return string
     * @throws Exception
     */
    public function fromArticleId($articleId)
    {
        if ($this->articleItems === null) {
            $contentId          = OscampusComponentHelper::getComponent('com_content')->id;
            $this->articleItems = JMenu::getInstance('site')->getItems('component_id', $contentId);
        }

        $link = 'index.php?option=com_content&view=article&id=' . $articleId;
        foreach ($this->articleItems as $item) {
            list(, $query) = explode('?', $item->link);
            parse_str($query, $query);

            if (!empty($query['article']) && !empty($query['id']) && $query['id'] == $articleId) {
                $link = $item->link;
            }
        }

        return $link;
    }

    /**
     * Get the query array for the selected view/layout
     *
     * @param string $view
     * @param string $layout
     * @param int    $id
     *
     * @return array
     * @throws Exception
     */
    public function getQuery($view, $layout = null, $id = null)
    {
        $query = array(
            'option' => 'com_oscampus',
            'view'   => $view
        );
        if ($layout) {
            $query['layout'] = $layout;
        }

        if ($id && isset($this->idVars[$view])) {
            $idKey         = $this->idVars[$view];
            $query[$idKey] = $id;
        }

        if ($menuItem = $this->getMenu($view, $layout, $id)) {
            $query['Itemid'] = $menuItem->id;

            $menuView = empty($menuItem->query['view']) ? '' : $menuItem->query['view'];
            if ($menuView == $view) {
                unset($query['view']);
            }

            $menuLayout = empty($menuItem->query['layout']) ? '' : $menuItem->query['layout'];
            if ($layout && $menuLayout == $layout) {
                unset($query['layout']);
            }

            if ($id && !empty($idKey)) {
                $menuId = empty($menuItem->query[$idKey]) ? null : $menuItem->query[$idKey];
                if ($menuId == $id) {
                    unset($query[$idKey]);
                }
            }
        }

        return $query;
    }

    /**
     * Wrapper function for $this->getSlug()
     *
     * @param int $id
     *
     * @return string
     * @throws Exception
     */
    public function getPathwaySlug($id)
    {
        return $this->getSlug(static::SLUG_PATHWAY, $id);
    }

    /**
     * Wrapper function for $this->getSlug()
     *
     * @param int $id
     *
     * @return string
     * @throws Exception
     */
    public function getCourseSlug($id)
    {
        return $this->getSlug(static::SLUG_COURSE, $id);
    }

    /**
     * @param int $id    The lesson ID or course ID
     * @param int $index If supplied, the index within a course. If not, $id is a lesson id
     *
     * @return string
     * @throws Exception
     */
    public function getLessonSlug($id, $index = null)
    {
        $db    = OscampusFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('lesson.alias')
            ->from('#__oscampus_lessons AS lesson');

        if ($index === null || $index < 0) {
            // Nice! we have the lesson id!
            $query->where('lesson.id = ' . (int)$id);
            $index = 0;

        } else {
            // A little more work, find by index
            $query
                ->innerJoin('#__oscampus_modules AS module ON module.id = lesson.modules_id')
                ->innerJoin('#__oscampus_courses AS course ON course.id = module.courses_id')
                ->where('course.id = ' . (int)$id)
                ->order('module.ordering ASC, lesson.ordering ASC');
        }

        if ($alias = $db->setQuery($query, $index, 1)->loadResult()) {
            return $alias;
        }

        $index = func_num_args() > 1 ? func_get_arg(1) : null;
        if ($index === null || $index < 0) {
            $identifier = $id;
        } else {
            $identifier = $id . '/' . $index;
        }
        throw new Exception(__CLASS__ . ": not found - Lesson={$identifier}", 404);
    }

    /**
     * Find the url slug for selected item type.
     *
     * @param string $type  See static::SLUG_* constants
     * @param int    $id    The id of the target item
     * @param int    $index For lessons, the zero-based index of the lesson as ordered in the course.
     *                      If this argument is passed, $id will be assumed to be a course ID
     *
     * @return string
     * @throws Exception
     */
    protected function getSlug($type, $id, $index = null)
    {
        if ($type == static::SLUG_LESSON) {
            // This usage is a bit silly, but just in case!
            return $this->getLessonSlug($id, $index);
        }

        $db    = OscampusFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('alias')
            ->from('#__oscampus_' . $type)
            ->where('id = ' . (int)$id);

        if ($alias = $db->setQuery($query)->loadResult()) {
            return $alias;
        }

        throw new Exception(__CLASS__ . ": not found - {$type}={$id}", 404);
    }

    /**
     * Get the pathway id from an alias slug
     *
     * @param string $slug
     *
     * @return int
     */
    public function getPathwayFromSlug($slug)
    {
        $alias = $this->slugToAlias($slug);

        $db    = OscampusFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__oscampus_pathways')
            ->where('alias = ' . $db->quote($alias));

        $id = $db->setQuery($query)->loadResult();

        return (int)$id;
    }

    /**
     * Get course ID from a category alias slug
     *
     * @param string $slug
     *
     * @return int
     */
    public function getCourseFromSlug($slug)
    {
        $alias = $this->slugToAlias($slug);

        $db    = OscampusFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__oscampus_courses')
            ->where('alias = ' . $db->quote($alias));

        $id = $db->setQuery($query)->loadResult();

        return (int)$id;
    }

    /**
     * Get the lesson index within the requested course
     *
     * @param string $slug
     * @param int    $courseId
     *
     * @return int
     */
    public function getLessonFromSlug($slug, $courseId)
    {
        $alias = $this->slugToAlias($slug);

        $db    = OscampusFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('lesson.id')
            ->from('#__oscampus_lessons AS lesson')
            ->innerJoin('#__oscampus_modules AS module ON module.id = lesson.modules_id')
            ->where(
                array(
                    'module.courses_id = ' . (int)$courseId,
                    'lesson.alias = ' . $db->q($alias)
                )
            );

        $id = $db->setQuery($query)->loadResult();
        if (!$id) {
            // Default to the first lesson
            $query->clear('where')->where('module.courses_id = ' . (int)$courseId);
            $id = $db->setQuery($query, 0, 1)->loadResult();
        }

        return $id;
    }

    /**
     * Joomla converts the first dash in a url path segment to a colon, expecting
     * it to be an id from a database table. We need to reconvert to a dash so we
     * can find the alias we're using instead
     *
     * @param string $slug
     *
     * @return string
     */
    protected function slugToAlias($slug)
    {
        return str_replace(':', '-', $slug);
    }

    protected function matchesQuery(array $query, $view, $layout = null, $id = null)
    {
        $testVars = array_merge(
            array(
                'option' => '',
                'view'   => '',
                'layout' => 'default'
            ),
            $query
        );
        $layout   = $layout ?: 'default';

        if ($testVars['option'] == 'com_oscampus'
            && $testVars['view'] == $view
            && $testVars['layout'] == $layout
        ) {
            if ($id && in_array($view, array_keys($this->idVars))) {
                $idKey = $this->idVars[$view];

                return ($testVars[$idKey] == $id);
            }

            return true;
        }

        return false;
    }
}

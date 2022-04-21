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

use Joomla\CMS\Menu\MenuItem;

defined('_JEXEC') or die();

/**
 * @TODO: Convert to new router classes
 */

if (!defined('OSCAMPUS_LOADED')) {
    require_once JPATH_ADMINISTRATOR . '/components/com_oscampus/include.php';
}

/**
 * Class OscampusRouter
 *
 * @TODO: Update to more integrated use of new 3.x router system
 */
class OscampusRouter extends JComponentRouterBase
{
    /**
     * @var OscampusRoute
     */
    protected $route = null;

    /**
     * @var MenuItem
     */
    protected $targetMenu = null;

    public function __construct(JApplicationCms $app = null, JMenu $menu = null)
    {
        parent::__construct($app, $menu);

        $this->route = OscampusRoute::getInstance();
    }

    /**
     * To avoid the possibility of stacking inappropriate views on to menu items
     * we have to remove the Itemid ahead of time. Otherwise there are situations
     * when the Itemid gets added when there shouldn't be one at all
     *
     * @param array $query
     *
     * @return array
     * @throws Exception
     */
    public function preprocess($query)
    {
        $app = JFactory::getApplication();

        if (!empty($query['Itemid']) && $app::getRouter()->getMode() == JROUTER_MODE_SEF) {
            $this->targetMenu = $this->menu->getItem($query['Itemid']);
            unset($query['Itemid']);
        }

        return parent::preprocess($query);
    }

    /**
     * @param array $query
     *
     * @return string[]
     * @throws Exception
     */
    public function build(&$query)
    {
        $segments = array();

        // Unset pointless limitstart=0
        if (isset($query['limitstart']) && $query['limitstart'] == 0) {
            unset($query['limitstart']);
        }

        if (!empty($query['view'])) {
            $view = $query['view'];
            unset($query['view']);

        } elseif ($this->targetMenu && $this->targetMenu->component == 'com_oscampus') {
            $view            = $this->targetMenu->query['view'];
            $query['Itemid'] = $this->targetMenu->id;
        }

        if (!empty($view)) {
            switch ($view) {
                case 'certificate':
                    // Certificates should be rooted on 'mycertificates' view
                    $menuQuery = $this->route->getQuery('mycertificates');

                    $id = isset($query['id']) ? (int)$query['id'] : null;
                    if ($id) {
                        unset($query['id']);
                        $segments[] = $id;
                    }

                    if (isset($query['format'])) {
                        unset($query['format']);
                    }

                    if (empty($query['Itemid'])
                        || (!empty($menuQuery['Itemid']) && $query['Itemid'] != $menuQuery['Itemid'])
                    ) {
                        if (!empty($menuQuery['Itemid'])) {
                            $query['Itemid'] = $menuQuery['Itemid'];
                        } else {
                            $segments[] = 'certificate';
                        }
                    }
                    break;

                case 'lesson':
                case 'course':
                    $courseId    = isset($query['cid']) ? (int)$query['cid'] : null;
                    $lessonId    = isset($query['lid']) ? (int)$query['lid'] : null;
                    $lessonIndex = isset($query['index']) ? (int)$query['index'] : null;

                    $courseQuery = $this->route->getQuery('course');
                    if (!empty($courseQuery['Itemid'])) {
                        $query['Itemid'] = $courseQuery['Itemid'];
                    } else {
                        $segments[] = 'course';

                        $pathwaysQuery = $this->route->getQuery('pathways');
                        if (!empty($pathwaysQuery['Itemid'])) {
                            $query['Itemid'] = $pathwaysQuery['Itemid'];
                        }
                    }

                    if ($courseId && ($course = $this->route->getCourseSlug($courseId))) {
                        $segments[] = $course;
                        unset($query['cid']);

                        if ($view == 'lesson') {
                            try {
                                if ($lessonId) {
                                    $lesson = $this->route->getLessonSlug($lessonId);
                                    unset($query['lid']);
                                    if (isset($query['index'])) {
                                        unset($query['index']);
                                    }

                                } elseif ($courseId) {
                                    $lesson = $this->route->getLessonSlug($courseId, $lessonIndex);
                                    unset($query['index']);
                                    if (isset($query['lid'])) {
                                        unset($query['lid']);
                                    }
                                }
                                if (!empty($lesson)) {
                                    $segments[] = $lesson;
                                }

                            } catch (Throwable $e) {
                                // The selected lesson probably doesn't exist. This will take us to the course homepage
                                if (isset($query['index'])) {
                                    unset($query['index']);
                                }
                                if (isset($query['lid'])) {
                                    unset($query['lid']);
                                }
                            }
                        }
                    }
                    break;

                case 'pathways':
                case 'pathway':
                    $targetQuery   = $this->targetMenu ? $this->targetMenu->query : array();
                    $layout        = $this->getvalueFromArrays('layout', $query, $targetQuery);
                    $pathwayId     = $this->getvalueFromArrays('pid', $query, $targetQuery);
                    $pathwaysQuery = $this->route->getQuery($view, $layout, $pathwayId);

                    if (!empty($pathwaysQuery['Itemid'])) {
                        $query['Itemid'] = $pathwaysQuery['Itemid'];
                    }

                    if (empty($query['Itemid'])) {
                        $segments[] = $view;

                    } else {
                        $query = array_merge($query, $pathwaysQuery);
                    }
                    if (isset($query['view'])) {
                        unset($query['view']);
                    }

                    if ($pathwayId
                        && (!$targetQuery || $targetQuery['view'] != 'pathway')
                        && ($pathway = $this->route->getPathwaySlug($pathwayId))
                    ) {
                        $segments[] = $pathway;
                        if (isset($query['pid'])) {
                            unset($query['pid']);
                        }
                    }

                    break;

                default:
                    $query = array_merge($query, $this->route->getQuery($view));
                    if (!empty($query['view'])) {
                        $segments[] = $view;
                        unset($query['view']);
                    }
                    break;
            }
        }

        return $segments;
    }

    /**
     * @param string[] $segments
     *
     * @return array
     * @throws Exception
     */
    public function parse(&$segments)
    {
        $app  = OscampusFactory::getApplication();
        $menu = $app->getMenu()->getActive();

        $view = $app->input->getCmd('view');
        if (!$view && !empty($menu) && $menu->component == 'com_oscampus') {
            $view = $menu->query['view'];
        }

        $vars = array(
            'option' => 'com_oscampus'
        );

        if (!empty($segments[0])) {
            if (in_array($segments[0], array('certificate', 'course', 'pathway', 'search'))) {
                $view = array_shift($segments);
            }

            if (!empty($view)) {
                $itemSlug = empty($segments[0]) ? null : $segments[0];

                switch ($view) {
                    case 'mycertificates':
                        $vars['view'] = 'certificate';

                        $vars['id']     = (int)$itemSlug;
                        $vars['format'] = 'raw';
                        break;

                    case 'course':
                        $vars['cid'] = $this->route->getCourseFromSlug($itemSlug);
                        if (empty($segments[1])) {
                            $vars['view'] = 'course';
                        } else {
                            $vars['lid']  = $this->route->getLessonFromSlug($segments[1], $vars['cid']);
                            $vars['view'] = 'lesson';
                        }
                        break;

                    case 'pathway':
                    case 'pathways':
                        $vars['view'] = 'pathway';
                        $vars['pid']  = $this->route->getPathwayFromSlug($itemSlug);
                        break;

                    case 'search':
                        $vars['view'] = 'search';
                        break;

                    default:
                        $vars['view'] = $itemSlug;
                }
            }
        }

        return $vars;
    }

    /**
     * Returns the first found value in a sequence of arrays
     *
     * @param int   $key
     * @param array ...$arrays
     *
     * @return mixed
     */
    protected function getvalueFromArrays($key, array ...$arrays)
    {
        foreach ($arrays as $array) {
            if (is_array($array)) {
                if (!empty($array[$key])) {
                    return $array[$key];
                }
            }
        }

        return null;
    }
}

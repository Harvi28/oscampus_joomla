<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2021 Joomlashack.com. All rights reserved
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

defined('_JEXEC') or die();

class PlgSearchOscampus extends JPlugin
{
    protected $areas = [
        'oscampus' => 'PLG_SEARCH_OSCAMPUS_OSCAMPUS'
    ];

    public function __construct($subject, array $config)
    {
        parent::__construct($subject, $config);

        $this->loadLanguage();
    }

    public function onContentSearchAreas()
    {
        return $this->areas;
    }

    /**
     *
     * Return objects should be:
     * {
     *    href       : {string}
     *    title      : {string}
     *    section    : {string}
     *    created    : {string}
     *    text       : {string}
     *    browsernav : {0|1}
     * }
     *
     * @param string $text       Target search string.
     * @param string $phrase     Matching option (possible values: exact|any|all).
     *                           Default is 'any'
     * @param string $ordering   Ordering option (possible values: newest|oldest|popular|alpha|category).
     *                           Default is 'newest'
     * @param mixed  $areas      An array if the search it to be restricted to areas or null to search all areas.
     *
     * @return  object[]  Search results.
     */

    public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null)
    {
        if (!$this->oscampusInstalled()) {
            return [];
        }

        // See if we've been selected for searching
        if (is_array($areas)) {
            if (!array_intersect($areas, array_keys($this->onContentSearchAreas()))) {
                return [];
            }
        }

        $db = OscampusFactory::getDbo();

        $lessonQuery = $db->getQuery(true)
            ->select([
                'module.courses_id',
                'lesson.id AS lessons_id',
                'lesson.title',
                'lesson.type AS section',
                'lesson.created',
                'course.introtext',
                'course.description'
            ])
            ->from('#__oscampus_lessons AS lesson')
            ->innerJoin('#__oscampus_modules AS module ON module.id = lesson.modules_id')
            ->innerJoin('#__oscampus_courses AS course ON course.id = module.courses_id')
            ->where([
                'lesson.published = 1',
                'course.published = 1',
                'course.publish_up <= UTC_TIMESTAMP()',
                'IFNULL(course.publish_down, UTC_TIMESTAMP()) >= UTC_TIMESTAMP()',
                $this->getSearchAtom($text, $phrase, ['lesson.title', 'lesson.description'])
            ])
            ->group('lesson.id');


        $courseQuery = $db->getQuery(true)
            ->select([
                'course.id',
                '0',
                'course.title',
                $db->quote('course'),
                'course.created',
                'course.introtext',
                'course.description'
            ])
            ->from('#__oscampus_courses AS course')
            ->where([
                'course.published = 1',
                'course.publish_up <= UTC_TIMESTAMP()',
                'IFNULL(course.publish_down, UTC_TIMESTAMP()) >= UTC_TIMESTAMP()',
                $this->getSearchAtom(
                    $text,
                    $phrase,
                    ['course.title', 'course.introtext', 'course.description']
                )
            ])
            ->group('course.id');

        $query = sprintf('(%s) UNION (%s)', $lessonQuery, $courseQuery);

        switch (strtolower($ordering)) {
            case 'alpha':
                $order = 'title ASC';
                break;

            case 'newest':
                $order = 'created DESC';
                break;

            case 'oldest':
                $order = 'created ASC';
                break;

            case 'popular':
            case 'category':
            default:
                $order = 'section ASC';
                break;
        }
        $query .= ' ORDER BY ' . $order;

        $limit = $this->params->get('searchLimit', 50);
        $items = $db->setQuery($query, 0, $limit)->loadObjectList();

        $language = JFactory::getLanguage();
        foreach ($items as $item) {
            if ($item->lessons_id > 0) {
                $item->href = JHtml::_(
                    'osc.link.lessonid',
                    $item->courses_id,
                    $item->lessons_id,
                    null,
                    null,
                    true
                );
            } else {
                $item->href = JHtml::_(
                    'osc.link.course',
                    $item->courses_id,
                    null,
                    null,
                    true
                );
            }

            $title = 'PLG_SEARCH_OSCAMPUS_SECTION_' . $item->section;
            if ($language->hasKey($title)) {
                $item->title = Text::sprintf($title, $item->title);
            }

            $item->section    = Text::_('PLG_SEARCH_OSCAMPUS_OSCAMPUS');
            $item->text       = ($item->introtext ?: $item->description);
            $item->browsernav = 0;

            unset($item->introtext, $item->description);
        }

        return $items;
    }

    /**
     * Create a standard subclause for where to search for the selected text
     *
     * @param string       $text   Target search string
     * @param string       $phrase (any|all|exact)
     * @param string|array $fields The field names to search in
     *
     * @return string
     */
    protected function getSearchAtom($text, $phrase, $fields)
    {
        if (!is_array($fields)) {
            $fields = (array)$fields;
        }

        $db     = OscampusFactory::getDbo();
        $phrase = strtolower($phrase) ?: 'any';
        $glue   = $phrase == 'all' ? 'AND' : 'OR';

        if ($phrase == 'exact') {
            $texts = array($text);
        } else {
            $texts = preg_split('/\W+/', $text);
        }
        foreach ($texts as $idx => $text) {
            $texts[$idx] = $db->quote('%' . $text . '%');
        }

        $mainClause = [];
        foreach ($fields as $field) {
            $subClause = [];

            foreach ($texts as $text) {
                $subClause[] = $field . ' like ' . $text;
            }

            if (count($subClause) > 1) {
                $mainClause[] = '(' . join(') ' . $glue . '(', $subClause) . ')';
            } else {
                $mainClause[] = array_pop($subClause);
            }
        }

        if (count($mainClause) > 1) {
            $where = '((' . join(') OR (', $mainClause) . '))';
        } else {
            $where = array_pop($mainClause);
        }

        return $where;
    }

    /**
     * Verify loading of OSCampus
     *
     * @return bool
     */
    protected function oscampusInstalled()
    {
        if (!defined('OSCAMPUS_LOADED') || !OSCAMPUS_LOADED) {
            $path = JPATH_ADMINISTRATOR . '/components/com_oscampus/include.php';
            if (!is_file($path)) {
                return false;
            }

            require_once $path;
        }

        return true;
    }
}

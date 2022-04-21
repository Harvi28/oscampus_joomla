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

use Joomla\CMS\MVC\Model\ListModel;

defined('_JEXEC') or die();

abstract class OscampusModelList extends ListModel implements JObservableInterface
{
    use OscampusModelTrait;
    use OscampusTraitObservable;

    /**
     * OscampusModel constructor.
     *
     * @param array $config
     *
     * @return void
     * @throws Exception
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->attachAllObservers($this);
    }

    protected function _getList($query, $limitstart = 0, $limit = 0)
    {
        $items = parent::_getList($query, $limitstart, $limit);

        $this->oscampusTrigger('oscampusAfterGetList', [$this->context, $this, &$items]);

        return $items;
    }

    /**
     * Create a where clause of OR conditions for a text search
     * across one or more fields. Optionally accepts a text
     * search like 'id: #' if $idField is specified
     *
     * @param string          $text
     * @param string|string[] $fields
     * @param ?string         $idField
     *
     * @return string
     */
    public function whereTextSearch(string $text, $fields, ?string $idField = null): string
    {
        $text = trim($text);

        if ($idField && stripos($text, 'id:') === 0) {
            $id = (int)substr($text, 3);
            return $idField . ' = ' . $id;
        }

        if (is_string($fields)) {
            $fields = [$fields];
        }
        $searchText = $this->getDbo()->quote('%' . $text . '%');

        $ors = [];
        foreach ($fields as $field) {
            $ors[] = $field . ' LIKE ' . $searchText;
        }

        if (count($ors) > 1) {
            return sprintf('(%s)', join(' OR ', $ors));
        }

        return array_pop($ors);
    }
}

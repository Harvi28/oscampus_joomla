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

namespace Oscampus\Statistics;

use Exception;
use JDatabaseDriver;
use Joomla\CMS\Language\Text;
use JText;
use Oscampus\AbstractBase;
use Oscampus\Statistics\Query\AbstractQueryBase;
use OscampusModel;
use OscampusModelList;

defined('_JEXEC') or die();

class Manager extends AbstractBase
{
    protected $daynumber = array(
        'Sunday'    => 1,
        'Monday'    => 2,
        'Tuesday'   => 3,
        'Wednesday' => 4,
        'Thursday'  => 5,
        'Friday'    => 6,
        'Saturday'  => 7
    );

    /**
     * @param string                          $queryName
     * @param OscampusModel|OscampusModelList $model
     *
     * @return AbstractQueryBase
     * @throws Exception
     */
    public function getQuery(string $queryName, $model = null): AbstractQueryBase
    {
        if (strpos($queryName, '.') !== false) {
            $parts = explode('.', $queryName);

        } elseif (strpos($queryName, '\\') === false) {
            $parts = preg_split('/(?<=[a-z])(?=[A-Z])/x', $queryName);

        } else {
            $parts = [$queryName];
        }

        $parts = array_map(
            function ($row) {
                return ucfirst(strtolower($row));
            },
            $parts
        );

        $className = '\\Oscampus\\Statistics\\Query\\' . join('\\', $parts);

        if (class_exists($className)) {
            $queryClass = new $className($this, $model);

            return $queryClass;
        }

        throw new Exception(Text::sprintf('COM_OSCAMPUS_ERROR_QUERY_CLASS_NOT_FOUND', $className));
    }

    /**
     * @return JDatabaseDriver
     */
    public function getDbo()
    {
        return $this->dbo;
    }
}

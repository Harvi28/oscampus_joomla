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

use Oscampus\Activity\CourseStatus;
use Oscampus\Activity\LessonStatus;
use Oscampus\Activity\LessonSummary;
use Oscampus\Font\Manager;
use Oscampus\Lesson\Download;
use Oscampus\Lesson\Properties;
use OscampusComponentHelper;
use OscampusFactory;
use Pimple\Container AS Pimple;
use Pimple\ServiceProviderInterface;

defined('_JEXEC') or die();

/**
 * Class Services
 *
 * @package Oscampus
 */
class Services implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Pimple $pimple A container instance
     */
    public function register(Pimple $pimple)
    {
        /* Start Services */
        $pimple['application'] = function () {
            return OscampusFactory::getApplication();
        };

        $pimple['dbo'] = function () {
            return OscampusFactory::getDbo();
        };

        $pimple['statistics'] = function (Container $c) {
            return new Statistics\Manager($c['dbo']);
        };

        $pimple['params'] = function () {
            return OscampusComponentHelper::getParams('com_oscampus');
        };

        $pimple['component'] = function () {
            return OscampusComponentHelper::getComponent('com_oscampus');
        };

        $pimple['theme'] = function (Container $c) {
            return new Theme($c['params']);
        };

        $pimple['fonts'] = function (Container $c) {
            return new Manager();
        };

        /* End Services */

        /* Start Factory Services */
        $pimple['user'] = $pimple->factory(
            function (Container $c) {
                return clone OscampusFactory::getUser();
            }
        );

        $pimple['lesson'] = $pimple->factory(
            function (Container $c) {
                $properties = new Properties();

                return new Lesson($c['dbo'], $properties, $c['params']);
            }
        );

        $pimple['activity'] = $pimple->factory(
            function (Container $c) {
                $lessonStatus  = new LessonStatus();
                $lessonSummary = new LessonSummary();
                $courseStatus  = new CourseStatus();
                return new UserActivity(
                    $c['dbo'],
                    $c['user'],
                    $lessonStatus,
                    $lessonSummary,
                    $courseStatus,
                    $c['certificate']
                );
            }
        );

        $pimple['course'] = $pimple->factory(
            function (Container $c) {
                return new Course($c['dbo']);
            }
        );

        $pimple['certificate'] = $pimple->factory(
            function (Container $c) {
                return new Certificate($c['dbo'], $c['application'], $c['fonts'], $c['params']);
            }
        );

        $pimple['download'] = $pimple->factory(
            function (Container $c) {
                return new Download($c['dbo']);
            }
        );

        /* End Factory Services */
    }
}

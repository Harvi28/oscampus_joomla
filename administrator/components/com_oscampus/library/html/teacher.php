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

abstract class OscTeacher
{
    protected static $linkIcons = [
        'default'  => '<i class="fa fa-link"></i>',
        'twitter'  => '<i class="fa fa-twitter"></i>',
        'facebook' => '<i class="fa fa-facebook"></i>',
        'linkedin' => '<i class="fa fa-linkedin-square"></i>',
        'blog'     => '<i class="fa fa-pencil"></i>',
        'email'    => '<i class="fa fa-envelope"></i>',
        'website'  => '<i class="fa fa-globe"></i>'
    ];

    /**
     * Generate links to known urls for a teacher
     *
     * @param object $teacher
     *
     * @return string
     */
    public static function links($teacher)
    {
        $html = array();

        if (!empty($teacher->links)) {
            foreach ($teacher->links as $type => $value) {
                if ($type == 'email' && !$value->link && !empty($teacher->email)) {
                    $value->link = $teacher->email;
                }
                if ($link = static::createLink($type, $value->link)) {
                    $type    = isset(static::$linkIcons[$type]) ? $type : 'default';
                    $attribs = preg_match('#^https?://#', $link) ? 'target="_blank"' : '';

                    $html[] = '<span class="osc-teacher-' . $type . '">';
                    $html[] = static::$linkIcons[$type];
                    $html[] = JHtml::_(
                        'link',
                        $link,
                        JText::_('COM_OSCAMPUS_TEACHER_LINK_' . $type),
                        $attribs
                    );
                    $html[] = '</span>';
                }
            }

            if ($html) {
                array_unshift($html, '<div class="osc-teacher-links">');
                $html[] = '</div>';
            }
        }

        return join("\n", $html);
    }

    /**
     * Normalize links based on type
     *
     * @param string $type
     * @param string $link
     *
     * @return string
     */
    protected static function createLink($type, $link)
    {
        if ($link) {
            switch ($type) {
                case 'twitter':
                    $link = 'https://www.twitter.com/' . $link;
                    break;

                case 'facebook':
                    $link = 'https://www.facebook.com/' . $link;
                    break;

                case 'linkedin':
                    $link ='https://www.linkedin.com/in/' . $link;
                    break;

                case 'email':
                    $link = 'mailto:' . $link;
                    break;
            }

            return $link;
        }

        return null;
    }
}

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

abstract class OscLink
{
    /**
     * Build link to a course from its ID alone
     *
     * @param int    $cid
     * @param string $text
     * @param mixed  $attribs
     * @param bool   $uriOnly
     *
     * @return string
     * @throws Exception
     */
    public static function course($cid, $text, $attribs = null, $uriOnly = false)
    {
        if ((int)$cid) {
            $query = OscampusRoute::getInstance()->getQuery('course');

            $query['view'] = 'course';
            $query['cid']  = (int)$cid;

            $link = 'index.php?' . http_build_query($query);
            if ($uriOnly) {
                return $link;
            }
            return JHtml::_('link', JRoute::_($link), $text, $attribs);
        }
        return '';
    }

    /**
     * Build link to a pathway from its ID alone
     *
     * @param int    $pid
     * @param string $text
     * @param null   $attribs
     * @param bool   $uriOnly
     *
     * @return string
     * @throws Exception
     */
    public static function pathway($pid, $text, $attribs = null, $uriOnly = false)
    {
        if ((int)$pid) {
            $query = OscampusRoute::getInstance()->getQuery('pathways');

            $query['view'] = 'pathway';
            $query['pid']  = (int)$pid;

            $link = 'index.php?' . http_build_query($query);
            if ($uriOnly) {
                return $link;
            }

            return JHtml::_('link', JRoute::_($link), $text, $attribs);
        }
        return '';
    }

    /**
     * Build link to a lesson on course ID/Index alone
     *
     * @param int    $cid
     * @param int    $index
     * @param string $text
     * @param mixed  $attribs
     * @param bool   $uriOnly
     *
     * @return string
     * @throws Exception
     */
    public static function lesson($cid, $index, $text, $attribs = null, $uriOnly = false)
    {
        if ((int)$cid) {
            $query = OscampusRoute::getInstance()->getQuery('course');

            $query['view']  = 'lesson';
            $query['cid']   = (int)$cid;
            $query['index'] = (int)$index;

            $link = 'index.php?' . http_build_query($query);
            if ($uriOnly) {
                return $link;
            }

            return JHtml::_('link', JRoute::_($link), $text, $attribs);
        }

        return '';
    }

    /**
     * Build link to a lesson using lesson ID
     *
     * @param int    $cid
     * @param int    $lid
     * @param string $text
     * @param mixed  $attribs
     * @param bool   $uriOnly
     *
     * @return string
     * @throws Exception
     */
    public static function lessonid($cid, $lid, $text, $attribs = null, $uriOnly = false)
    {
        if ((int)$cid) {
            $query = OscampusRoute::getInstance()->getQuery('course');

            $query['view'] = 'lesson';
            $query['cid']  = (int)$cid;
            $query['lid']  = (int)$lid;

            $link = 'index.php?' . http_build_query($query);
            if ($uriOnly) {
                return $link;
            }

            return JHtml::_('link', JRoute::_($link), $text, $attribs);
        }

        return '';
    }

    /**
     * Create link to a single certificate
     *
     * @param int          $id
     * @param string       $text
     * @param array|string $attribs
     * @param bool         $uriOnly
     * @param bool         $fullUri
     *
     * @return string
     */
    public static function certificate($id, $text = null, $attribs = null, $uriOnly = false, $fullUri = false)
    {
        $text = $text ?: '<i class="fa fa-download"></i> ' . JText::_('COM_OSCAMPUS_DOWNLOAD_CERTIFICATE');

        $query = array(
            'option' => 'com_oscampus',
            'view'   => 'certificate',
            'format' => 'raw',
            'id'     => (int)$id
        );

        $link = JRoute::_('index.php?' . http_build_query($query));

        if ($fullUri) {
            $link = static::absoluteLink($link);
        }

        if ($uriOnly) {
            return $link;
        }

        return JHtml::_('link', $link, $text, $attribs);
    }

    /**
     * Turn a relative url into an absolute url
     *
     * @param string $relativeLink
     *
     * @return string
     */
    protected static function absoluteLink($relativeLink)
    {
        return str_replace('//', '/', JUri::root() . $relativeLink);
    }
}

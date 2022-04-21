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

;(function($) {
    $.Oscampus = $.extend({}, $.Oscampus);

    $.Oscampus.lesson = {
        previous: null,
        current : null,
        next    : null
    };

    /**
     * Method to respond to next/prev navigation buttons.
     *
     * @param {object} lessons
     * @param {object} options
     */
    $.Oscampus.lesson.navigation = function(lessons, options) {
        this.previous = lessons.previous || null;
        this.current = lessons.current || null;
        this.next = lessons.next || null;

        // We DO want the options to be overwritten for use in submodules
        options = $.extend(true, this.navigation.options, options);

        var setLoading = function(message, title) {
            if (!$.Oscampus.isIE11) {
                $(options.container)
                    .addClass('loading')
                    .html('<span class="message">' + message.replace('%s', title) + '</span>');
            }
            document.title = Joomla.JText._('COM_OSCAMPUS_LESSON_LOADING_TITLE', null);
        };

        if (this.next && this.next.title) {
            var next = this.next;
            $(options.buttons.next).on('click', function() {
                if (next.authorised) {
                    setLoading(Joomla.JText._('COM_OSCAMPUS_LESSON_LOADING_NEXT', null), next.title);
                }
            });
        }

        if (this.previous && this.previous.title) {
            var previous = this.previous;
            $(options.buttons.previous).on('click', function() {
                if (previous.authorised) {
                    setLoading(Joomla.JText._('COM_OSCAMPUS_LESSON_LOADING_PREVIOUS', null), previous.title);
                }
            });
        }
    };
    $.Oscampus.lesson.navigation.options = {
        container: '#oscampus.osc-container',
        buttons  : {
            previous: '#prevbut',
            next    : '#nextbut'
        }
    }
})(jQuery);

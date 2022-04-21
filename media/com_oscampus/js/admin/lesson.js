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

    $.Oscampus.admin = $.extend(true, {}, $.Oscampus.admin);

    $.Oscampus.admin.lesson = {};

    /**
     *
     * @param options
     *        container : Selector for main container with all modules/lessons
     *        modules   : Selector for the module parent container
     *        lessons   : Selector for the lesson parent container
     *        reset     : Selector for a reset button
     *        handle    : Selector for cursor handles
     *        cursor    : Cursor to use for dragging animation
     */
    $.Oscampus.admin.lesson.ordering = function(options) {
        options = $.extend(true, {}, this.ordering.options, options);

        var container = $(options.container),
            cache     = container.html(),
            reset     = $(options.reset);

        var setup = function() {
            var modules = container.find(options.modules),
                lessons = container.find(options.lessons),
                targets = modules.add(lessons);

            targets
                .sortable({
                    handle: options.handle,
                    update: function() {
                        reset.show();
                    }
                });
            lessons.children().draggable({
                connectToSortable: lessons,
                refreshPositions : true
            });
            targets.find(options.handle).css('cursor', options.cursor);

            reset.hide();
        };
        setup();

        reset.on('click', function(evt) {
            evt.preventDefault();

            container.html(cache);
            setup();
        });
    };
    $.Oscampus.admin.lesson.ordering.options = {
        container: '#lessons',
        modules  : 'ul.oscampus-module',
        lessons  : 'ul.oscampus-lesson',
        reset    : '.reset-lesson-order',
        handle   : '.handle',
        cursor   : 'move'
    };
})(jQuery);

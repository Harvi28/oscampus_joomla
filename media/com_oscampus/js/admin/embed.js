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

    $.Oscampus.admin.embed = {
        options: {
            urlbase : '',
            selector: '.osc-url-embed-field',
            token   : null
        },

        init: function(options) {
            options = $.extend(true, {}, this.options, options);

            var fields = $(options.selector);

            fields.each(function(i, el) {
                var btn     = $('#' + el.id + '_btn'),
                    preview = $('#' + el.id + '_preview'),
                    target  = $(el);

                // Make url field expandable with string length
                target.css('min-width', target.css('width'));
                target.on('keyup', function() {
                    $(this).width(($(this).val().length * 8) + 'px');
                });
                target.trigger('keyup');

                btn.on('click', function() {
                    if (target.val()) {
                        preview.html('Loading...');

                        var data = {
                            option: 'com_oscampus',
                            task  : 'embed.content',
                            format: 'raw',
                            url   : btoa(target.val())
                        };

                        if (options.token) {
                            data[options.token] = 1;
                        }

                        $.get({
                            url    : options.urlbase + 'index.php',
                            data   : data,
                            success: function(text) {
                                preview.html(text);
                            }
                            ,
                            error  : function(error) {
                                preview.html(error.statusText);
                            }
                        });
                    } else {
                        preview.html('');
                    }
                });

                if (target.val()) {
                    btn.trigger('click');
                } else {
                    preview.html('');
                }
            });
        }
    };
})(jQuery);

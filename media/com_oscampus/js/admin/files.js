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
    $.extend($.fn, {
        pop: function() {
            var top = this.get(-1);
            this.splice(this.length - 1, 1);
            return top;
        }
    });

    $.Oscampus = $.extend({}, $.Oscampus);

    $.Oscampus.admin = $.extend(true, {}, $.Oscampus.admin);

    $.Oscampus.admin.files = {
        options: {
            container: '#file-manager',
            fileBlock: '.osc-file-block',
            button   : {
                delete: '.osc-file-delete',
                add   : '.osc-file-add',
                order : '.osc-file-ordering'
            },
            path     : '.osc-file-path'
        },

        init: function(options) {
            options = $.extend(true, {}, this.options, options);

            var container = $(options.container);

            // Enable the delete buttons
            container
                .find(options.button.delete)
                .css('cursor', 'pointer')
                .on('click', function(evt) {
                    evt.preventDefault();

                    var target = $(this).parent('li');

                    if (target.siblings().length > 0) {
                        target.remove();
                    } else {
                        $.Oscampus.admin.files.clearBlock(target);
                    }
                });

            // Create new file block
            container
                .find(options.button.add)
                .css('cursor', 'pointer')
                .on('click', function(evt) {
                    evt.preventDefault();

                    var blocks = $(container).find(options.fileBlock);

                    if (blocks[0]) {
                        var newElement     = $(blocks[0]).clone(true),
                            chznContainers = newElement.find('.chzn-container');

                        $.Oscampus.admin.files.clearBlock(newElement);
                        $(container.children('ul').first()).append(newElement);

                        // Handle jui chosen selectors
                        if (chznContainers) {
                            chznContainers.each(function(idx, container) {
                                var element = $(container).prev('select');
                                $(container).remove();
                                element
                                    .show()
                                    .removeData('chosen')
                                    .chosen();
                            });
                        }
                    }
                });

            $(options.button.order).css('cursor', 'move');
            container
                .children('ul')
                .first()
                .sortable({
                    handle: options.button.order,
                    cancel: 'input,textarea,select,option'
                });
        },

        clearBlock: function(fileBlock, options) {
            options = $.extend(true, {}, this.options, options);

            fileBlock.find('select option').attr('selected', false);
            fileBlock.find('input, textarea, select').val('');

            fileBlock.find(options.path).html('');

            return fileBlock;
        }
    };
})(jQuery);

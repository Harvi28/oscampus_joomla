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

    $.Oscampus.admin.utilities = {};

    $.Oscampus.admin.utilities.transfer = {
        userDisplays: null,
        submitters  : null,
        form        : null,
        messaging   : null,
        userRequest : {
            option: 'com_oscampus',
            format: 'raw',
            view  : 'utilities',
            layout: 'transfer',
            id    : null

        },

        init: function() {
            var transfer = this;

            // Setup execution buttons and messaging
            transfer.submitters = $('.osc-transfer-execute');
            transfer.messaging = $('.osc-transfer-message');
            transfer.form = $(transfer.submitters[0].form);

            transfer.submitters.on('click', function(evt) {
                evt.preventDefault();

                transfer.form.find('[name=execute]').val(1);
                transfer.messaging.load('index.php', transfer.form.serializeArray(), function() {
                    transfer.userDisplays.each(function(i, el) {
                        var display = $(el),
                            userId  = display.data('user').val();

                        if (userId > 0) {
                            var data = $.extend({}, transfer.userRequest, {
                                id: userId
                            });

                            display.load('index.php', data);
                        }
                    });
                });
            });

            // Setup user displays
            transfer.userDisplays = $('.osc-transfer[data-field]');

            this.userDisplays.each(function(i, el) {
                var display = $(el);

                display.data('user', $(display.attr('data-field')));

                display.data('user').on('change', function() {
                    var data = $.extend({}, transfer.userRequest, {
                        id: $(this).val()
                    });

                    display.load('index.php', data, function() {
                        transfer.form.find('[name=execute]').val(0);
                        transfer.messaging.load('index.php', transfer.form.serializeArray());
                    });
                });
            });
        }
    }
})(jQuery);

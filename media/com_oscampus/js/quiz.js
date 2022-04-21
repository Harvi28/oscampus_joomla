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
        formatSeconds: function(totalSeconds) {
            var minutes = parseInt(totalSeconds / 60, 10),
                seconds = totalSeconds - (minutes * 60),
                pad2    = function(n) {
                    if (n < 10) {
                        return '0' + n;
                    }
                    return n;
                };

            this.html(pad2(minutes) + ':' + pad2(seconds));

            return this;
        }
    });

    $.Oscampus = $.extend({}, $.Oscampus);

    $.Oscampus.quiz = {
        options: {
            form : '#formQuiz',
            timer: {
                selector     : '#oscampus-timer .osc-clock',
                alertSelector: '.oscampus-quiz .osc-timer-alert',
                timeLimit    : 600,
                limitAlert   : 60
            }
        },

        /**
         * Start the countdown timer
         *
         * @param {object} options
         */
        timer: function(options) {
            options = $.extend(true, {}, this.options, options);

            var form = $(options.form);

            if (options.timer.timeLimit > 0) {
                var clock   = $(options.timer.selector),
                    seconds = $.Oscampus.cookie.get('quiz_time', options.timer.timeLimit);

                clock.formatSeconds(seconds);

                var update = setInterval(function() {
                    seconds--;
                    $.Oscampus.cookie.set('quiz_time', seconds);
                    clock.formatSeconds(seconds);
                    if (seconds <= 0) {
                        clearInterval(update);
                        alert(Joomla.JText._('COM_OSCAMPUS_QUIZ_TIMEOUT'));
                        form.submit();

                    } else if (options.timer.limitAlert && seconds <= options.timer.limitAlert) {
                        $(options.timer.alertSelector).show();
                    }
                }, 1000);
            }

            form.on('submit', function() {
                $(this).find('button[type=submit]').prop('disabled', true);

                if (update) {
                    clearInterval(update);
                    $.Oscampus.cookie.delete('quiz_time');
                }
            });
        }
    };
})(jQuery);

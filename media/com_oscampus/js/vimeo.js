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

    $.Oscampus.vimeo = {
        options: {
            lessonId : null,
            duration : null,
            formToken: null,
            accuracy : 1,
            select   : {
                container: '.oscampus-lesson-content',
                download : {
                    button: '.osc-lesson-download'
                }
            },

            player: {
                id       : null,
                autoplay : false,
                player_id: null
            }
        },

        init: function(options) {
            options = $.extend(true, {}, this.options, options);

            if (options.player.id && options.player.player_id) {
                let video  = $.Oscampus.video,
                    player = new Vimeo.Player(options.player.player_id, options.player);

                $.extend($.Oscampus.video.player, {
                    play : function() {
                        player.play();
                    },
                    pause: function() {
                        player.pause();
                    }
                });

                video.setDownload(options);

                player.getDuration().then(function(duration) {
                    options.duration = duration;
                    video.setMonitoring(options);
                })

                player.on('timeupdate', function(data) {
                    video.sendProgress(data.seconds);
                });

                player.on('pause', function(data) {
                    video.sendProgress(data.seconds, true);
                });

                player.on('ended', function(data) {
                    video.sendProgress(data.seconds, true);
                });

            } else {
                // @TODO: Handle this as an error
            }
        },
    };
})(jQuery);

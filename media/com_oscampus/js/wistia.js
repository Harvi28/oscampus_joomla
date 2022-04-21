/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2015-2021 Joomlashack. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * Wistia legacy support
 * @TODO: upgrade to newer API
 *
 * @external video
 * @see {@link https://wistia.com/doc/player-api}
 *
 * @external Wistia
 * @see {@link https://wistia.com/doc/player-api#legacy_api_embeds}
 *
 */

;(function($) {
    $.Oscampus = $.extend({}, $.Oscampus);

    $.Oscampus.wistia = {
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
            }
        },

        /**
         * Initialise all custom video functions. Call with:
         *
         * window._wq = window._wq || [];
         * window._wq.push({
         *    id: {hashedId},
         *    onReady: function(video) {
         *       $.Oscampus.wistia.init(video, {$options});
         *    }
         * });
         *
         * @param {object} player
         * @param {object} options
         */
        init: function(player, options) {
            let video = $.Oscampus.video;

            options = $.extend(true, {}, this.options, options);

            $.extend(video.player, {
                play : function() {
                    player.play();
                },
                pause: function() {
                    player.pause();
                }
            });

            options.duration = player.duration();

            video.setDownload(options);
            video.setMonitoring(options);

            player.bind('secondchange', function(seconds) {
                video.sendProgress(seconds);
            });

            player.bind('pause', function() {
                video.sendProgress(this.time(), true);
            });

            player.bind('end', function() {
                video.sendProgress(this.duration(), true);
            });
        },
    };
})(jQuery);

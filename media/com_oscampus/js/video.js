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

    $.Oscampus.video = {
        throttle: 1250,

        ajax: {
            url    : 'index.php',
            method : 'post',
            queue  : true,
            data   : {
                option  : 'com_oscampus',
                format  : 'json',
                task    : 'lesson.progress',
                lessonId: null,
                score   : null,
                data    : null
            },
            success: function() {
            }
        },

        player: {
            play : function() {
            },
            pause: function() {
            }
        },

        /**
         * @param {object} options
         *
         * @return void
         */
        setDownload: function(options) {
            let $downloadButton = $(options.select.download.button + '[data-lesson-id=' + options.lessonId + ']');
            if ($downloadButton.length === 0) {
                return;
            }

            let that         = this,
                verifyAccess = $.extend(true, {}, this.ajax);

            if (options.formToken) {
                verifyAccess.data[options.formToken] = 1;
            }

            verifyAccess = $.extend(true, verifyAccess, {
                data   : {
                    format  : 'json',
                    task    : 'lesson.downloadable',
                    lessonId: options.lessonId
                },
                success: function(response, status, $xhr) {
                    if (response.error) {
                        let $overlay = that.overlay.create(options, response.message, response.link, response.text);

                        $overlay.on('remove', function(evt) {
                            $downloadButton.spinnerIcon(true);
                        });

                        $downloadButton.spinnerIcon(false);

                    } else {
                        let query = $.extend({}, verifyAccess.data);
                        that.executeDownload(query);

                        $downloadButton.spinnerIcon(true);
                    }
                },
            });

            $downloadButton
                .data({
                    icon    : $downloadButton.find('i.fa'),
                    enabled : 'fa-cloud-download',
                    disabled: 'fa-cloud-download'
                })
                .spinnerIcon(true)
                .on('click', function(evt) {
                    evt.preventDefault();
                    if (!$(this).hasClass('disabled')) {

                        $(this).spinnerIcon();

                        $.Oscampus.video.player.pause();

                        $.ajax(verifyAccess);
                    }
                });
        },

        /**
         * @param {object} query
         *
         * @return void
         */
        executeDownload: function(query) {
            let formId = 'formDownload-' + query.lessonId,
                $form  = $('#' + formId);

            if ($form.length === 0) {
                $.extend(query, {
                    task  : 'lesson.download',
                    format: 'raw'
                });

                $form = $('<form>')
                    .attr({
                        id    : formId,
                        method: 'POST',
                        action: 'index.php?' + $.param(query)
                    });

                $('body').append($form);
            }

            $form.submit();
        },

        /**
         * @param {object} options
         *
         * @return void
         */
        setMonitoring: function(options) {
            if (options.accuracy > 0) {
                let scoreMask = $.Oscampus.mask.create(100 / options.accuracy),
                    duration  = options.duration;

                $.extend(this.ajaxStatus, {
                    duration: duration,
                    percent : duration / scoreMask.length
                });

                this.ajaxStatus.settings = $.extend(true, {}, this.ajax, {
                    data: {
                        lessonId: options.lessonId,
                        data    : duration,
                        score   : scoreMask
                    }
                });

                if (options.formToken) {
                    this.ajaxStatus.settings.data[options.formToken] = 1
                }

                this.ajaxStatus.lastCall = (new Date()).getTime();

            } else {
                this.ajaxStatus = false;
            }
        },

        /**
         * @param {int}      seconds
         * @param {?boolean} immediate
         *
         * @return void
         */
        sendProgress: function(seconds, immediate) {
            if (this.ajaxStatus) {
                let ajaxStatus = this.ajaxStatus,
                    currentBit = this.ajaxStatus.percent ? parseInt(seconds / this.ajaxStatus.percent) : 0,
                    oldMask    = ajaxStatus.settings.data.score,
                    timeStamp  = (new Date()).getTime();

                ajaxStatus.settings.data.score = $.Oscampus.mask.set(oldMask, currentBit);

                if (
                    !!immediate
                    || (
                        oldMask !== ajaxStatus.settings.data.score
                        && (this.throttle < (timeStamp - ajaxStatus.lastCall))
                    )
                ) {
                    $.Oscampus.ajax(ajaxStatus.settings);
                    ajaxStatus.lastCall = timeStamp;
                }
            }
        },
        ajaxStatus  : {
            duration: null,
            percent : null,
            lastCall: null,
            settings: null
        },

        /**
         * Manage overlays on video frame
         */
        overlay: {
            /**
             * Resize the overlay container based on current conditions
             *
             * @param {jQuery} $overlay
             * @param {jQuery} $wrapper
             */
            resize: function($overlay, $wrapper) {
                $wrapper.css('top', Math.max(0, ($overlay.height() - $wrapper.height()) / 2) + 'px');
            },

            /**
             * Create the overlay
             *
             * @param {object} options
             * @param {string} message
             * @param {string} link
             * @param {string} linkText
             */
            create: function(options, message, link, linkText) {
                let lessonId = options.lessonId || '';

                let $container  = $(options.select.container),
                    $overlay    = $('<div>'),
                    $wrapper    = $('<div>').addClass('wrapper'),
                    $linkButton = null,
                    $items      = $('<div>').html(message);

                if (link) {
                    $linkButton = $('<a>')
                        .attr({
                                href   : link,
                                id     : 'subscribe_' + lessonId,
                                'class': 'subscribe'
                            }
                        )
                        .html(
                            '<span id="subscribe_icon_' + lessonId + '">&nbsp</span>'
                            + linkText
                        )
                        .on('click', function(evt) {
                            evt.preventDefault();

                            if (link) {
                                window.location = link;
                            }
                        });

                    $items = $items.add($linkButton);
                }

                $container
                    .css('position', 'relative')
                    .append($overlay);

                $overlay
                    .attr('id', 'overlay_' + lessonId)
                    .addClass('video_overlay')
                    .css({
                        height   : $container.height(),
                        width    : $container.width(),
                        'z-index': 9999
                    })
                    .append($wrapper);

                $items = $items.add(this.createResumeButton(options.lessonId, $overlay));

                $wrapper.append($items);

                this.resize($overlay, $wrapper);

                $overlay.addClass('visible');

                return $overlay;
            },

            /**
             * Create the standard resume button
             *
             * @param {string} lessonId
             * @param {jQuery} $overlay
             *
             * @returns {jQuery}
             */
            createResumeButton: function(lessonId, $overlay) {
                let $resumeButton,
                    resumeId    = 'resume_skip_' + lessonId,
                    resumeArrow = 'resume_skip_arrow_' + lessonId;

                $resumeButton = $('<a>')
                    .attr({
                        href   : '#',
                        id     : resumeId,
                        'class': 'skip'
                    })
                    .html('  <span id="' + resumeArrow + '">&nbsp;</span>'
                        + Joomla.JText._('COM_OSCAMPUS_VIDEO_RESUME', null)
                    )
                    .on('click', function(evt) {
                        evt.preventDefault();
                        $overlay.fadeOut(200, function() {
                            $overlay.triggerRemove();
                            $.Oscampus.video.player.play();
                        });
                    });

                return $resumeButton;
            }
        }
    };
})(jQuery);

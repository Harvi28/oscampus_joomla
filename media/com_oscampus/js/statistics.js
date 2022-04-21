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

    $.Oscampus.statistics = {
        options: {
            controls  : 'div.osc-course-tabs div.osc-tab',
            tab       : 'div.osc-tab',
            disabled  : 'osc-tab-disabled',
            target    : '#osc-data-area',
            form      : '#oscAdminForm',
            pagination: '.osc-pagination a',
            loading   : null
        },

        /**
         * @param {object} options
         *
         * @return void
         */
        init: function(options) {
            options = $.extend(this.options, options);

            var $controls = $(this.options.controls),
                self      = this;

            $controls.find('a').on('click', function(evt) {
                evt.preventDefault();

                var $active = $(this).parent(options.tab);

                if ($active.hasClass(options.disabled)) {
                    $controls.each(function(idx, el) {
                        if ($active[0] === el) {
                            $(el)
                                .removeClass(options.disabled)
                                .find('a').css('cursor', 'default');

                        } else {
                            $(el)
                                .addClass(options.disabled)
                                .find('a').css('cursor', 'pointer');
                        }
                    });

                    self.load($(this).attr('href').substr(1));
                }
            });

            $controls
                .not('.' + options.disabled)
                .addClass(options.disabled)
                .find('a')
                .trigger('click');
        },

        /**
         * @param {string} $report
         * @param {int}    [$start]
         *
         * @return void
         */
        load: function($report, $start) {
            $('html,body').animate({scrollTop: 0}, 'slow');

            var loading      = this.options.loading || Joomla.JText._('COM_OSCAMPUS_LOADING'),
                $target      = $(this.options.target),
                $form        = $(this.options.form),
                $url         = $form.attr('action'),
                $reportField = $form.find('input[name=report]'),
                $returnField = $form.find('input[name=return]');

            if ($start) {
                $url += '?start=' + $start;
            }

            $returnField.val($reportField.val());
            $reportField.val($report);

            var $data = $form.serialize();
            $target
                .html(loading)
                .load($url, $data, function(response, status, xhr) {
                        if (status === 'error') {
                            if (xhr.responseText) {
                                $target.html(xhr.responseText);

                            } else {
                                var msg = Joomla.JText._('COM_OSCAMPUS_ERROR_GENERIC_PREFIX');
                                $target.html(msg.replace('%s', xhr.status + ' ' + xhr.statusText));
                            }
                        }
                    }
                );
        },

        /**
         * Standard method for returning to previous report
         */
        back: function() {
            var returnReport = $(this.options.form).find('input[name=return]').val();

            if (returnReport) {
                this.load(returnReport);
            }
        },

        /**
         * @param {string} task
         *
         * @return void
         */
        setPagination: function(task) {
            if (task) {
                var self = this;

                $(this.options.form).find('[name=limit]')
                    .attr('onchange', null)
                    .on('change', function(evt) {
                        self.load(task);
                    });

                $(this.options.pagination).on('click', function(evt) {
                    evt.preventDefault();
                    evt.stopPropagation();

                    var $start = $(this).attr('href').match(/start=(\d*)/);

                    self.load(task, $start ? $start[1] : 1);
                });
            }
        }
    };
})(jQuery);

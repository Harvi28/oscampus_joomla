/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2021 Joomlashack.com. All rights reserved
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
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
 * along with OSCampus.  If not, see <https://www.gnu.org/licenses/>.
 */
;jQuery(function($) {
    $.Oscampus = $.extend({}, $.Oscampus);

    $.Oscampus.admin = $.extend(true, {}, $.Oscampus.admin);

    /**
     * Standard handling of ajax admin pages
     *
     * @type {{init: init, submitForm: submitForm}}
     */
    $.Oscampus.admin.statistics = (function() {
        let options = {
            loading  : null,
            form     : null,
            container: null,
            formData : {
                option: 'com_oscampus',
                view  : 'statistics',
                format: 'raw',
            },
            return   : null
        };

        /**
         * @param {?boolean} noFormData
         *
         * @return {{view: string, format: string, option: string}}
         */
        let getFormData = function(noFormData) {
            let data = Object.assign({}, options.formData);

            if (!noFormData) {
                options.form.serializeArray().forEach(function(element) {
                    data[element.name] = element.value;
                });
            }

            return data;
        };

        /**
         * @param {object} params
         *
         * @return void
         */
        let init = function(params) {
            options = $.extend(true, options, params);

            // Init sortable column titles
            $('.js-stools-column-order').on('click', function(evt) {
                evt.preventDefault();

                let order     = $(this).data('order'),
                    direction = $(this).data('direction');

                $('#list_fullordering').val(order + ' ' + direction);

                submitForm();
            });

            // Initialize the clear button
            $('.js-stools-btn-clear').on('click', function(evt) {
                evt.preventDefault();

                $(this.form.filter_search).val('');
                submitForm();
            });

            // Initialize list limit
            $('#list_limit').on('change', function(evt) {
                evt.preventDefault();

                submitForm();
            });

            // Initialize back button when return report provided
            if (options.return) {
                $('#backButton').on('click', function(evt) {
                    submitForm({report: options.return}, true);
                })
            }

            // Hijack normal form submit
            options.form.on('submit', function(evt) {
                evt.preventDefault();

                submitForm();
            });
        }

        /**
         * @param {?object}  data
         * @param {?boolean} noFormData
         *
         * @return void
         */
        let submitForm = function(data, noFormData) {
            data = $.extend(true, {}, getFormData(noFormData), data);

            options.container
                .html(options.loading)
                .load('index.php', data);
        };

        return {
            init      : init,
            submitForm: submitForm
        };
    }());
});

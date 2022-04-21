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
        /**
         * Simple panel toggle that will enable/disable
         * any form input fields in the container
         * with option to set focus to first element on
         * enable.
         *
         * Triggers the custom events sr.enable/sr.disable
         */
        closePanel: function(state, options) {
            options = $.extend({
                focus: true
            }, options);

            if (state) {
                $(this)
                    .hide()
                    .find(':input')
                    .attr('disabled', true)
                    .trigger('sr.disable');
            } else {
                $(this)
                    .show()
                    .find(':input')
                    .attr('disabled', false)
                    .trigger('sr.enable');

                if (options.focus) {
                    $(this).find(':input:visible').first().focus();
                }
            }
            return this;
        },

        /**
         * Simple panel slider that will enable/disable
         * any form input fields in the container
         * with option to set focus to first element on
         * enable.
         *
         * Triggers the custom events sr.enable/sr.disable
         */
        closePanelSlide: function(state, options) {
            options = $.extend({
                duration: 400,
                focus   : true
            }, options);

            if (state) {
                $(this)
                    .slideUp()
                    .find(':input')
                    .attr('disabled', true)
                    .trigger('sr.disable');
            } else {
                $(this)
                    .slideDown()
                    .find(':input')
                    .attr('disabled', false)
                    .trigger('sr.enable');

                if (options.focus) {
                    $(this).find(':input:visible').first().focus();
                }
            }
            return this;
        },

        /**
         * Sometimes we want to insert a new event handler at the beginning of the queue
         *
         * @param {string} event
         * @param {function} handler
         *
         * @returns {jQuery}
         */
        bindBefore: function(event, handler) {
            let elements = $(this),
                element, lastEvent;

            for (let i = 0; i < elements.length; i++) {
                element = elements.get(i);
                $(element).on(event, handler);
                lastEvent = $._data(element, 'events').click.pop();
                $._data(element, 'events').click.unshift(lastEvent);
            }

            return this;
        },

        /**
         * Toggle a spinner icon with a base icon. Passing no argument
         * sets the spinner icon. Passing true/false sets the enabled/disabled icon
         *
         * Uses the element data store for configuration
         *    {jQuery} icon     : the html element displaying the icon
         *    {string} disabled : The icon to use when option is disabled
         *    {string} enabled  : The icon to use when option is enabled
         *
         * @param {boolean} [isEnabled]
         *
         * @returns {jQuery}
         */
        spinnerIcon: function(isEnabled) {
            let data = $(this).data();

            if (data.icon) {
                let icon = {
                    disabled: data.disabled || 'fa-square-o',
                    enabled : data.enabled || 'fa-check-square'
                };

                if (isEnabled === undefined) {
                    // With no arguments, set waiting state
                    data.icon
                        .removeClass(icon.disabled)
                        .removeClass(icon.enabled)
                        .addClass('fa-spinner fa-spin');

                    $(this).addClass('disabled');

                } else {
                    data.icon.removeClass('fa-spinner fa-spin');

                    if (isEnabled) {
                        data.icon
                            .removeClass(icon.disabled)
                            .addClass(icon.enabled);

                        $(this).removeClass('disabled');

                    } else {
                        data.icon
                            .removeClass(icon.enabled)
                            .addClass(icon.disabled);

                        $(this).addClass('disabled');
                    }
                }
            }

            return this;
        },

        /**
         * A custom remove method for when we want to catch the remove event
         */
        triggerRemove: function() {
            $(this).trigger('remove');
            $(this).remove();
        }
    });

    $.Oscampus = $.extend({}, $.Oscampus);

    // We hate this, but there doesn't appear to be an alternative
    $.Oscampus.isIE11 = !!window.MSInputMethodContext && !!document.documentMode;

    /**
     * Simple tabs. Define tab headings with any selector
     * and include the attribute data-content with a selector
     * for the content area it controls. All tabs selected by
     * the passed selector will hide all content panels
     * except the one(s) controlled by the active tab.
     *
     * @param options
     *        selector : A jQuery selector for the tab headers
     */
    $.Oscampus.tabs = function(options) {
        options = $.extend({}, this.tabs.options, options);

        let headers = $(options.selector);
        headers
            .css('cursor', 'pointer')
            .each(function(idx, active) {
                $(this)
                    .data('contentPanel', $($(this).attr('data-content')))
                    .on('click', function() {
                        headers.each(function() {
                            $(this)
                                .toggleClass(options.enabled, active === this)
                                .toggleClass(options.disabled, active !== this)
                                .data('contentPanel').closePanel(active !== this, options)
                        });
                    });
            });

        // Set active panel
        if (!options.active) {
            options.active = '#' + $(headers[0]).attr('id');
        }
        $(headers.filter(options.active)).trigger('click', {focus: false});
    };
    $.Oscampus.tabs.options = {
        selector: null,
        active  : null,
        enabled : 'osc-tab-enabled',
        disabled: 'osc-tab-disabled'
    };

    /**
     * Independent sliding panels. Use any selector
     * to select one or more slide controls. Use the
     * data-content attribute to select the content
     * panels to slide Up/Down on clicking the control.
     *
     * @param options
     *        selector : a jQuery selector for the slider headers
     *        visible  : bool - initial visible state (default: false)
     */
    $.Oscampus.sliders = function(options) {
        options = $.extend({}, this.sliders.options, options);

        $(options.selector).each(function() {
            $(this)
                .css('cursor', 'pointer')
                .data('contentPanel', $($(this).attr('data-content')))
                .on('click', function(evt, options) {
                    evt.preventDefault();
                    let contentPanel = $(this).data('contentPanel');
                    contentPanel.closePanelSlide(contentPanel.is(':visible'), options);
                })
                .data('contentPanel').closePanel(!options.visible, {focus: false});
        });
    };
    $.Oscampus.sliders.options = {
        selector: null,
        visible : false
    };

    /**
     * Wrapper to the jQuery ajax method with defaults for this application
     * Include support for chaining ajax calls into a queue
     *
     * @param options Standard ajax options
     *
     * @return {jQuery}
     */
    $.Oscampus.ajax = function(options) {
        let ajaxQueue = this.ajax.queue;

        options = $.extend(true, {}, this.ajax.options, options);

        if (options.queue) {
            options.beforeSend = function() {
                if (this.queue) {
                    ajaxQueue.push(this);

                    return ajaxQueue.length <= 1;
                }

                return true;
            };
            options.complete   = function() {
                ajaxQueue.shift();
                let next = ajaxQueue[0];
                if (next) {
                    next.queue = false;
                    $.ajax(next);
                }
            }
        }

        return $.ajax(options);
    };
    $.Oscampus.ajax.options = {
        url     : 'index.php',
        data    : {
            option: 'com_oscampus',
            format: 'json'
        },
        dataType: 'json',
        queue   : false,
        success : function(result) {
            alert('RESULT: ' + result);
        },
        error   : function(xhr, status, error) {
            alert(error);
        }
    };
    $.Oscampus.ajax.queue   = [];

    $.Oscampus.mask = {
        create: function(length) {
            return ('0').repeat(length)
        },
        set   : function(mask, bit, value) {
            if (bit >= mask.length) {
                bit = mask.length - 1;

            } else if (bit < 0) {
                bit = 0;
            }

            return mask.substr(0, bit) + (value || '1') + mask.substr(bit + 1);
        },
    };

    /**
     * Make a container sortable by dragging
     *
     * @param options
     *        selector : Selector for the parent container
     *        css      : css that will be applied to the sortable items
     *        options  : Options to pass to sortable setup
     *
     * @return void
     */
    $.Oscampus.sortable = function(options) {
        options = $.extend({}, this.sortable.options, options);

        let selection = $(options.selector);

        selection
            .sortable(options.options)
            .children().css(options.css);
    };
    $.Oscampus.sortable.options = {
        selector: '.oscampus-sortable',
        css     : {
            cursor: 'move'
        },
        options : null
    };

    /**
     * Utilities for managing cookies
     */
    $.Oscampus.cookie = {
        /**
         * Get the value of the named cookie or return the default value
         *
         * @param {string} name
         * @param {*} [defaultValue]
         *
         * @returns {string|null}
         */
        get: function(name, defaultValue) {
            let cookies = document.cookie.split('; ');

            for (let i = 0; i < cookies.length; i++) {
                if (cookies[i].indexOf(name + '=') === 0) {
                    return cookies[i].split('=').pop();
                }
            }

            return defaultValue || null;
        },

        /**
         * Set the named cookie, overwriting if it exists
         * and returning the original value
         *
         * @param {string} name
         * @param {string} value
         *
         * @return {string}
         */
        set: function(name, value) {
            let oldValue = this.get(name);

            document.cookie = name + '=' + value;

            return oldValue;
        },

        /**
         * Delete the named cookie
         *
         * @param {string} name
         */
        delete: function(name) {
            document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT';
        }
    }
})(jQuery);


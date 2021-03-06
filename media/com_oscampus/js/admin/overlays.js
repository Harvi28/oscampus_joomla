/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2019 Joomlashack.com. All rights reserved
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

    $.Oscampus.admin.overlays = {
        options: {
            baseUri     : null,
            defaultImage: null,
            boxBin      : '#positions',
            certificate : '#certificate-image',
            dataBox     : '.data-box',
            preview     : '.osc-certificate-preview',
            imageField  : null,
            editBase    : '#adminForm [id$=editBase]',
            dragHandle  : '.osc-certificate-drag-button',
            closeButton : '',
            closeClass  : 'osc-overlay-close-button'
        },

        init: function(options) {
            options = $.extend(true, {}, this.options, options);

            let $boxBin      = $(options.boxBin),
                $certificate = $(options.certificate),
                $baseImage   = $certificate.find('img').first(),
                $editBase    = $(options.editBase),
                $boxes       = $boxBin.find(options.dataBox);

            $.fn.extend({
                dropBox: function() {
                    this.each(function(idx, box) {
                        let $box         = $(box),
                            $closeButton = $(options.closeButton),
                            boxPosition  = $box.getPosition();

                        $box
                            .appendTo($certificate)
                            .css({
                                position: 'absolute',
                                top     : boxPosition.top,
                                left    : boxPosition.left,
                                width   : boxPosition.width,
                                height  : boxPosition.height
                            })
                            .resizable({
                                disabled: false,
                                handles : 'e'
                            })
                            .on('resize', function(evt) {
                                $box.savePosition();
                            })
                            .draggable('option', 'containment', 'parent');

                        if (!$box.find('.' + options.closeClass)[0]) {
                            $closeButton
                                .addClass(options.closeClass)
                                .on('click', function(evt) {
                                    $(this).parent().resetBox();
                                });

                            $box.prepend($closeButton);
                        }
                    });

                    return this;
                },

                resetBox: function() {
                    if ($boxBin[0]) {
                        this.each(function(idx, box) {
                            let $box     = $(box),
                                $storage = $box.data('storage');

                            if ($box.draggable('instance')) {
                                $box.draggable('option', 'containment', '');
                            }
                            if ($box.resizable('instance')) {
                                $box.resizable('disable');
                            }

                            $box
                                .css({
                                    position: '',
                                    top     : '',
                                    left    : '',
                                    width   : '',
                                    height  : ''
                                })
                                .appendTo($boxBin)
                                .off('resize');

                            $box.find('.' + options.closeClass).remove();
                            if ($storage) {
                                $storage.val('');
                            }
                        });

                        let $boxes = $boxBin.find(options.dataBox);

                        $boxes.sort(function(a, b) {
                            let orderA = parseInt(a.getAttribute('data-order')),
                                orderB = parseInt(b.getAttribute('data-order'));

                            return orderA < orderB ? -1 : (orderA > orderB ? 1 : 0);
                        });
                        $boxes.detach().appendTo($boxBin);
                    }

                    return this;
                },

                getPosition: function() {
                    return $.extend({}, this.offset(), {
                        width : this.width(),
                        height: this.height(),
                        outer : {
                            width : this.outerWidth(),
                            height: this.outerHeight()
                        }
                    });
                },

                savePosition: function() {
                    let imagePosition = $baseImage.getPosition();

                    $editBase.val(JSON.stringify(imagePosition));

                    this.each(function(idx, box) {
                        let $box        = $(box),
                            boxPosition = $box.getPosition(),
                            $storage    = $box.data('storage');

                        if ($storage) {
                            boxPosition.top  = boxPosition.top - imagePosition.top;
                            boxPosition.left = boxPosition.left - imagePosition.left;

                            $storage.val(JSON.stringify(boxPosition));
                        }
                    });

                    return this;
                }
            });

            $boxes.draggable({
                handle: options.dragHandle,
                stop  : function(event, ui) {
                    let $box    = $(this),
                        $parent = $(this).parent();

                    if ($parent[0]
                        && $certificate[0]
                        && $parent[0] !== $certificate[0]
                    ) {
                        $box.resetBox();

                    } else {
                        $box.savePosition();
                    }
                }
            });

            $certificate.droppable({
                accept: $boxes,
                drop  : function(evt, ui) {
                    ui.draggable.dropBox();
                }
            });

            $(options.imageField).on('change', function() {
                if (this.value) {
                    $baseImage.attr('src', options.baseUri + this.value);

                } else {
                    $baseImage.attr('src', options.defaultImage);
                }
                $boxes.resetBox();
            });

            $certificate.data('position', $certificate.getPosition());

            $(window).on('resize', function(evt) {
                let resizeTimer;

                clearTimeout(resizeTimer);

                resizeTimer = setTimeout(function() {
                    let oldPosition = $certificate.data('position'),
                        newPosition = $certificate.getPosition(),
                        moveTop     = newPosition.top - oldPosition.top,
                        moveLeft    = newPosition.left - oldPosition.left;

                    if (moveTop || moveLeft) {
                        $certificate
                            .data('position', newPosition)
                            .find('.data-box')
                            .each(function(idx, el) {
                                let $box        = $(el),
                                    boxPosition = $box.getPosition();

                                $box
                                    .css({
                                        top : (boxPosition.top + moveTop) + 'px',
                                        left: (boxPosition.left + moveLeft) + 'px'
                                    })
                                    .savePosition();
                            });
                    }
                }, 400);
            });

            // Save initial states restore placed boxes
            let initBoxes = function() {
                $boxes.each(function(idx, box) {
                    let $box          = $(box),
                        $storage      = $box.find('[name*=overlays]').first(),
                        initPosition  = $storage.val(),
                        imagePosition = $baseImage.getPosition();

                    $box
                        .attr('data-order', idx)
                        .data('storage', $storage);

                    if (initPosition) {
                        try {
                            initPosition     = JSON.parse(initPosition);
                            let dropPosition = {
                                position: 'absolute',
                                margin  : 0,
                                padding : 0,
                                top     : (initPosition.top + imagePosition.top) + 'px',
                                left    : (initPosition.left + imagePosition.left) + 'px',
                                width   : initPosition.width + 'px',
                                height  : initPosition.height + 'px'
                            };

                            $box
                                .css(dropPosition)
                                .dropBox()
                                .css({
                                    padding: '',
                                    margin : ''
                                })

                        } catch (error) {
                            alert($box.data('item') + ': Unparsable JSON - ' + initPosition);
                        }
                    }
                });
            };

            setTimeout(initBoxes, 100);
        }
    };
})(jQuery);

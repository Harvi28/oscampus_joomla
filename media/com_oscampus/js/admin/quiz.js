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
    $.fn.pop = function() {
        var top = this.get(-1);
        this.splice(this.length - 1, 1);
        return top;
    };

    $.Oscampus = $.extend({}, $.Oscampus);

    $.Oscampus.admin = $.extend(true, {}, $.Oscampus.admin);

    $.Oscampus.admin.quiz = {};

    $.Oscampus.admin.quiz.init = function() {

        // Enable the delete buttons
        $('[class*=osc-quiz-delete-]')
            .css('cursor', 'pointer')
            .on('click', function(evt) {
                evt.preventDefault();

                var target   = $(this).parent('li'),
                    siblings = target.siblings();

                if (siblings.length > 1) {
                    target.remove();
                }
            });

        // Create new answer
        $('[class*=osc-quiz-add-answer]')
            .css('cursor', 'pointer')
            .on('click', function(evt) {
                evt.preventDefault();

                var siblings = $(this).parent('ul').find('li.osc-answer');

                if (siblings[0]) {
                    var newElement = $(siblings[0]).clone(true);

                    newElement.find('input[type=radio]')
                        .prop('checked', false)
                        .val(siblings.length);

                    var newInput = newElement.find('input[type=text]'),
                        newName  = newInput.prop('name').replace(/\[\d+\]$/, '[' + siblings.length + ']'),
                        newId    = newInput.prop('id').replace(/\d+$/, siblings.length);

                    newInput
                        .prop('name', newName)
                        .prop('id', newId)
                        .val('');

                    newElement.insertBefore($(this));
                }
            });

        // Create new question
        $('[class*=osc-quiz-add-question]')
            .css('cursor', 'pointer')
            .on('click', function(evt) {
                evt.preventDefault();

                var siblings = $(this).parent('ul').find('li.osc-question');
                if (siblings[0]) {
                    var newElement = $(siblings[0]).clone(true),
                        answers    = newElement.find('li.osc-answer');

                    while (answers.length > 1) {
                        $(answers.pop()).remove();
                    }

                    newElement.find('input[type=text]').val('');
                    newElement.find('input[type=radio]').prop('checked', false);

                    var newName, newId;
                    newElement.find('input').each(function(index, element) {
                        newName = $(element).prop('name').replace(/questions\]\[\d+/, 'questions][' + siblings.length);
                        newId = $(element).prop('id').replace(/questions_\d+/, 'questions_' + siblings.length);

                        $(element).prop('name', newName).prop('id', newId);
                    });

                    newElement.insertBefore(this);
                }
            });
    };
})(jQuery);

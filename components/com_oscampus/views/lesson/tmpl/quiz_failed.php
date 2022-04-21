<?php
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

use Joomla\CMS\Language\Text;
use Oscampus\Lesson\Type\Quiz;

defined('_JEXEC') or die();

/**
 * @var Quiz $quiz
 */

$quiz     = $this->lesson->renderer;
$activity = $this->activity;
$score    = Text::sprintf('COM_OSCAMPUS_SCORE', number_format($activity->score))
?>
    <div class="osc-section">
        <h1 class="osc-lesson-title"><?php echo $this->lesson->title; ?></h1>
        <div class="osc-lesson-links">
            <?php echo $this->loadDefaultTemplate('navigation'); ?>
        </div>
    </div>

    <div class="osc-section osc-quiz-details">
        <div class="block1">
            <div class="osc-quiz-big-icon">
                <i class="fa fa-times"></i>
            </div>
        </div>
        <div class="block3">
            <div class="osc-quiz-left">
                <span class="osc-quiz-score-label">
                    <?php echo Text::_('COM_OSCAMPUS_QUIZ_YOUR_SCORE'); ?>
                </span>
                <br>
                <span class="osc-quiz-percentage">
                    <?php echo $score; ?>
                </span>
                <br>
                <span class="osc-quiz-failed-label osc-negative-color">
                    <?php echo Text::_('COM_OSCAMPUS_QUIZ_STATUS_FAILED'); ?>
                </span>
                <br>
            </div>
        </div>
        <div class="block8">
            <div class="osc-quiz-right">
                <strong><?php echo Text::_('COM_OSCAMPUS_QUIZ_YOUR_SCORE_LABEL'); ?></strong>
                <strong class="osc-negative-color"><?php echo $score; ?></strong>,
                <?php echo Text::_('COM_OSCAMPUS_QUIZ_PASSING_SCORE_LABEL'); ?>
                <strong class="osc-positive-color">
                    <?php echo Text::sprintf('COM_OSCAMPUS_SCORE', number_format($quiz->passingScore)); ?>
                </strong>.
                <br>
                <strong><?php echo Text::_('COM_OSCAMPUS_QUIZ_RETRY_ASK'); ?></strong><br>

                <div class="osc-btn-group">
                    <form id="formRetry" name="formRetry" action="" method="post">
                        <input type="hidden" name="retry" value="1"/>
                        <div class="osc-btn-group">
                            <button class="osc-btn osc-btn-main">
                                <?php echo Text::_('COM_OSCAMPUS_QUIZ_RETRY'); ?>
                            </button><?php
                            echo JHtml::_(
                                'osc.link.course',
                                $this->lesson->courses_id,
                                Text::_('COM_OSCAMPUS_LATER'),
                                'class="osc-btn"'
                            );
                            ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- .osc-section -->
<?php
echo $this->loadTemplate('results');

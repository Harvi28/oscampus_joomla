<?php
/**
 * @package   OSCampus
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2016-2021 Joomlashack.com. All rights reserved
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

$attempt     = $this->quiz->getLastAttempt($this->activity);
$showCorrect = $this->quiz->hasPassed($this->activity) || $this->getParams()->get('quizzes.showCorrect', 1);
?>
    <div class="osc-quiz-question">
        <?php
        $i           = 1;
        foreach ($attempt as $question) :
            $selected = $question->selected;
            $correct = $selected && $question->answers[$selected]->correct;
            ?>
            <h4>
                <?php
                echo sprintf('<i class="fa %s"></i> ', $correct ? 'fa-check' : 'fa-times');
                echo $i++ . '. ' . $this->escape($question->text);
                ?>
            </h4>
            <?php
            if ($correct || $showCorrect) : ?>
                <ul class="osc-quiz-options">
                    <?php foreach ($question->answers as $key => $answer) : ?>
                        <li>
                            <?php
                            echo sprintf('<i class="fa fa-fw%s"></i> ', $answer->correct ? ' fa-check' : '');
                            echo $this->escape($answer->text);
                            ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <div class="alert alert-danger">
                    <p><?php echo $this->escape(Text::_('COM_OSCAMPUS_QUIZ_WRONG_ANSWER')); ?></p>
                </div>
            <?php endif;
        endforeach;
        ?>
    </div>
    <!-- .osc-section -->
<?php
echo $this->loadDefaultTemplate('description');

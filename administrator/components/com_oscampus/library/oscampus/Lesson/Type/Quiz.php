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

namespace Oscampus\Lesson\Type;

use Exception;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text as JText;
use Joomla\Registry\Registry as Registry;
use Oscampus\Activity\LessonStatus;
use OscampusFactory;

defined('_JEXEC') or die();

class Quiz extends AbstractType
{
    /**
     * @inheritdoc
     */
    protected static $icon = 'fa-question';

    /**
     * @inheritdoc
     */
    protected $overlayImage = 'com_oscampus/lesson-overlay-quiz.png';

    /**
     * @var int
     */
    public $passingScore = null;

    /**
     * @var int
     */
    public $timeLimit = null;

    /**
     * @var int
     */
    public $limitAlert = null;

    /**
     * @var int
     */
    public $quizLength = null;

    /**
     * @var object[]
     */
    protected $questions = null;

    /**
     * @inheritDoc
     */
    protected function init()
    {
        parent::init();

        $content = json_decode($this->lesson->content);

        $this->passingScore = $this->params->get('quizzes.passingScore');
        $this->timeLimit    = empty($content->timeLimit)
            ? $this->params->get('quizzes.timeLimit')
            : $content->timeLimit;

        $this->limitAlert = $this->params->get('quizzes.limitAlert');


        $this->questions = isset($content->questions) ? (array)$content->questions : [];
        foreach ($this->questions as $question) {
            $question->answers = (array)$question->answers;
        }
        $this->quizLength = $content->quizLength ?? count($this->questions);
    }

    /**
     * @inheritDoc
     */
    public static function getStatusId(LessonStatus $status = null)
    {
        return $status->completed ? LessonStatus::PASSED : LessonStatus::FAILED;
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        HTMLHelper::_('osc.jquery');
        HTMLHelper::_('script', 'com_oscampus/quiz.min.js', ['relative' => true]);

        if ($this->lesson->isAuthorised()) {
            $options = json_encode([
                'timer' => [
                    'timeLimit'  => $this->timeLimit,
                    'limitAlert' => $this->limitAlert < $this->timeLimit ? $this->limitAlert : 0
                ]
            ]);

            JText::script('COM_OSCAMPUS_QUIZ_TIMEOUT');
            HTMLHelper::_('osc.onready', "$.Oscampus.quiz.timer({$options})");
        }

        return $this;
    }

    /**
     * @param LessonStatus $activity
     *
     * @return bool
     */
    public function hasPassed(LessonStatus $activity)
    {
        return $this->passingScore > 0 && $activity->score >= $this->passingScore;
    }

    /**
     * @param LessonStatus $activity
     *
     * @return object[]
     */
    public function getLastAttempt(LessonStatus $activity)
    {
        if (isset($activity->data) && $activity->data) {
            $questions = json_decode($activity->data);
            foreach ($questions as $question) {
                $question->answers = (array)$question->answers;
            }

            return $questions;
        }

        return [];
    }

    /**
     * Custom method to select randomly ordered questions. Retains the
     * same questions and order while the quiz time is still running
     *
     * @return array
     * @throws Exception
     */
    public function getQuestions()
    {
        $cookieStore = $this->getQuestionStore();

        $keys     = $this->getUserState($cookieStore, null, 'base64');
        $timeLeft = $this->getUserState('quiz_time', null, 'base64');

        if ($keys && $timeLeft) {
            $keys = json_decode(base64_decode($keys));

        } elseif ($this->quizLength < 0) {
            $keys = array_keys($this->questions);

        } else {
            $length = (int)$this->quizLength ?: count($this->questions);
            $length = min(count($this->questions), $length);

            $keys = (array)array_rand($this->questions, $length);
            shuffle($keys);

            $this->setUserState($cookieStore, base64_encode(json_encode($keys)));
        }

        $selection = [];
        foreach ($keys as $key) {
            $selection[$key] = $this->questions[$key];
        }

        return $selection;
    }

    /**
     * Gets the cookie used for storing questions currently in play
     *
     * @return string
     */
    protected function getQuestionStore()
    {
        return 'quiz_questions_' . $this->lesson->id;
    }

    /**
     * @inheritDoc
     */
    public function prepareActivityProgress(LessonStatus $status, $score = null, $data = null)
    {
        if (is_array($data)) {
            $status->score = 0;

            $responses = $this->collectResponse($data);
            foreach ($responses as $response) {
                $selected = $response->selected;
                $answer   = $response->answers[$selected];

                $status->score += (int)$answer->correct;
            }
            $status->score = round(($status->score / count($responses)) * 100);
            $status->data  = json_encode($responses);
        }

        $now = OscampusFactory::getDate();
        if ($status->score >= $this->passingScore) {
            $status->completed = $now;
        }
    }

    /**
     * Process the raw responses from a form. Expects an array in
     * the form
     * array( questionHash => answerHash)
     *
     * Where the hashes are md5 hashes of the associated texts.
     *
     * @param array $responses
     *
     * @return array
     */
    protected function collectResponse(array $responses)
    {
        $data = [];
        foreach ($responses as $question => $answer) {
            if (isset($this->questions[$question])) {
                $q      = $this->questions[$question];
                $a      = isset($q->answers[$answer]) ? $answer : null;
                $data[] = (object)[
                    'text'     => $q->text,
                    'answers'  => $q->answers,
                    'selected' => $a
                ];
            }
        }

        // Reset all caches for this attempt
        $this->setUserState($this->getQuestionStore(), null);
        $this->setUserState('quiz_time', null);

        return $data;
    }

    /**
     * @inheritDoc
     */
    protected function prepareAdminData(Registry $data)
    {
        parent::prepareAdminData($data);

        // Force '0' to blank
        if (!$data->get('content.timeLimit')) {
            $data->set('content.timeLimit', '');
        }
        if (!$data->get('content.quizLength')) {
            $data->set('content.quizLength', '');
        }
    }

    /**
     * @inheritDoc
     */
    public function saveAdminChanges(Registry $data)
    {
        $quiz = $data->get('content');

        if ($quiz) {
            if (is_string($quiz)) {
                $quiz = json_decode($quiz, true);
            }
        }

        $questions = [];
        foreach ((array)$quiz->questions as $question) {
            $question = (array)$question;

            $questionText = $question['text'];
            if ($questionText) {
                if (!isset($question['correct'])) {
                    throw new Exception(JText::sprintf('COM_OSCAMPUS_ERROR_QUIZ_CORRECT_ANSWER', $questionText));
                }

                $questionKey    = md5($questionText);
                $correctAnswer  = $question['correct'];
                $enteredAnswers = (array)$question['answers'];

                $answers = [];
                foreach ($enteredAnswers as $answerId => $answerText) {
                    if (!empty($answerText)) {
                        $answerKey = md5($answerText);
                        if (isset($answers[$answerKey])) {
                            throw new Exception(
                                JText::sprintf(
                                    'COM_OSCAMPUS_ERROR_QUIZ_DUPLICATE_ANSWER',
                                    $answerText,
                                    $questionText
                                )
                            );
                        }

                        $answers[$answerKey] = [
                            'text'    => $answerText,
                            'correct' => (int)($correctAnswer == $answerId)
                        ];
                    }
                }

                $minimumAnswers = 2;
                if (count($answers) < $minimumAnswers) {
                    throw new Exception(
                        JText::sprintf(
                            'COM_OSCAMPUS_ERROR_QUIZ_MINIMUM_ANSWERS',
                            $questionText,
                            $minimumAnswers
                        )
                    );
                }

                $questions[$questionKey] = [
                    'text'    => $question['text'],
                    'answers' => $answers
                ];
            }
        }

        $quiz->questions = $questions;
        $data->set('content', json_encode($quiz));
    }

    /**
     * Quizzes don't make sense if not logged in
     *
     * @inheritDoc
     */
    public static function isAuthorised($access, $type = null)
    {
        return parent::isAuthorised($access) && OscampusFactory::getUser()->id;
    }
}

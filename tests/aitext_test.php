<?php
// This file is part of Moodle - http://moodle.org/.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace quiz_aitext;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/report/aitext/classes/aitext.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once($CFG->dirroot . '/mod/quiz/tests/quiz_question_helper_test_trait.php');

/**
 * Unit tests for the aitext class.
 *
 * @package    quiz_aitext
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \quiz_aitext\aitext
 */
class aitext_test extends \advanced_testcase {
    use \quiz_question_helper_test_trait;

    /** @var \stdClass The student user object */
    protected $student;

    /**
     * Set up function to create test data.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        // Create a student user.
        $this->student = $this->getDataGenerator()->create_user();
    }

    /**
     * Test that get_question_attempts returns an array.
     */
    public function test_get_question_attempts_returns_array(): void {
        // Create a quiz and related data.
        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);

        // Create a question with a grade.
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $questiongenerator->create_question_category();
        $question = $questiongenerator->create_question('multichoice', null, ['category' => $cat->id]);

        // Add question to quiz with a specific grade.
        quiz_add_quiz_question($question->id, $quiz, 0, 1);

        // Rebuild the quiz grade calculations.
        quiz_update_sumgrades($quiz);

        // Create and complete a quiz attempt using the trait helper.
        $this->setUser($this->student);
        list($quizobj, $quba, $attemptobj) = $this->attempt_quiz($quiz, $this->student);

        // Instantiate the aitext class.
        $aitext = new aitext();

        // Call the get_question_attempts method.
        $result = $aitext->get_question_attempts($quiz->id);

        // Assert that the result is an array.
        $this->assertIsArray($result);
    }

    /**
     * Test that get_question_attempts returns data for existing quiz.
     */
    public function test_get_question_attempts_with_existing_quiz(): void {
        // Create a quiz and related data.
        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);

        // Create a question with a grade.
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $questiongenerator->create_question_category();
        $question = $questiongenerator->create_question('shortanswer', null, ['category' => $cat->id]);

        // Add the question to the quiz with a specific grade.
        quiz_add_quiz_question($question->id, $quiz, 0, 1);

        // Rebuild the quiz grade calculations.
        quiz_update_sumgrades($quiz);

        // Create a quiz attempt using the trait helper.
        $this->setUser($this->student);
        list($quizobj, $quba, $attemptobj) = $this->attempt_quiz($quiz, $this->student);

        // Instantiate the aitext class.
        $aitext = new aitext();

        // Call the get_question_attempts method.
        $result = $aitext->get_question_attempts($quiz->id);

        // Assert that we get data back.
        $this->assertNotEmpty($result);

        // Check that the returned data contains expected fields.
        $firstattempt = reset($result);
        $this->assertNotNull($firstattempt);
        $this->assertObjectHasProperty('questiontext', $firstattempt);
        $this->assertObjectHasProperty('qtype', $firstattempt);
    }

    /**
     * Test that get_question_attempts returns empty array for non-existent quiz.
     */
    public function test_get_question_attempts_with_nonexistent_quiz(): void {
        // Instantiate the aitext class.
        $aitext = new aitext();

        // Call the get_question_attempts method with a non-existent quiz ID.
        $result = $aitext->get_question_attempts(99999);

        // Assert that the result is an empty array.
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
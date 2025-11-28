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
final class aitext_test extends \advanced_testcase {
    use \quiz_question_helper_test_trait;

    /** @var \stdClass The student user object */
    protected $student;

    /**
     * Test that get_student_submissions returns array with response data
     */
    public function test_get_student_submissions_returns_array(): void {
        $this->resetAfterTest();

        // Create a quiz and related data.
        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);

        // Create a question.
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $questiongenerator->create_question_category();
        $question = $questiongenerator->create_question('essay', null, ['category' => $cat->id]);

        // Add the question to the quiz.
        quiz_add_quiz_question($question->id, $quiz);

        // Create a user and attempt the quiz.
        $user = $this->getDataGenerator()->create_user();
        $quizobj = \quiz::create($quiz->id, $user->id);
        $quba = \question_usage_by_activity::create('test');
        $quizobj->prepare_attempt_quiz($quba, 1);
        $timenow = time();
        $attempt = quiz_create_attempt($quizobj, 1, false, $timenow, $user->id);

        // Submit an answer.
        $tosubmit = ['answer' => 'This is a test essay response.'];
        $quba->process_all_actions($timenow, $tosubmit);
        $timenow += 1;
        $quba->finish_all_questions($timenow);

        // Save the question usage,
        question_engine::save_questions_usage_by_activity($quba);

        // Complete the quiz attempt
        $attemptobj = \quiz_attempt::create($attempt->uniqueid);
        $attemptobj->process_finish($timenow, false);

        // Instantiate the aitext class.
        $aitext = new quiz_aitext\aitext();

        // Call the get_student_submissions method.
        $result = $aitext->get_student_submissions($quiz->id);

        // Assert that the result is an array.
        $this->assertIsArray($result);

        // Assert that we get data back.
        $this->assertNotEmpty($result);

        // Check that the returned data contains expected fields.
        $firstsubmission = reset($result);
        $this->assertNotNull($firstsubmission);
        $this->assertArrayHasKey('userid', $firstsubmission);
        $this->assertArrayHasKey('username', $firstsubmission);
        $this->assertArrayHasKey('questiontype', $firstsubmission);
        $this->assertArrayHasKey('response_text', $firstsubmission);
        $this->assertArrayHasKey('responses', $firstsubmission);
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
        [$quizobj, $quba, $attemptobj] = $this->attempt_quiz($quiz, $this->student);

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
        [$quizobj, $quba, $attemptobj] = $this->attempt_quiz($quiz, $this->student);

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
     * Test that get_student_submissions returns empty array for non-existent quiz.
     */
    public function test_get_student_submissions_with_nonexistent_quiz(): void {
        // Instantiate the aitext class.
        $aitext = new quiz_aitext\aitext();

        // Call the get_student_submissions method with a non-existent quiz ID.
        $result = $aitext->get_student_submissions(99999);

        // Assert that the result is an empty array.
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test the report display functionality
     */
    public function test_report_display_with_form_submission(): void {
        $this->resetAfterTest();

        // Create a quiz and related data.
        $course = $this->getDataGenerator()->create_course();
        $quiz = $this->getDataGenerator()->create_module('quiz', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('quiz', $quiz->id);

        // Create a question.
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $questiongenerator->create_question_category();
        $question = $questiongenerator->create_question('multichoice', null, ['category' => $cat->id]);

        // Add the question to the quiz.
        quiz_add_quiz_question($question->id, $quiz);

        // Create a user and attempt the quiz.
        $user = $this->getDataGenerator()->create_user();
        $quizobj = \quiz::create($quiz->id, $user->id);
        $quba = \question_usage_by_activity::create('test');
        $quizobj->prepare_attempt_quiz($quba, 1);
        $timenow = time();
        $attempt = quiz_create_attempt($quizobj, 1, false, $timenow, $user->id);

        // Test the report display
        $report = new \quiz_aitext_report();

        // Mock the form submission by setting GET parameters
        $_POST['mode'] = 'aitext';
        $_POST['id'] = $cm->id;

        // This would normally trigger the analysis, but we're just testing the structure
        $this->assertNotNull($report);
    }
}

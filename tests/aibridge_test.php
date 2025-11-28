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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/quiz/report/aitext/classes/aibridge.php');

/**
 * Unit tests for the aibridge class.
 *
 * @package    quiz_aitext
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class aibridge_test extends advanced_testcase {
    /**
     * Test that perform_request returns 'AI Feedback' in test environments.
     */
    public function test_perform_request_returns_ai_feedback_in_test_environment(): void {

        // Create a mock context ID.
        $contextid = 123;

        // Instantiate the aibridge class.
        $aibridge = new quiz_aitext\aibridge($contextid);

        // Create a test prompt.
        $prompt = "This is a test prompt for AI feedback.";

        // Call the perform_request method.
        $result = $aibridge->perform_request($prompt);

        // Assert that the result is 'AI Feedback'.
        $this->assertEquals('AI Feedback', $result);

        // Test with different purpose parameter.
        $airesult = $aibridge->perform_request($prompt, 'analysis');
        $this->assertEquals('AI Feedback', $airesult);
    }

    /**
     * Test that perform_request returns 'AI Feedback' when BEHAT_SITE_RUNNING is defined.
     */
    public function test_perform_request_returns_ai_feedback_when_behat_running(): void {
        // Set up BEHAT test environment constant.
        if (!defined('BEHAT_SITE_RUNNING')) {
            define('BEHAT_SITE_RUNNING', true);
        }

        // Create a mock context ID.
        $contextid = 456;

        // Instantiate the aibridge class.
        $aibridge = new quiz_aitext\aibridge($contextid);

        // Create a test prompt.
        $prompt = "This is another test prompt.";

        // Call the perform_request method.
        $result = $aibridge->perform_request($prompt, 'feedback');

        // Assert that the result is 'AI Feedback'.
        $this->assertEquals('AI Feedback', $result);
    }

    /**
     * Test constructor with different context IDs.
     */
    public function test_constructor_sets_context_id(): void {
        $contextid = 789;
        $aibridge = new quiz_aitext\aibridge($contextid);

        // Verify the object was created successfully.
        $this->assertInstanceOf('quiz_aitext\aibridge', $aibridge);
    }
}

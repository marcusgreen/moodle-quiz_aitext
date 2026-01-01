<?php
// This file is part of Moodle - http://moodle.org/
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

/**
 * Quiz report aitext main class.
 *
 * @package   quiz_aitext
 * @copyright 2025 Your Name
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use mod_quiz\local\reports\report_base;


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/report/reportlib.php');

/**
 * The quiz report aitext class.
 *
 * @copyright 2025 Your Name
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiz_aitext_report extends report_base {
    /**
     * Display the report.
     */
    public function display($quiz, $cm, $course) {
        global $OUTPUT, $PAGE, $DB;

        $this->print_header_and_tabs($cm, $course, $quiz, 'aitext');

        echo $OUTPUT->heading(get_string('pluginname', 'quiz_aitext'), 3);

        // Handle form submission.
        $analysisdata = null;
        xdebug_break();
        if ($this->is_form_submitted()) {
            // Process the form and get question attempts.
            $prompt = optional_param('prompt', '', PARAM_TEXT);
            $analysisdata = $this->process_analysis_request($quiz, $cm, $course, $prompt);
        }

        $templatecontext = [
            'pluginname' => get_string('pluginname', 'quiz_aitext'),
            'description' => get_string('templatereport', 'quiz_aitext'),
            'quizname' => $quiz->name,
            'coursename' => $course->fullname,
            'cmid' => $cm->id,
            'has_analysis' => !empty($analysisdata),
        ];

        // Add analysis data if available.
        if ($analysisdata) {
            $templatecontext = array_merge($templatecontext, $analysisdata);
        }

        echo $OUTPUT->render_from_template('quiz_aitext/report', $templatecontext);

        return true;
    }

    /**
     * Check if the form was submitted
     *
     * @return bool True if form was submitted
     */
    private function is_form_submitted() {
        return optional_param('submit', '', PARAM_TEXT) === 'submit' &&
               optional_param('mode', '', PARAM_ALPHA) === 'aitext' &&
               optional_param('id', 0, PARAM_INT) > 0;
    }

    /**
     * Process the analysis request and return data
     *
     * @param object $quiz The quiz object
     * @param object $cm The course module object
     * @param object $course The course object
     * @return array|null Analysis data or null if no data
     */
    private function process_analysis_request($quiz, $cm, $course, $prompt) {
        try {
            // Create aitext instance.
            $aitext = new \quiz_aitext\aitext();

            // Get student submissions for this quiz.
            $studentsubmissions = $aitext->get_student_submissions($quiz->id);

            if (empty($studentsubmissions)) {
                return [
                    'analysis_message' => get_string('no_attempts_found', 'quiz_aitext'),
                    'student_submissions' => [],
                ];
            }

            $submissioncount = count($studentsubmissions);
            // Recurse through studentSubmissions and collect comments.
            $comments = '';
            foreach ($studentsubmissions as $submission) {
                if (!empty($submission->comment)) {
                    $comments .= $submission->comment . "\n";
                }
            }
            $ctx = \context_module::instance($cm->id);
            $aibridge = new \quiz_aitext\aibridge($ctx->id);
            $commentsummary = $aibridge->perform_request($prompt. get_string('formathtml', 'quiz_aitext') .$comments);
            $commentsummary = $this->clean_ai_response($commentsummary);
            xdebug_break();
            return [
                'analysis_message' => get_string('analysis_complete', 'quiz_aitext'),
                'total_submissions_count' => $submissioncount,
                'comments' => $commentsummary,
            ];
        } catch (Exception $e) {
            return [
                'analysis_message' => get_string('analysis_error', 'quiz_aitext'),
                'error_details' => $e->getMessage(),
            ];
        }
    }

    /**
     * Clean the AI response by removing markdown code fences.
     *
     * @param string $text The text to clean.
     * @return string The cleaned text.
     */
    private function clean_ai_response($text) {
        // Remove the opening and closing markdown code fences.
        $text = preg_replace('/^```[a-zA-Z]*\n?/', '', $text);
        $text = preg_replace('/\n?```$/', '', $text);
        return trim($text);
    }

    /**
     * Process student submissions for template display
     *
     * @param array $submissions Raw student submissions data
     * @return array Processed data for template
     */
    private function process_student_submissions($submissions) {
        $processed = [];
        xdebug_break();
        foreach ($submissions as $submission) {
            // Format the response data for display.
            $responsetext = '';
            if (!empty($submission['responses'])) {
                foreach ($submission['responses'] as $varname => $value) {
                    if ($varname === 'answer' && !empty($value)) {
                        $responsetext = format_text($value, FORMAT_HTML);
                        break;
                    }
                }
                if (empty($responsetext)) {
                    // If no 'answer' field, show all response data.
                    $responsetext = '<pre>' . htmlspecialchars(print_r($submission['responses'], true)) . '</pre>';
                }
            }

            $processed[] = [
                'userid' => $submission['userid'],
                'username' => $submission['username'],
                'questionid' => $submission['questionid'],
                'questiontype' => $submission['questiontype'],
                'questiontext' => format_text($submission['questiontext'], FORMAT_HTML),
                'response_text' => $responsetext,
                'fraction' => $submission['fraction'],
                'maxmark' => $submission['maxmark'],
                'score_percentage' => $submission['maxmark'] > 0 ? round(($submission['fraction'] / $submission['maxmark']) * 100, 1) : 0,
                'quizattemptid' => $submission['quizattemptid'],
                'questionattemptid' => $submission['questionattemptid'],
            ];
        }

        return $processed;
    }

    /**
     * Process question attempts for template display (legacy method)
     *
     * @param array $attempts Raw question attempts data
     * @return array Processed data for template
     */
    private function process_question_attempts($attempts) {
        $processed = [];

        foreach ($attempts as $attempt) {
            $processed[] = [
                'id' => $attempt->id,
                'questiontext' => format_text($attempt->questiontext, FORMAT_HTML),
                'qtype' => $attempt->qtype,
                'state' => $attempt->state ?? get_string('unknownstate', 'quiz_aitext'),
                'fraction' => $attempt->fraction ?? 0,
            ];
        }

        return $processed;
    }
}

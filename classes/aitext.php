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

namespace quiz_aitext;

/**
 * Class aitext
 *
 * @package    quiz_aitext
 * @copyright  2025 2024 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class aitext {

    /**
     * Perform an AI request using the AI bridge
     *
     * @param int $contextid The context ID for AI requests
     * @param string $prompt The prompt to send to the AI
     * @param string $purpose The purpose of the request (default: 'feedback')
     * @return string The AI response
     */
    public function perform_ai_request(int $contextid, string $prompt, string $purpose = 'feedback'): string {
        $aibridge = new aibridge($contextid);
        return $aibridge->perform_request($prompt, $purpose);
    }

    /**
     * Get all question attempts data for a quiz instance
     *
     * @param int $quizid The quiz ID
     * @return array Array of question attempt data
     */
    public function get_question_attempts(int $quizid): array {
        global $DB;
        
        $sql = "SELECT qa.*, q.questiontext, q.qtype
                FROM {question_attempts} qa
                JOIN {question_attempt_steps} qas ON qa.id = qas.questionattemptid
                JOIN {question} q ON qa.questionid = q.id
                JOIN {quiz_attempts} quizat ON qa.questionusageid = quizat.uniqueid
                WHERE quizat.quiz = :quizid
                ORDER BY qa.id";
                
        return $DB->get_records_sql($sql, ['quizid' => $quizid]);
    }
}

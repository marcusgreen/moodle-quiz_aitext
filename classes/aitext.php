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
     * Get all student submissions for a quiz instance with grouped responses
     *
     * @param int $quizid The quiz ID
     * @return array Array of student submission data grouped by user and question
     */
    public function get_student_submissions(int $quizid): array {
        global $DB;

        $sql = "SELECT DISTINCT
                    quizat.id as quizattemptid,
                    quizat.userid,
                    u.firstname,
                    u.lastname,
                    qa.id as questionattemptid,
                    qa.questionid,
                    qa.questionusageid,
                    qa.fraction,
                    qa.maxmark,
                    q.qtype,
                    q.questiontext,
                    qas.id as stepid,
                    qasd.name as variablename,
                    qasd.value as responsevalue
                FROM {quiz_attempts} quizat
                JOIN {question_usage_by_activity} quba ON quizat.uniqueid = quba.id
                JOIN {question_attempts} qa ON quba.id = qa.questionusageid
                JOIN {question_attempt_steps} qas ON qa.id = qas.questionattemptid
                JOIN {question_attempt_step_data} qasd ON qas.id = qasd.attemptstepid
                JOIN {user} u ON qa.userid = u.id
                JOIN {question} q ON qa.questionid = q.id
                WHERE quizat.quiz = :quizid
                  AND quizat.state = 'finished'
                  AND quizat.preview = 0
                  AND qasd.name NOT LIKE '-%'     -- Exclude behavior vars
                  AND qasd.name NOT LIKE ':_%'    -- Exclude metadata
                ORDER BY quizat.userid, qa.questionid, qas.id, qasd.name";

        $rawdata = $DB->get_records_sql($sql, ['quizid' => $quizid]);

        // Group responses by user and question
        $grouped = [];
        foreach ($rawdata as $row) {
            $key = $row->userid . '_' . $row->questionid;
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'userid' => $row->userid,
                    'username' => $row->firstname . ' ' . $row->lastname,
                    'questionid' => $row->questionid,
                    'questiontype' => $row->qtype,
                    'questiontext' => $row->questiontext,
                    'fraction' => $row->fraction,
                    'maxmark' => $row->maxmark,
                    'responses' => [],
                    'quizattemptid' => $row->quizattemptid,
                    'questionattemptid' => $row->questionattemptid,
                    'stepid' => $row->stepid,
                ];
            }
            $grouped[$key]['responses'][$row->variablename] = $row->responsevalue;
        }

        return array_values($grouped);
    }

    /**
     * Get all question attempts data for a quiz instance (legacy method)
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

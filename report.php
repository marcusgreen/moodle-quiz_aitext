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
 * @package   quiz_report_aitext
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
        global $OUTPUT, $PAGE;

        $this->print_header_and_tabs($cm, $course, $quiz, 'aitext');

        echo $OUTPUT->heading(get_string('pluginname', 'quiz_aitext'), 3);

        $templatecontext = [
            'pluginname' => get_string('pluginname', 'quiz_aitext'),
            'description' => get_string('templatereport', 'quiz_aitext'),
            'quizname' => $quiz->name,
            'coursename' => $course->fullname,
            'cmid' => $cm->id,
        ];

        echo $OUTPUT->render_from_template('quiz_aitext/report', $templatecontext);

        return true;
    }
}

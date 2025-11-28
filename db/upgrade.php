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
 * Upgrade script for quiz report aitext plugin.
 *
 * @package   quiz_aitext
 * @copyright 2025 Your Name
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade function for the quiz report template plugin.
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool always true
 */
function xmldb_quiz_aitext_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2025112600) {
        // Add the aitext report to the quiz_reports table.
        $record = new stdClass();
        $record->name = 'aitext';
        $record->capability = 'mod/quiz:viewreports';
        $record->displayorder = 10000; // Put it at the end.

        // Check if it already exists.
        if (!$DB->record_exists('quiz_reports', ['name' => 'aitext'])) {
            $DB->insert_record('quiz_reports', $record);
        }

        upgrade_mod_savepoint(true, 2025112600, 'quiz', 'aitext');
    }

    return true;
}

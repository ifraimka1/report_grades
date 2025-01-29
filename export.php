<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Export grades sessions across multiple courses.
 *
 * @package     report_grades
 * @copyright   2024 Solomonov Ifraim mr.ifraim@yandex.ru
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_OUTPUT_BUFFERING', true);

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.'/mod/attendance/locallib.php');
require_once($CFG->dirroot.'/report/grades/lib.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/cohort/lib.php');

$cohortid = required_param('cohort', PARAM_INT);

$context = context_system::instance();
//require_capability('report/grades:view', $context);

$modattendance = $DB->get_record('modules', array('name' => 'attendance'));
$cohortmembers = get_cohort_members($cohortid);
$courses = [];

global $DB;
$sql = "SELECT DISTINCT c.*
            FROM {enrol} e
            JOIN {course} c
            WHERE e.enrol = 'cohort' AND e.customint1 = :cohortid";
$cohortcourses = $DB->get_records_sql($sql, ['cohortid' => $cohortid]);


require_once("$CFG->libdir/excellib.class.php");
$filename = clean_filename('Отчет '.$cohortname.' '.userdate(time(), '%Y-%m-%d').'.xls');

$workbook = new MoodleExcelWorkbook("-");

// Sending HTTP headers.
$workbook->send($filename);

// foreach ($exportdata as $data) {
//     $myxls = $workbook->add_worksheet($data->course);
//     // Format types.
//     $formatbc = $workbook->add_format();
//     $formatbc->set_bold(1);

//     $myxls->write(0, 0, get_string('course'), $formatbc);
//     $myxls->write(0, 1, $data->course);

//     $i = 3;
//     $j = 0;
//     foreach ($data->tabhead as $cell) {
//         // Merge cells if the heading would be empty (remarks column).
//         if (empty($cell)) {
//             $myxls->merge_cells($i, $j - 1, $i, $j);
//         } else {
//             $myxls->write($i, $j, $cell, $formatbc);
//         }
//         $j++;
//     }
//     $i++;
//     $j = 0;
//     foreach ($data->table as $row) {
//         foreach ($row as $cell) {
//             $myxls->write($i, $j++, $cell);
//         }
//         $i++;
//         $j = 0;
//     }
// }

$workbook->close();

exit;

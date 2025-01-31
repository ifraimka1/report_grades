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
 * @copyright   2025 Solomonov Ifraim mr.ifraim@yandex.ru
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_OUTPUT_BUFFERING', true);

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->dirroot.'/mod/attendance/locallib.php');
require_once($CFG->libdir.'/formslib.php');

require_login();

$cohortid = required_param('cohort', PARAM_INT);
$categorypath = required_param('semestr', PARAM_NOTAGS);

global $DB;

// Запрашиваем список студентов из группы.
$sql = "
    SELECT
        user.id AS id,
        user.lastname AS lastname,
        user.firstname AS firstname,
        user.email AS email,
        cohort.name AS cohort
    FROM {cohort} cohort
    JOIN {cohort_members} cm ON cm.cohortid = cohort.id
    JOIN {user} user ON user.id = cm.userid
    WHERE cohort.id = :cohortid
    ORDER BY user.lastname, user.firstname";
$params = ['cohortid' => $cohortid];
$students = $DB->get_records_sql($sql, $params);
$cohortname = $DB->get_field('cohort', 'name', ['id' => $cohortid]);

// Создаём файл excel.
require_once("$CFG->libdir/excellib.class.php");
$filename = clean_filename('Отчет по оценкам '.$cohortname.' '.userdate(time(), '%Y-%m-%d').'.xls');
$workbook = new MoodleExcelWorkbook("-");
// Sending HTTP headers.
$workbook->send($filename);

$myxls = $workbook->add_worksheet("Отчет");
// Задаем номера столбцов.
$columnstudent = 0;
$columncohort = 1;
$columnemail = 2;
// Format types.
$formatbc = $workbook->add_format();
$formatbc->set_bold(1);
// Пишем заголовки.
$myxls->write(0, $columnstudent, get_string('tabhead_student', 'report_grades'), $formatbc);
$myxls->write(0, $columncohort, get_string('tabhead_cohort', 'report_grades'), $formatbc);
$myxls->write(0, $columnemail, get_string('tabhead_email', 'report_grades'), $formatbc);
// Вносим студентов в таблицу.
$row = 1;
foreach ($students as $student) {
    $myxls->write($row, $columnstudent, $student->lastname.' '.$student->firstname);
    $myxls->write($row, $columncohort, $student->cohort);
    $myxls->write($row, $columnemail, $student->email);
    $student->row = $row; // Запоминаем, в какой строке студент.
    $row++;
}

$sql = "
    SELECT
        user.id AS userid,
        course.fullname AS coursename,
        grades.finalgrade AS grade
    FROM {cohort} cohort
    JOIN {cohort_members} cm ON cm.cohortid = cohort.id
    JOIN {user} user ON user.id = cm.userid
    JOIN {grade_grades} grades ON grades.userid = user.id
    JOIN {grade_items} items ON items.id = grades.itemid
    JOIN {course} course ON course.id = items.courseid
    JOIN {course_categories} categories ON categories.id = course.category
    WHERE cohort.id = :cohortid
      AND items.itemtype LIKE 'course'
      AND categories.path LIKE :categorypath
      AND grades.finalgrade NOT NULL
    ORDER BY course.fullname, user.lastname, user.firstname";
$params = ['cohortid' => $cohortid, 'categorypath' => $categorypath.'%'];
$grades = $DB->get_recordset_sql($sql, $params);

$currentcourse = '';
$column = 2;
foreach ($grades as $grade) {
    if (empty($currentcourse) || $currentcourse !== $grade->coursename) {
        $column++;
        $currentcourse = $grade->coursename;
        $myxls->write(0, $column, $currentcourse, $formatbc);
    }

    $myxls->write($students[$grade->userid]->row, $column, round($grade->grade, 2));
}

$grades->close();
$workbook->close();

exit;

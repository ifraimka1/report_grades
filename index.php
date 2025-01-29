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
 * Main plugin page
 *
 * @package     report_grades
 * @copyright   2024 Solomonov Ifraim mr.ifraim@yandex.ru
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/cohort/lib.php');

require_login();

admin_externalpage_setup('reportgrades', '', null, '', array('pagelayout'=>'report'));

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/report/grades/index.php'));
$PAGE->set_title(get_string('pluginname', 'report_grades'));
$PAGE->set_heading(get_string('pluginname', 'report_grades'));

$cohorts = cohort_get_cohorts($context->id, 0, 0);

$mform = new report_grades\form\export();

// Обрабатываем данные формы после отправки
if ($mform->is_submitted() && $mform->is_validated()) {
    $data = $mform->get_data();
    redirect(new moodle_url('/report/grades/export.php', ['cohort' => $data->cohort]));
}

echo $OUTPUT->header(); // Выводим заголовок страницы

// Отображаем форму
$mform->display();

echo $OUTPUT->footer(); // Выводим подвал страницы


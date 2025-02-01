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
 * Export form
 *
 * @package     report_grades
 * @copyright   2025 Solomonov Ifraim mr.ifraim@yandex.ru
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_grades\form;

class export extends \moodleform {
    public function definition() {
        $mform =& $this->_form;

        $mform->addElement('autocomplete', 'cohort', get_string('select_cohort', 'report_grades'), $this->get_cohort_options(), ['multiple' => true]);
        $mform->setType('cohort', PARAM_SEQUENCE);

        $mform->addElement('autocomplete', 'semestr', get_string('select_semestr', 'report_grades'), $this->get_semestr_options());
        $mform->setType('semestr', PARAM_NOTAGS);

        $this->add_action_buttons(false, get_string('export', 'report_grades'));
    }

    // Метод для получения глобальных групп.
    private function get_cohort_options() {
        global $CFG;
        require_once($CFG->dirroot.'/cohort/lib.php');

        $options = [];

        $context = \context_system::instance();
        $cohorts = cohort_get_cohorts($context->id, 0, 0, 'КТ');

        foreach ($cohorts['cohorts'] as $cohort) {
            $options[$cohort->id] = format_string($cohort->name);
        }

        return $options;
    }

    // Mетод для получения списка семестров.
    private function get_semestr_options() {
        global $DB;

        $options = [];

        $sql = "
            SELECT half.id AS id, year.name AS yearname, half.name AS halfname, half.path AS path
            FROM {course_categories} year
            JOIN {course_categories} half ON half.parent = year.id
            WHERE half.name LIKE '%семестр%'
            ORDER BY yearname, halfname DESC";
        $query = $DB->get_records_sql($sql);

        foreach ($query as $semestr) {
            $options[$semestr->path] = $semestr->yearname.' '.$semestr->halfname;
        }

        return $options;
    }
}

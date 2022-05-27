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
 * This is a one-line short description of the file.
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    report_coursestatistic
 * @category   report
 * @copyright  2021 Igor Grebennikov
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->libdir/formslib.php");
class statistic_form extends moodleform {
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('text', 'text_from', 'Номер курса "c" (минимальный 2)');
        $mform->setDefault('text_from', '2');
        $mform->addElement('text', 'text_to', 'Номер курса "до"');
        $mform->setDefault('text_to', '2');
        $mform->addElement('date_time_selector', 'time_from', 'Время "c" (используется для определения новизны элементов в курсе');
        //$mform->addElement('date_time_selector', 'time_to', 'Время "до"');
        $mform->addElement('text', 'name_of_first_file', 'Название файла информации о курсах');
        $mform->setDefault('name_of_first_file', 'Courses_info_');
        $mform->addElement('text', 'name_of_second_file', 'Название файла информации о дисциплинах курсов');
        $mform->setDefault('name_of_second_file', 'Courses_disciplines_');
        $mform->addElement('text', 'name_of_third_file', 'Название файла информации о модулях курса');
        $mform->setDefault('name_of_third_file', 'Courses_modules_');
        $this->add_action_buttons();

    }
}

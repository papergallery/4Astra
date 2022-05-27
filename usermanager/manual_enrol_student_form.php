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
 * @package    block_usermanager
 * @category   block
 * @copyright  2021 Igor Grebennikov
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once('lib.php');
require_once($CFG->libdir.'/formslib.php');

class manual_enrol_student extends moodleform {
    public $id;

    function definition() {
        $mform =& $this->_form;
        //Uncomment and test this feature when table with naprspec will be filled
//        $searchareas = \core_search\manager::get_search_areas_list(true);

        $courseid = $this->_customdata['courseid'];
        $facultets = $this->_customdata['facultets'];
        $num_course = $this->_customdata['num_course'];
        $edu_forms = $this->_customdata['edu_forms'];
        $edu_levels = $this->_customdata['edu_levels'];
        $edu_specialites = $this->_customdata['edu_specialites'];
        $streamyears = $this->_customdata['streamyears'];

        $mform->addElement('hidden', 'courseid', $courseid);

        $mform->addElement('html', get_string('select_student_faculty', 'block_usermanager'));
        $mform->addElement('select', 'fac', get_string('choose_faculty', 'block_usermanager'),
            $facultets);
        //Uncomment and test this feature when table with naprspec will be filled
//        $options = array(
//            'multiple' => false,
//            'noselectionstring' => get_string('search', 'search'),
//        );
//        $mform->addElement('autocomplete', 'naprspec', get_string('choose_edu_specialites', 'block_usermanager'),
//            $edu_specialites, $options);
        $mform->addElement('select', 'naprspec', get_string('choose_edu_specialites', 'block_usermanager'),
            $edu_specialites);
        $mform->addElement('select', 'year', get_string('choose_course', 'block_usermanager'),
            $num_course);
        $mform->addElement('select', 'stform', get_string('choose_edu_forms', 'block_usermanager'),
            $edu_forms);
        $mform->addElement('select', 'level', get_string('choose_edu_level', 'block_usermanager'),
            $edu_levels);
        $mform->addElement('select', 'streamyears', 'Выберите год потока/поступления',
            $streamyears);

        $this->add_action_buttons($cancel = false, $submitlabel = get_string('search', 'search'));

        $mform->registerNoSubmitButton('addotags');
        $otagsgrp = array();
        $otagsgrp[] =& $mform->createElement('submit', 'addotags', get_string('subscribe_students', 'block_usermanager'));
        $mform->addGroup($otagsgrp, 'otagsgrp', get_string('subscribe_students', 'block_usermanager'), array(' '), false);
        $mform->disable_form_change_checker();
    }

}

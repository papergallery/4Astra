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
require_once('../../config.php');
require_once('manual_enrol_student_form.php');
require_once('lib.php');
require_once($CFG->dirroot.'/group/lib.php');

$courseid = required_param('courseid', PARAM_RAW);
$course = $DB->get_record('course',array('id' => $courseid));
require_login($course, true);

$coursecontext = context_course::instance($courseid);
if (!has_capability('block/usermanager:manageuser', $coursecontext)) {
    die(get_string('access_error', 'block_usermanager'));
}

$PAGE->set_context($coursecontext);
$PAGE->set_pagelayout('standart');
$PAGE->set_url('/blocks/usermanager/group_autosearch_users.php', array('courseid' => $courseid));
$PAGE->navbar->add(get_string('pluginname', 'block_usermanager'));
$PAGE->set_title(get_string('pluginname', 'block_usermanager'));
$PAGE->set_heading(get_string('pluginname', 'block_usermanager'));
echo $OUTPUT->header();

//Get and prepare user data for search
$vsu_data = new \local_addition_vsu_data\general_data_vsu;
$toform = $vsu_data->get_all_vsu_data();
$toform['courseid'] = $courseid;

$mform = new manual_enrol_student(null, $toform);

if ($mform->is_cancelled()) {

} else if ($fromform = $mform->get_data()) {
    $mform->display();
    $ids = $vsu_data->ids;
    $data = new stdClass();
    $data->facultets = $toform['facultets'];
    $data->num_course = $toform['num_course'];
    $data->edu_forms = $toform['edu_forms'];
    $data->edu_levels = $toform['edu_levels'];
    $data->edu_specialites = $toform['edu_specialites'];
    $data->streamyears = $toform['streamyears'];
    $data = prepare_data_one($fromform, $data);
    $users = search_vsu_fields_users($ids, $data);
    $i = 1;
    $SESSION->users = '';
    $SESSION->group_name = '';
    $SESSION->group_name = $data->fac.' '.$data->naprspec.' '.$data->stform.' '.$data->edu_levels.' '.$data->streamyears;
    $SESSION->users = $users;
    echo '<h4>Найденные студенты:</h4>';
    foreach ($users as $user) {
        $list_of_user .= $i.'. '.$user->lastname.' '.$user->firstname.'</br>';
        $i++;
    }
    echo $list_of_user;

} elseif ($mform->no_submit_button_pressed()){
    $group_name = $SESSION->group_name;
    $users = $SESSION->users;
    if ($moodle_group_id = groups_get_group_by_name($courseid, $group_name)) {
        echo get_string('groupe_has_benn_created', 'block_usermanager') . ' ' . $group_name . '</br>';
    } else {
        $moodle_group_data = new stdClass();
        $moodle_group_data->courseid = $courseid;
        $moodle_group_data->name = $group_name;
        $moodle_group_data->descriptionformat = FORMAT_HTML;
        $moodle_group_id = groups_create_group($moodle_group_data);
        echo get_string('group_created', 'block_usermanager') . ' ' . $group_name . '</br>';
    }

    foreach ($users as $user) {
        enrol_user_custom($courseid, $user->id, $moodle_group_id);

        //Display report to teacher
        echo get_string('student', 'block_usermanager') . $user->lastname . ' ' . $user->firstname . ': ' ;
        if (is_enrolled($coursecontext, $user->id, '', true)) {
            echo get_string('enrolled', 'block_usermanager') . ' ';
            if (groups_is_member($moodle_group_id, $user->id)) {
                echo get_string('enrolled_to_the_group', 'block_usermanager');
            } else {
                echo get_string('enrolled_to_the_group_error', 'block_usermanager');
            }
        } else {
            echo $user->id . ' ' . get_string('enrol_error', 'block_usermanager');
        }
        echo '</br>';
    }
    echo '<b>' . get_string('subscribtion_complete', 'block_usermanager') . '</b></br>';
    $url = new moodle_url($CFG->wwwroot.'/course/view.php', array('id' => $courseid));
    echo html_writer::link($url,get_string('return_to_coursepage', 'block_usermanager'));

} else {
    $mform->display();
}

echo $OUTPUT->footer();
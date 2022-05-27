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
require_once($CFG->dirroot.'/group/lib.php');
require_once('lib.php');
require_once('enrol_student_groups_form.php');

//Should be remove when oracle integration will be removed
require_once('connect.php');

global $DB, $CFG;

$courseid = required_param('courseid', PARAM_RAW);
$course = $DB->get_record('course',array('id' => $courseid));
require_login($course, true);

//Only users with block/usermanager:manageuser permission can view page
$coursecontext = context_course::instance($courseid);
if (!has_capability('block/usermanager:manageuser', $coursecontext)) {
    die(get_string('access_error', 'block_usermanager'));
}

$PAGE->set_context($coursecontext);
$PAGE->set_pagelayout('standart');
$PAGE->navbar->add(get_string('pluginname', 'block_usermanager'));
$PAGE->set_title(get_string('pluginname', 'block_usermanager'));
$PAGE->set_heading(get_string('pluginname', 'block_usermanager'));
echo $OUTPUT->header();

//Get disciplins from contingent
//TODO: Should be removed when all data will be in moodle database
$disciplins_with_number = get_semestr_of_subject_oci_old($conn, $courseid);
$vsu_data = new \local_addition_vsu_data\general_data_vsu;
$ids = $vsu_data->ids;

$groups_of_users_per_disciplin = new stdClass();
$extra_groups_of_users_per_disciplin = new stdClass();
$moodle_group_name = new stdClass();
$moodle_group_description = new stdClass();

//TODO: Should be uncomment when all data will be in moodle database
//$sql = "SELECT * FROM mdl_block_vsucourse_new WHERE cid='".$course->id."' AND status='0';";
//$disciplins_with_number = $DB->get_records_sql($sql);

foreach ($disciplins_with_number as $disciplin_id => $disciplin) {

    //Disciplins have ' ' or '-' in 'specialisation' field
    if ($disciplin->specialisation == '-') {
        $groups_of_users_per_disciplin->{$disciplin_id} =
            search_vsu_fields_users_per_disciplin_without_specialisation($ids, $disciplin);
    } else {
        $groups_of_users_per_disciplin->{$disciplin_id} =
            search_vsu_fields_users_per_disciplin($ids, $disciplin);
    }
    //Geting extra groups without specialisation
    $extra_groups_of_users_per_disciplin->{$disciplin_id} =
        search_vsu_fields_users_per_disciplin_without_specialisation($ids, $disciplin);
    //Reformat students plan groups to academic groups
    $groups_of_users_per_disciplin = format_users_to_groups($ids, $groups_of_users_per_disciplin);
    $extra_groups_of_users_per_disciplin = format_users_to_groups($ids, $extra_groups_of_users_per_disciplin);

}

$toform_data = array("courseid" => $courseid,
                     "disciplins" => $disciplins_with_number,
                     "groups_of_users_per_disciplin" => $groups_of_users_per_disciplin,
                     "extra_groups_of_users_per_disciplin" => $extra_groups_of_users_per_disciplin);

$mform = new group_search_user_groups_form(null, $toform_data);

if ($mform->is_cancelled()) {

} else if ($fromform = $mform->get_data()) {
    echo '<b>' . get_string('error_notice', 'block_usermanager') . '</b></br>';
    //If user press "submit" button, all users in groups will be enrolled and
    //this action will be logged

    //Reformat $fromform array to usefull format
    $groups_selected = array();
    $i = 0;
    foreach ($fromform as $group_id => $selected) {
        if ($selected == '1') {
            $groups_selected[$i] = $group_id;
            $i++;
        }
    }

    //Subscription for students with secialities
    $error_count = 0;
    foreach ($groups_of_users_per_disciplin as $disciplin_id=>$groups_of_users) {
        foreach ($groups_of_users as $group_id => $group_of_user) {
            //Chech if group was selected
            $check = $disciplin_id . '_' . $group_id;
            if (in_array($check, $groups_selected, $strict = true)) {
                [$moodle_group_name, $moodle_group_description] = create_student_moodlegroup_vars($disciplins_with_number->{$disciplin_id}, $group_id);
                //Create group in moodle course
                if ($moodle_group_id = groups_get_group_by_name($courseid, $moodle_group_name)) {
                    echo get_string('groupe_has_benn_created', 'block_usermanager') . ' ' . $moodle_group_name . '</br>';
                } else {
                    $moodle_group_data = new stdClass();
                    $moodle_group_data->courseid = $courseid;
                    $moodle_group_data->idnumber = $moodle_group_id;
                    $moodle_group_data->name = $moodle_group_name;
                    $moodle_group_data->description = $moodle_group_description;
                    $moodle_group_data->descriptionformat = FORMAT_HTML;
                    $moodle_group_id = groups_create_group($moodle_group_data);
                    echo get_string('group_created', 'block_usermanager') . ' ' . $moodle_group_name;
                }
                //Create application report for logging in db (block_usermanager_applies)
                $application_report = new stdClass();
                $application_report->group_id = $moodle_group_id;
                $application_report->courseid = $courseid;
                $application_report->required_user = $USER->id;
                $application_report->status = 0000;
                $application_report->num_of_users = count((array)$group_of_user);

                if ($DB->record_exists('block_usermanager_applies', array('group_id' => $moodle_group_id))) {
                    $application = $DB->get_record('block_usermanager_applies', array('group_id' => $moodle_group_id), 'id');
                    $application_report->id = $application->id;
                    $application_id = $application->id;
                    $application_report->modified = time();
                    $DB->update_record('block_usermanager_applies', $application_report);
                } else {
                    $application_report->created = time();
                    $application_report->modified = 0;
                    $application_id = $DB->insert_record('block_usermanager_applies', $application_report);
                }

                foreach ($group_of_user as $user) {
                    //Create user report for logging in db (block_usermanager_users)
                    //and enrol user
                    $userid = $user->id;
                    $user_report = new stdClass();
                    $user_report->application_id = $application_id;
                    $user_report->user_id = $userid;
                    if (enrol_user_custom($courseid, $userid, $moodle_group_id)) {
                        //Status 01 - success
                        $user_report->status = 01;
                        $groups_of_users_per_disciplin->{$disciplin_id}->{$group_id}->{$userid}->enrolled = true;
                    } else {
                        //Status 00 - error
                        $user_report->status = 00;
                        $groups_of_users_per_disciplin->{$disciplin_id}->{$group_id}->{$userid}->enrolled = false;
                        $error_count += 1;
                        $error_log = new stdClass();
                        $error_log->application_id = $application_id;
                        $error_log->userid = $userid;
                        $error_log->time = time();
                        $DB->insert_record('block_usermanager_error_log', $error_log, $returnid=false,
                            $bulk=false);
                    }
                    $DB->insert_record('block_usermanager_users', $user_report);


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
            }
        }
    }

    //Subscription for students without specialities
    $error_count = 0;
    foreach ($extra_groups_of_users_per_disciplin as $disciplin_id=>$groups_of_users) {
        foreach ($groups_of_users as $group_id => $group_of_user) {
            //Chech if group was selected
            $check = $disciplin_id . '_' . $group_id . '_extra';
            if (in_array($check, $groups_selected, $strict = true)) {
                [$moodle_group_name, $moodle_group_description] = create_student_moodlegroup_vars($disciplins_with_number->{$disciplin_id}, $group_id);
                //Create group in moodle course
                if ($moodle_group_id = groups_get_group_by_name($courseid, $moodle_group_name)) {
                    echo get_string('groupe_has_benn_created', 'block_usermanager') . ' ' . $moodle_group_name . '</br>';
                } else {
                    $moodle_group_data = new stdClass();
                    $moodle_group_data->courseid = $courseid;
                    $moodle_group_data->idnumber = $moodle_group_id;
                    $moodle_group_data->name = $moodle_group_name;
                    $moodle_group_data->description = $moodle_group_description;
                    $moodle_group_data->descriptionformat = FORMAT_HTML;
                    $moodle_group_id = groups_create_group($moodle_group_data);
                    echo get_string('group_created', 'block_usermanager') . ' ' . $moodle_group_name;

                }
                //Create application report for logging in db (block_usermanager_applies)
                $application_report = new stdClass();
                $application_report->group_id = $moodle_group_id;
                $application_report->courseid = $courseid;
                $application_report->required_user = $USER->id;
                $application_report->status = 0000;
                $application_report->num_of_users = count((array)$group_of_user);

                if ($DB->record_exists('block_usermanager_applies', array('group_id' => $moodle_group_id))) {
                    $application = $DB->get_record('block_usermanager_applies', array('group_id' => $moodle_group_id), 'id');
                    $application_report->id = $application->id;
                    $application_id = $application->id;
                    $application_report->modified = time();
                    $DB->update_record('block_usermanager_applies', $application_report);
                } else {
                    $application_report->created = time();
                    $application_report->modified = 0;
                    $application_id = $DB->insert_record('block_usermanager_applies', $application_report);
                }

                foreach ($group_of_user as $user) {
                    //Create user report for logging in db (block_usermanager_users)
                    //and enrol user
                    $userid = $user->id;
                    $user_report = new stdClass();
                    $user_report->application_id = $application_id;
                    $user_report->user_id = $userid;
                    if (enrol_user_custom($courseid, $userid, $moodle_group_id)) {
                        //Status 01 - success
                        $user_report->status = 01;
                        $groups_of_users_per_disciplin->{$disciplin_id}->{$group_id}->{$userid}->enrolled = true;
                    } else {
                        //Status 00 - error
                        $user_report->status = 00;
                        $groups_of_users_per_disciplin->{$disciplin_id}->{$group_id}->{$userid}->enrolled = false;
                        $error_count += 1;
                        $error_log = new stdClass();
                        $error_log->application_id = $application_id;
                        $error_log->userid = $userid;
                        $error_log->time = time();
                        $DB->insert_record('block_usermanager_error_log', $error_log, $returnid=false,
                            $bulk=false);
                    }
                    $DB->insert_record('block_usermanager_users', $user_report);


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
            }
        }
    }


    //Processing errors and show button "return to course"

    echo '<b>'.get_string('subscribtion_complete', 'block_usermanager').'</b></br>';
    $url = new moodle_url($CFG->wwwroot.'/course/view.php', array('id' => $courseid));
    echo html_writer::link($url,get_string('return_to_coursepage', 'block_usermanager'));

} else {
    $mform->display();
}

echo $OUTPUT->footer();

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

function format_users_to_groups($ids, $users_from_disciplin) {
    //Reformat group from student plan to academic format
    global $DB;

    foreach ($users_from_disciplin as $disciplin_id=>$disciplin) {
        foreach ($disciplin as $user) {
            if (is_string($user->id)) {
                $userid = $user->id;
                $sql = 'select data from mdl_user_info_data where fieldid=' . $ids['groupname'] . ' and userid=' . $userid . ';';
                $academic_group = $DB->get_record_sql($sql)->data;
                $users_from_disciplin->{$disciplin_id}->{$academic_group}->{$userid} = $user;
                unset($users_from_disciplin->{$disciplin_id}->{$userid});
            }
        }
    }
    return $users_from_disciplin;

}

function search_vsu_fields_users($ids, $fields) {
    global $DB;
    
    //Get list of user
    //$ids - data from get_user_field_ids()
    //$fields - field value (example 1 - Физический факультет or 2 - 2020)
    /*
     * 7 -
     * 11 -
     * 12 -
     * 16 - fac (facultet; example 'Физический факультет')
     * 19 - stat (edu status; example эучится')
     * 21 -
     */

    $sql = "select userid from mdl_user_info_data where fieldid='".$ids['streamyear']."' and data = '".$fields->streamyears."'and userid in 
                (select userid from mdl_user_info_data where fieldid='19' and data='учится' and userid in
                    (select userid from mdl_user_info_data where fieldid = '".$ids['fac']."' and data = '".$fields->fac."' and userid in
                        (select userid from mdl_user_info_data where fieldid = '".$ids['naprspec']."' and data = '".$fields->naprspec."' and userid in 
                            (select userid from mdl_user_info_data where fieldid = '".$ids['year']."' and data = '".$fields->year."' and userid in
                                (select userid from mdl_user_info_data where fieldid = '".$ids['stform']."' and data = '".$fields->stform."' and userid in
                                    (select userid from mdl_user_info_data where fieldid = '".$ids['level']."' and data = '".$fields->edu_levels."'))))));";
    $user_ids = $DB->get_records_sql($sql);

        $users = new stdClass();
        $i = 0;

    foreach ($user_ids as $user_id) {
        $id = $user_id->userid;
        $users->$i = $DB->get_record('user', array('id' => $id), $fields = 'id,firstname,lastname');
        $i++;
    }
    
    return $users;
}

function prepare_data_one($fromform, $firstdata) {
    $data = new stdClass();
    $data->fac = $firstdata->facultets[$fromform->fac];
    $data->naprspec = $firstdata->edu_specialites[$fromform->naprspec];
    $data->year = $firstdata->num_course[$fromform->year];
    $data->stform = $firstdata->edu_forms[$fromform->stform];
    $data->edu_levels = $firstdata->edu_levels[$fromform->level];
    $data->streamyears = $firstdata->streamyears[$fromform->streamyears];

    return $data;
}

function enrol_user_custom($courseid, $userid, $group_id, $roleid=5, $duration=0, $method='manual') {

    global $DB;

    //Check if user already enrolled
    $context = context_course::instance($courseid);
    if (!is_enrolled($context, $userid, '', false)) {

        //Get enroll instance:
        $sql = "SELECT id FROM mdl_enrol WHERE courseid='" . $courseid . "' AND enrol='" . $method . "';";
        $result = $DB->get_records_sql($sql);
        if (!$result) {
            ///Not enrol associated (this shouldn't happen and means you have an error in your moodle database)
            return false;
        }
        foreach ($result as $unit) {
            $idenrol = $unit->id;
        }

        //Get the context
        $sql = "SELECT id FROM mdl_context WHERE contextlevel='50' AND instanceid='" . $courseid . "';"; ///contextlevel = 50 means course in moodle
        $result = $DB->get_records_sql($sql);
        if (!$result) {
            ///Again, weird error, shouldnt happen to you
        }
        foreach ($result as $unit) {
            $idcontext = $unit->id;
        }

        //Get variables from moodle. Here is were the enrolment begins:
        $time = time();
        //$ntime = $time + 60*60*24*$duration; //How long will it last enroled $duration = days, this can be 0 for unlimited.
        $ntime = 0;
        if (!$DB->record_exists('user_enrolments', array('enrolid' => $idenrol, 'userid' => $userid))) {
            $sql = "INSERT INTO mdl_user_enrolments (status, enrolid, userid, timestart, timeend, timecreated, timemodified)
VALUES (0, $idenrol, $userid, '$time', '$ntime', '$time', '$time')";
            if ($DB->execute($sql) === TRUE) {
                //return true;
            } else {
                ///Manage sql error
                return false;
            }
        }

        $sql = "INSERT INTO mdl_role_assignments (roleid, contextid, userid, timemodified)
VALUES ($roleid, $idcontext, '$userid', '$time')";
        if ($DB->execute($sql) === TRUE) {
            //return true;
        } else {
            //Manage errors
            return false;
        }
    }

    //add users into group
    //$group = groups_get_group_by_idnumber($courseid, $group_id);
    if (!groups_is_member($group_id, $user->id)) {
        if (groups_add_member($group_id, $userid)) {
            return true;
        } else {
            return false;
        }
    }
}

function create_student_moodlegroup_vars($disciplin, $group_id) {
    $moodle_group_name = '';
    $moodle_group_name .= $disciplin->faculty.' ';
    $moodle_group_name .= $disciplin->speciality.' ';
    $moodle_group_name .= $disciplin->step;
    $moodle_group_name .= ' '.$group_id.' группа ';
    $moodle_group_name .= $disciplin->year.'г. ';
    $moodle_group_name .= $disciplin->st_form;

    $moodle_group_description = '';
    $moodle_group_description .= $group_id . ' группа ' . '</br>';
    $moodle_group_description .= $disciplin->faculty . '</br>';
    $moodle_group_description .= $disciplin->speciality_code . '</br>';
    $moodle_group_description .= $disciplin->speciality . '</br>';
    $moodle_group_description .= $disciplin->specialisation . '</br>';
    $moodle_group_description .= $disciplin->step . '</br>';
    $moodle_group_description .= $disciplin->year . '</br>';

    return array($moodle_group_name, $moodle_group_description);
}

function search_vsu_fields_users_per_disciplin($ids, $disciplin) {
    global $DB;

    //Get list of user
    //$field_ids - field ids (example 1 - facultet or 2 - year)
    //$fields - field value (example 1 - Физический факультет or 2 - 2020)
    /*
     * 7 -
     * 11 -
     * 12 -
     * 16 - fac (facultet; example 'Физический факультет')
     * 19 - stat (edu status; example эучится')
     * 21 -
     */

    $sql = "select userid from mdl_user_info_data where fieldid='19' and data='учится' and userid in
                (select userid from mdl_user_info_data where fieldid = '".$ids['level']."' and data = '".$disciplin->step."' and userid in
                    (select userid from mdl_user_info_data where fieldid = '".$ids['specialityCode']."' and data = '".$disciplin->speciality_code."' and userid in 
                        (select userid from mdl_user_info_data where fieldid = '".$ids['naprspec2']."' and data = '".$disciplin->speciality."' and userid in
                            (select userid from mdl_user_info_data where fieldid = '".$ids['stform']."' and data = '".$disciplin->st_form."' and userid in
                                (select userid from mdl_user_info_data where fieldid = '".$ids['profile']."' and data = '".$disciplin->specialisation."' and userid in
                                    (select userid from mdl_user_info_data where fieldid = '".$ids['streamyear']."' and data = '".$disciplin->year."'))))));";
    $user_ids = $DB->get_records_sql($sql);

    $users = new stdClass();

    foreach ($user_ids as $user_id) {
        $id = $user_id->userid;
        $users->{$id} = $DB->get_record('user', array('id' => $id), 'id,firstname,lastname');
    }

    return $users;
}

function search_vsu_fields_users_per_disciplin_without_specialisation($ids, $disciplin) {
    global $DB;

    $sql = "select userid from mdl_user_info_data where fieldid='19' and data='учится' and userid in
                (select userid from mdl_user_info_data where fieldid = '".$ids['level']."' and data = '".$disciplin->step."' and userid in
                    (select userid from mdl_user_info_data where fieldid = '".$ids['specialityCode']."' and data = '".$disciplin->speciality_code."' and userid in 
                            (select userid from mdl_user_info_data where fieldid = '".$ids['stform']."' and data = '".$disciplin->st_form."' and userid in
                                (select userid from mdl_user_info_data where fieldid = '".$ids['naprspec2']."' and data = '".$disciplin->speciality."' and userid in
                                    (select userid from mdl_user_info_data where fieldid = '".$ids['streamyear']."' and data = '".$disciplin->year."')))));";
    $user_ids = $DB->get_records_sql($sql);

    $users = new stdClass();

    foreach ($user_ids as $user_id) {
        $id = $user_id->userid;
        $users->{$id} = $DB->get_record('user', array('id' => $id),  'id,firstname,lastname');
    }

    return $users;
}

function get_semestr_of_subject_oci_old($conn, $courseid) {
    /*
     * Geting semestr information from oracle DB
     * Used code from vsucourse plugin!
     * File connect.php was created locally for usermanager plugin
     * When this data will store in moodle, get_semestr_of_subject_oci_old() must be deleted
     */
    global $DB;

    $rows = $DB->get_records_sql("SELECT * FROM {block_vsucourse_new} WHERE cid = '".$courseid."' and status = '0'");

    $result = new stdClass();

    foreach($rows as $row) {
        if ($row->specialisation == ""){
            $row->specialisation = '-';
        }
        $sql = '';
        if ($row->specialisation == "-") {
            $sql = "select * from contingent.moodle_subject_view WHERE 
							SUBJ_CODE = '" . $row->subj_code . "' and 
							SUBJ_NAME = '" . $row->subj_name . "' and
							ST_FORM = '" . $row->st_form . "' and
							faculty = '" . $row->faculty . "' 
							
							and STUDY_YEAR = (select max(STUDY_YEAR) from contingent.moodle_subject_view WHERE 
												SUBJ_CODE = '" . $row->subj_code . "' and 
												SUBJ_NAME = '" . $row->subj_name . "' and
												ST_FORM = '" . $row->st_form . "' and
												faculty = '" . $row->faculty . "' 
												
							)
							";
        } else {
            $sql = "select * from contingent.moodle_subject_view WHERE 
							SUBJ_CODE = '" . $row->subj_code . "' and 
							SUBJ_NAME = '" . $row->subj_name . "' and
							ST_FORM = '" . $row->st_form . "' and
							faculty = '" . $row->faculty . "' and
							SPECIALISATION = '" . $row->specialisation . "'
							and STUDY_YEAR = (select max(STUDY_YEAR) from contingent.moodle_subject_view WHERE 
												SUBJ_CODE = '" . $row->subj_code . "' and 
												SUBJ_NAME = '" . $row->subj_name . "' and
												ST_FORM = '" . $row->st_form . "' and
												faculty = '" . $row->faculty . "' and
												SPECIALISATION = '" . $row->specialisation . "'
							)
							";

        }

        $stid = oci_parse($conn, $sql);
        $r = oci_execute($stid);
        while ($row_out = oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS)) {
            $subj_id = $row_out['SUBJ_ID'];
            $sql_s = "select * from contingent.moodle_study_work_view WHERE subj_id = '" . $subj_id . "' ORDER BY SEMESTER,HOURS_COUNT ";
            $stid_s = oci_parse($conn, $sql_s);
            $r_s = oci_execute($stid_s);
            while($row_study = oci_fetch_array($stid_s, OCI_ASSOC+OCI_RETURN_NULLS)){
                $result->{$subj_id} = $row;
                /*
                $year = (int)date('Y');
                $year_per_semestr = $year - intdiv((int)$row_study['SEMESTER'], 2);
*/
                $years = array();

                if (date('n') < 7){
                    array_push($years, (date('Y') - floor(($row_study['SEMESTER'] + 1) / 2)));
                } else {
                    array_push($years, (date('Y') + 1 - floor(($row_study['SEMESTER'] + 1) / 2)));
                }
/*
                    if ((int)date('m') < 8) {
                    $year_per_semestr -= 1;
                }
                $result->{$subj_id}->semestr = (int)$row_study['SEMESTER'];
                //$result->{$subj_id}->year = $year_per_semestr;
                $result->{$subj_id}->year = (int)$row_study['STUDY_YEAR'];
*/
                $result->{$subj_id}->semestr = (int)$row_study['SEMESTER'];
            }

            $NeedYears = array();
            $NeedYears = array_unique($years);

            foreach ($NeedYears as $need_yaer){

                if (date('n') < 7){
                    if (date('Y') == $need_yaer) {
                        //$result->{$subj_id}->year = $need_yaer;
                    }
                }
            }
            $result->{$subj_id}->year = $need_yaer;
        }
    }
    return $result;
}

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

require '../../config.php';
require "statistic_form.php";

global $DB;

$PAGE->set_url('/blocks/coursestatistic/statistic.php');
$PAGE->set_title('Статистика курса');
$PAGE->set_heading('Статистика курса');
echo $OUTPUT->header();

require_login();

$mform = new statistic_form();

if ($mform->is_cancelled()) {

} else if ($fromform = $mform->get_data()) {

    //first file -
    $first_filename = "reports/$fromform->name_of_first_file".date("Y-m-d_H:i",time()).".csv";
    $first_file = fopen($first_filename, "w");
    //second file -
    $second_filename = "reports/$fromform->name_of_second_file".date("Y-m-d_H:i",time()).".csv";
    $second_file = fopen($second_filename, "w");

    //necessary vars
    $course_from = (int)$fromform->text_from;
    $course_to = (int)$fromform->text_to;
    $timefrom = (int)$fromform->time_from;
    $timeto =(int)$fromform->time_to;

    //get course information
    $sql = "SELECT *
            FROM mdl_course
            WHERE id BETWEEN $course_from AND $course_to;";
    $courses = $DB->get_records_sql($sql);

    //analyse course information
    foreach ($courses as $course) {
        $third_filename = "reports/$fromform->name_of_third_file($course->id)" . date("Y-m-d_H:i", time()) . ".csv";
        $third_file = fopen($third_filename, "w");
        $context = context_course::instance($course->id);

        //number of disciplins
        $sql = "SELECT COUNT(DISTINCT specialisation) 
                FROM mdl_block_vsucourse_new 
                WHERE cid='2';";
        //$num_of_disciplin = $DB->get_record_sql($sql)->count;

        //number of students, enter once or more
        $sql = "SELECT COUNT(DISTINCT userid) AS enter_once 
                FROM mdl_logstore_standard_log 
                WHERE userid > 1 AND target='course' AND action='viewed' AND courseid='$course->id';";
        $enter_once = $DB->get_record_sql($sql)->enter_once;

        //number of entrance at all in course
        $sql = "SELECT COUNT(userid) AS enter_at_all 
                FROM mdl_logstore_standard_log 
                WHERE userid > 1 AND courseid='$course->id' AND (target='course' OR target='course_module');";
        $enter_at_all = $DB->get_record_sql($sql)->enter_at_all;

        //count all users in course
        $num_of_user = count_enrolled_users($context);
        //count average number of entrance per user
        $avg_enterance = $enter_at_all / $num_of_user;

        //count number of question on course
        $num_of_question = '';

        //count number of new question in course (since first data in form)
        $num_of_new_question = '';

        //get cafedra id (course category)
        $category_cafedra = $DB->get_record('course_categories', array('id' => $course->category), 'name,parent');
        $category = $category_cafedra->name;
        $cafedra = $DB->get_record('course_categories', array('id' => $category_cafedra->parent), 'name')->name;

        //get code napravlenija (napravlenijas)
        $sql = "SELECT DISTINCT speciality_code, speciality 
                FROM mdl_block_vsucourse_new 
                WHERE cid='$course->id';";
        //$code_napravls = $DB->get_records_sql($sql);
        $count = count($code_napravls);
        if ($count > 1) {
            foreach ($code_napravls as $napr) {
                if ($code_napravl == null) {
                    $code_napravl = $napr->speciality_code.'_'.$napr->speciality;
                } else {
                    $code_napravl = $code_napravl.'/'.$napr->speciality_code.'_'.$napr->speciality;
                }
            }
        } else {
            $code_napravl = $napr->speciality_code.'_'.$napr->speciality;
        }

        //get edu form (forms)
        $sql = "SELECT DISTINCT st_form 
                FROM mdl_block_vsucourse_new 
                WHERE cid='$course->id';";
        //$forms_edu = $DB->get_record_sql($sql);
        $count = count($forms_edu);
        if ($count > 1) {
            foreach ($forms_edu as $form) {
                if ($forms_edu == null) {
                    $form_edu = $form.'/';
                } else {
                    $form_edu = $form_edu.'/'.$form;
                }
            }
        } else {
            $form_edu = $forms_edu;
        }

        //get disciplin (disciplins)
        $sql = "SELECT DISTINCT subj_code, subj_name 
                FROM mdl_block_vsucourse_new 
                WHERE cid='$course->id';";
        //$disciplins = $DB->get_records_sql($sql);
        $count = count($disciplins);
        if ($count > 1) {
            foreach ($disciplins as $discip) {
                if ($discip == null) {
                    $disciplin = $discip->subj_code.'_'.$discip->subj_name;
                } else {
                    $disciplin = $disciplin.'/'.$discip->subj_code.'_'.$discip->subj_name;
                }
            }
        } else {
            $disciplin = $discip->subj_code.'_'.$discip->subj_name;
        }

        $half_of_year = '';  //TODO:не найдено?

        //write output to first and second file
        fputcsv($first_file, array($course->id, $course->shortname, $course->fullname, $num_of_disciplin, $enter_once,
            $enter_at_all, $avg_enterance));
        //echo var_dump(array($course->id, $course->shortname, $course->fullname, $num_of_disciplin, $enter_once,
            //$enter_at_all, $avg_enterance));
        fputcsv($second_file, array($course->id, $course->shortname, $course->fullname,
            $category, $cafedra, $form_edu, $disciplin, $half_of_year));
        //echo var_dump(array($course->id, $course->shortname, $course->fullname,
            //$category, $cafedra, $form_edu, $disciplin, $half_of_year));

        //get modules information and count new modules
        $modules = $DB->get_records('course_modules', array("course"=>$course->id), $sort = '', 'id,module,added');
        $i = 1;
        foreach ($modules as $module) {
            $type_of_module = $DB->get_record('modules', array('id' => $module->module), 'name')->name;
            $date = date('Y-m-d h:i', $module->added);
            if ((int)$module->added > $timefrom) {
                $new_element = 'Новый';
            } else {
                $new_element = 'Старый';
            }
            //get and count all access to course
            $sql = "SELECT COUNT(userid) 
                    FROM mdl_logstore_standard_log 
                    WHERE objectid=$module->id AND courseid=$course->id;";
            $access_at_all = $DB->get_record_sql($sql)->count;

            $method_of_mark = '';  //TODO: Какие матоды оцениваня?

            //write output to third file
            fputcsv($third_file, array($i, $type_of_module, $date,
                $new_element, $access_at_all, $method_of_mark));
            //echo var_dump(array($i, $type_of_module, $date,
                //$new_element, $access_at_all, $method_of_mark));
            $i = $i + 1;
        }
        fclose($third_file);
        echo 'Файл '.$third_filename.' создан</br>';
    }
    fclose($second_file);
    echo 'Файл '.$second_filename.' создан</br>';
    fclose($first_file);
    echo 'Файл '.$first_filename.' создан</br>';
    echo 'Отчёты созданы</br></br>';



} else {

    }

    //$mform->set_data($toform);
    $mform->display();




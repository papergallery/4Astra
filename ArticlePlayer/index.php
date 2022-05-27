<?php
/**
 * User: densh
 * Date: 20.10.2021
 * Time: 14:19
 */

require_once('../../config.php');

$id = required_param('id', PARAM_INT);           // Course ID

// Ensure that the course specified is valid
if (!$course = $DB->get_record('course', array('id'=> $id))) {
    print_error('Course ID is incorrect');
}

require_course_login($course, true);
$PAGE->set_pagelayout('incourse');

$strpage         = get_string('modulename', 'longread');
$strpages        = get_string('modulenameplural', 'longread');
$strname         = get_string('name');
$strintro        = get_string('moduleintro');
$strlastmodified = get_string('lastmodified');

$PAGE->set_url('/mod/longread/index.php', array('id' => $course->id));
$PAGE->set_title($course->shortname.': '.$strpages);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strpages);
echo $OUTPUT->header();
echo $OUTPUT->heading($strpages);

echo "index php";

echo $OUTPUT->footer();

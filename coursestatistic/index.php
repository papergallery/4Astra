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

require_once(__DIR__."/../../config.php");
require_once($CFG->libdir."/adminlib.php");

require_login(null, false);
$PAGE->set_url(new moodle_url('/report/coursestatistic/index.php'));

$systemcontext = context_system::instance();
$personalcontext = context_user::instance($USER->id);

$showallusers = false;

if (is_siteadmin()) {
    // User must be site admin or manager - can see records for all users.
    $PAGE->set_context($systemcontext);
    $PAGE->set_pagelayout('report');
    $PAGE->navbar->add(get_string('pluginname', 'block_usermanager'));
    $PAGE->set_title(get_string('pluginname', 'block_usermanager'));
    $PAGE->set_heading(get_string('pluginname', 'block_usermanager'));
    echo $OUTPUT->header();

    $url = new moodle_url('/report/coursestatistic/weights.php');
    echo html_writer::link($url, get_string('set_up_weights', 'report_coursestatistic'));
    //admin_externalpage_setup("coursestatistic", "", null, "", array("pagelayout" => "report"));
    //$defaultsort = "u.firstname";

} else if (has_capability('report/coursestatistic:viewweights', $systemcontext)) {
    // User is likely a parent/mentor - can see the student's records only.
    $PAGE->set_pagelayout('report');
    $PAGE->set_context($personalcontext);
    print('qq');
    //$defaultsort = "c.fullname";
} else {
    //print_error(get_string('couldnotgetuseremail', 'auth_googleoauth2'));
    print('qqq');
}

echo 'Инициализация скрипта';
$modules = $DB->get_records('modules', $conditions=null, $sort = 'name',
    $fields = 'name');
echo 'Данные из course_modules получены';
$new_modules = new stdClass();
$i = 0;
foreach ($modules as $module) {
    $new_modules->{$i}->name = $module->name;
    $new_modules->{$i}->weight = 1;
    $new_modules->{$i}->created = time();
    $i++;
}
echo 'Данные из course_modules преобразованы';
echo var_dump($new_modules);
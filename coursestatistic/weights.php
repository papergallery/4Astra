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

require_once('../../config.php');
require_once('weights_form.php');

global $DB;

$context = context_system::instance();
if (!is_siteadmin()) {
    die(get_string('access_error', 'report_coursestatistic'));
}

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_url('/report/coursestatistic/weights.php');
$PAGE->navbar->add(get_string('pluginname', 'report_coursestatistic'));
$PAGE->set_title(get_string('pluginname', 'report_coursestatistic'));
$PAGE->set_heading(get_string('pluginname', 'report_coursestatistic'));

echo $OUTPUT->header();

if (!has_capability('report/coursestatistic:viewweights', $context)) {
    echo 'error';
} else {
    $mform = new weights_form();
    $mform->display();
}

echo $OUTPUT->footer();

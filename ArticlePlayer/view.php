<?php

require('../../config.php');
require_once('lib.php');
//require_once($CFG->libdir.'/completionlib.php');

global $DB, $PAGE, $OUTPUT;

$PAGE->requires->css(new moodle_url('/local/edu/colorpicker/css/colorpicker.css'));
$PAGE->requires->css(new moodle_url('/local/edu/colorpicker/style.css'));
$PAGE->requires->js_call_amd('mod_longread/script', 'initColorPicker');

$id     = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', 'view', PARAM_TEXT);

if ( !$cm = get_coursemodule_from_id('longread', $id) )
{
    print_error('invalidcoursemodule');
}

$longread = $DB->get_record('longread', ['id' => $cm->instance], '*', MUST_EXIST);

$course   = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

require_course_login($course, true, $cm);

$context = context_module::instance($cm->id);
$PAGE->set_context($context);
//require_capability('mod/longread:view', $context);

longread_view($longread, $course, $cm, $context);

$PAGE->set_url('/mod/longread/view.php', ['id' => $cm->id, 'action' => $action]);

$PAGE->set_title($longread->name);
$PAGE->set_heading($course->fullname);

$iseditor = has_capability('mod/longread:edit', $context);

echo $OUTPUT->header();

if( $iseditor )
{
    if ( $action != 'delete' )
    {
        $row[] = new tabobject('view', new moodle_url($PAGE->url, ['action' => 'view']), "Просмотр лонгрида");
        $row[] = new tabobject('edit', new moodle_url($PAGE->url, ['action' => 'edit']), "Редактирование разделов");
        $row[] = new tabobject('showScores', new moodle_url($PAGE->url, ['action' => 'showScores']), "Результаты теста");

        echo $OUTPUT->tabtree($row, $action);
    }

    echo \mod_longread\longread::render($longread, $action);
}
else
{
    echo \mod_longread\longread::render($longread);
    echo '<style>.card { border: none; }.card-body { padding: 0; }</style>';
}

$PAGE->requires->js_call_amd('mod_longread/script', 'init');
//$PAGE->requires->js_call_amd('mod_longread/star_rating', 'star_rating');

$mysetting = get_config("mod_longread");

if($mysetting->likes == 1){
    $PAGE->requires->js_call_amd('local_likes/like', 'init');
    if( $action == 'view' ) echo local_likes\like::get_render('longread', $longread->id, true, '', 2);
}

if( $mysetting->notes && $iseditor && $action == 'view' ) {
    $PAGE->requires->js_call_amd('mod_longread/notes', 'notes');
}

if($mysetting->yandexspeech == 1 && $action == 'view' ) {
    $PAGE->requires->js_call_amd('mod_longread/speech', 'speech');
}


echo $OUTPUT->footer();
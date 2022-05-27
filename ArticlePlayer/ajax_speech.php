<?php

define('AJAX_SCRIPT', true);
require_once('../../config.php');

global $DB, $USER, $CFG;
require_once ($CFG->libdir."/filelib.php");
$action = required_param('action', PARAM_TEXT);
switch ($action) {

    case 'speechload':
        //выгрузга последнего прослушенного спича пользователем
        $cmid = required_param('coursemoduleid',PARAM_INT );
        echo json_encode(\mod_longread\longread::getSpeeches($cmid));
        break;

    case 'audioload':
        //формирование url
        $moduleid = required_param('coursemoduleid', PARAM_INT);
        $noteid = required_param('noteid', PARAM_TEXT);
        echo json_encode(\mod_longread\longread::getSpeechFile($moduleid, $noteid));
        break;

    case 'saveuser':
        //запись в базу последнего прослушанного спича ползователем
        $moduleid = required_param('coursemoduleid', PARAM_INT);
        $noteid = required_param('currentidnote', PARAM_TEXT);
        \mod_longread\longread::getSchUsr($moduleid, $noteid);
        break;

    case 'audioloadbulk':
        //закачка всех несуществующих спичей
        $noteidarray = required_param('noteidarray', PARAM_TEXT);
        $moduleid = required_param('moduleid', PARAM_TEXT);
        echo json_encode(\mod_longread\longread::getSpeechFiles($noteidarray, $moduleid));
        break;

}

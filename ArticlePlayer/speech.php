<?php

global $DB, $CFG, $USER;

require_once("../../config.php");
require_once ($CFG->libdir."/filelib.php");

$text = optional_param('SendText','', PARAM_TEXT);
$noteid = optional_param('SendNoteid','', PARAM_TEXT);
$moduleid = optional_param('moduleid', 0, PARAM_INT);
$context = \context_module::instance($moduleid);
$key = '))))))))))))))))))))))))))))))))))))))';
$url = "https://tts.api.cloud.yandex.net/speech/v1/tts:synthesize";
$curl = curl_init($url);
$post = "text=".urlencode($text)."&lang=ru-RU";
//$file = fopen('/var/moodledata/temp/'.$noteid,"wb");
curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_HTTPHEADER => array(
        "Authorization: Api-Key $key",
    ),
    CURLOPT_POSTFIELDS => $post
));
//curl_setopt($curl, CURLOPT_FILE, $file);
$result = curl_exec($curl);
if (curl_errno($curl)) {
    print "Error: " . curl_error($curl);
}
if (curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200) {
    $decodedResponse = json_decode($result, true);
    echo "Error code: " . $decodedResponse["error_code"] . "\r\n";
    echo "Error message: " . $decodedResponse["error_message"] . "\r\n";
} else {
    make_temp_directory('yandexspeech', false);
//    check_dir_exists($CFG->tempdir.'/yandexspeech/', true, true);
    $tempfile = $CFG->tempdir. '/yandexspeech/speech.ogg';
//    $tempfile = $CFG->tempdir. '/speech.ogg';
    file_put_contents($tempfile, $result);
}
//fclose($file);
//save speech file
$ob = new stdClass();
$ob->noteid = $noteid;
$ob->moduleid = $moduleid;
$ob->timeinsert = time();

if(!$DB->record_exists('longread_speech', array('noteid' => $noteid))){
    $itemid = $DB->insert_record('longread_speech', $ob);
}else{
    $itemid = $DB->get_record('longread_speech', array('noteid' => $noteid), 'id') -> id;
    $ob -> id = $itemid;
    $DB->update_record('longread_speech', $ob);
}
$fs = get_file_storage();
$filedata = [
    'contextid' => $context->id,
    'component' => "mod_longread",
    'filearea' => "speech",
    'itemid' => $itemid,
    'userid' => $USER->id,
    'filepath' => "/",
    'filename' => "speech-{$itemid}.ogg",
    'timecreated'=>time(),
    'timemodified' => time()
];
//print_object($tempfile);
//print_object($CFG->dataroot . "/temp/speech.ogg");
//print_object($filedata);
//exit;
$fs->create_file_from_pathname($filedata, $tempfile);


//@unlink($tempfile);
curl_close($curl);

echo \mod_longread\longread::getSpeechFile($moduleid, $noteid);

?>
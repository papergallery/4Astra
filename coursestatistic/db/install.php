<?php
require(__DIR__ . '/../../config.php');

global $DB;

echo 'Инициализация скрипта';
$modules = $DB->get_records('course_modules', $conditions=null, $sort = 'name',
                            $fields = 'name');
echo 'Данные из course_modules получены';
$new_modules = new stdClass();
$i = 0;
foreach ($modules as $module) {
    $new_modules->{$i}->name = $module->name;
    $new_modules->{$i}->weight = 1;
    $new_modules->{$i}->created = time();
}
echo 'Данные из course_modules преобразованы';
$DB->insert_records('coursereport_weights', $new_modules);
echo 'Данные записаны в coursereport_weights';

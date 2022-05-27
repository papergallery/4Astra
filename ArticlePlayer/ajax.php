<?php
require_once("../../config.php");
header('Content-Type: application/json');

$action = required_param('action', PARAM_TEXT);

print \mod_longread\ajax::run($action);
<?php

$lang = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/lang/en.json"), true);
require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/app.php";
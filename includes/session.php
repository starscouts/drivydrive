<?php

$isLoggedIn = false;
global $_PROFILE;

$token = $_POST["_session"] ?? $_GET["_session"] ?? $_COOKIE['DRIVYDRIVE_SESSION_TOKEN'] ?? null;

if (isset($token)) {
    if (!(str_contains($token, "/") || trim($token) === "" || trim($token) === "." || trim($token) === "..")) {
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/includes/tokens/" . str_replace("/", "", $token))) {
            $data = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/tokens/" . str_replace("/", "", $token)), true);
            $_PROFILE = $data;
            $isLoggedIn = true;
        }
    }
}
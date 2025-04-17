<?php

$selectedLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);

switch ($selectedLang) {
    case "fr":
        header("Location: /fr/");
        die();

    case "en":
    default:
        header("Location: /en/");
        die();
}
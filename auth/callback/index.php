<?php

$app = $GLOBALS["ColdHazeApp"] = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/app.json"), true);
$server = "account.equestria.dev";

header("Content-Type: text/plain");

if (!isset($_GET['code'])) {
    die();
}

$crl = curl_init('https://' . $server . '/hub/api/rest/oauth2/token');
curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($crl, CURLINFO_HEADER_OUT, true);
curl_setopt($crl, CURLOPT_POST, true);
curl_setopt($crl, CURLOPT_HTTPHEADER, [
    "Authorization: Basic " . base64_encode($app["id"] . ":" . $app["secret"]),
    "Content-Type: application/x-www-form-urlencoded",
    "Accept: application/json"
]);
curl_setopt($crl, CURLOPT_POSTFIELDS, "grant_type=authorization_code&redirect_uri=" . urlencode("https://drivydrive.equestria.horse/auth/callback") . "&code=" . $_GET['code']);

$result = curl_exec($crl);
$result = json_decode($result, true);

curl_close($crl);

if (isset($result["access_token"])) {
    $crl = curl_init('https://' . $server . '/hub/api/rest/users/me');
    curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($crl, CURLINFO_HEADER_OUT, true);
    curl_setopt($crl, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $result["access_token"],
        "Accept: application/json"
    ]);

    $result = curl_exec($crl);
    $result = json_decode($result, true);

    if (!in_array($result["id"], $app["allowed"])) {
        header("Location: https://equestria.dev");
        die();
    }

    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . "/includes/tokens")) mkdir($_SERVER['DOCUMENT_ROOT'] . "/includes/tokens");

    $token = "dd" . str_replace("/", ".", base64_encode(random_bytes(96)));

    file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/tokens/" . $token, json_encode($result));

    header("Set-Cookie: DRIVYDRIVE_SESSION_TOKEN=" . $token . "; SameSite=None; Path=/; Secure; HttpOnly; Expires=" . date("r", time() + (86400 * 730)));

    header("Location: /");
    die();
}
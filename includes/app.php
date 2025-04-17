<?php

global $lang;
require_once $_SERVER['DOCUMENT_ROOT'] . "/includes/session.php";
$db = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/includes/db.json"), true);

$total = array_reduce(array_map(function ($i) { return $i['distance']; }, $db), function ($a, $b) { return $a + $b; });
$max = 3000;

function inTime($time) {
    global $lang;
    $difference = $time - time();

    if ($difference < 2.6298e+6) {
        return round($difference / 604800) . " " . $lang["time"][4][0] . (round($difference / 604800) > 0 ? $lang["time"][4][1] : "");
    } elseif ($difference < 604800) {
        return round($difference / 86400) . " " . $lang["time"][3][0] . (round($difference / 86400) > 0 ? $lang["time"][3][1] : "");
    } elseif ($difference < 86400) {
        return round($difference / 3600) . " " . $lang["time"][2][0] . (round($difference / 3600) > 0 ? $lang["time"][2][1] : "");
    } elseif ($difference < 3600) {
        return round($difference / 60) . " " . $lang["time"][1][0] . (round($difference / 60) > 0 ? $lang["time"][1][1] : "");
    } elseif ($difference < 60) {
        return $difference . " " . $lang["time"][0][0] . ($difference > 0 ? $lang["time"][0][1] : "");
    } else {
        return round($difference / 2.6298e+6) . " " . $lang["time"][5][0] . (round($difference / 2.6298e+6) > 0 ? $lang["time"][5][1] : "");
    }
}

function formatDate($date) {
    global $lang;

    $parts = array_map(function ($i) { return (int)$i; }, explode("/", $date));
    $dow = match ($parts[0]) {
        1 => $lang["days"][0],
        2 => $lang["days"][1],
        3 => $lang["days"][2],
        4 => $lang["days"][3],
        5 => $lang["days"][4],
        6 => $lang["days"][5],
        7 => $lang["days"][6],
        default => "-",
    };
    $month = match ($parts[2]) {
        1 => $lang["months"][0],
        2 => $lang["months"][1],
        3 => $lang["months"][2],
        4 => $lang["months"][3],
        5 => $lang["months"][4],
        6 => $lang["months"][5],
        7 => $lang["months"][6],
        8 => $lang["months"][7],
        9 => $lang["months"][8],
        10 => $lang["months"][9],
        11 => $lang["months"][10],
        12 => $lang["months"][11],
        default => "-",
    };

    return "$dow $parts[1] $month $parts[3]";
}

function getDuration($minutes) {
    global $lang;

    if ($minutes < 60) {
        return $minutes . " " . $lang["time"][1][0] . ($minutes > 1 ? $lang["time"][1][1] : "");
    } else {
        $hours = ceil($minutes / 60);
        $minutes = $minutes - $hours * 60;

        if ($minutes > 0) {
            return $hours . " " . $lang["time"][2][0] . ($hours > 1 ? $lang["time"][2][1] : "") . ", " . $minutes . " " . $lang["time"][1][0] . ($minutes > 1 ? $lang["time"][1][1] : "");
        } else {
            return $hours . " " . $lang["time"][2][0] . ($hours > 1 ? $lang["time"][2][1] : "");
        }
    }
}

?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <title><?= $lang["title"] ?></title>
</head>
<body>
<div class="container">
    <br><br>
    <h1><?= $lang["title"] ?></h1>

    <div class="card" style="margin-top: 10px;">
        <div class="card-body">
            <div style="display: grid; grid-template-columns: max-content 1fr max-content; margin-bottom: 10px;">
                <span><?= $total ?> km <?= $lang["km"] ?> (<?= str_replace(".", $lang["separator"], round(($total / $max) * 100, 1)) ?>%)</span>
                <span></span>
                <span><?= $max - $total ?> km <?= $lang["left"] ?></span>
            </div>

            <div class="progress" style="margin-bottom: 10px;">
                <div class="progress-bar" style="width: <?= ($total / $max) * 100 ?>%;"></div>
            </div>

            <div><?= $lang["eta"] ?> <?php

                $firstDate = strtotime($db[0]["date"]);
                $lastDate = strtotime($db[count($db) - 1]["date"]);
                $difference = $lastDate - $firstDate;
                $perKilometer = $difference / $total;

                $remainingTime = ($max - $total) * $perKilometer;
                $endDate = time() + $remainingTime;

                ?><?= formatDate(date('N/j/n/Y', $endDate)) ?>, <?= $lang["in"] ?> <?= inTime($endDate) ?></div>
        </div>
    </div>

    <hr>

    <div class="accordion" id="history">
        <?php foreach ($db as $id => $track): ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="track-<?= $id ?>-heading">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#track-<?= $id ?>" aria-expanded="false" aria-controls="track-<?= $id ?>" style="display: grid; grid-template-columns: max-content 1fr max-content max-content; grid-gap: 10px;">
                        <span><?= formatDate(date('N/j/n/Y', strtotime($track["date"]))) ?></span>
                        <span></span>
                        <span><?= $track["distance"] ?> km, <?= getDuration($track["duration"]) ?></span>
                    </button>
                </h2>
                <div id="track-<?= $id ?>" class="accordion-collapse collapse" aria-labelledby="track-<?= $id ?>-heading" data-bs-parent="#history">
                    <div class="accordion-body">
                        <?php if (isset($track["time"][0]) && isset($track["time"][1]) && isset($track["motion"])): ?>
                            <p><?= $lang["timing"][0] ?> <?= $track["time"][0] ?> <?= $lang["timing"][1] ?> <?= $track["time"][1] ?>, <?= $track["motion"] ?> <?= $lang["timing"][2] ?> (<?= round(($track["motion"] / $track["duration"]) * 100) ?>%).</p>
                        <?php endif; ?>

                        <div class="list-group">
                            <div class="list-group-item">
                                <p style="margin-bottom: 0.5rem;"><b><?= $lang["target"]["intro"] ?></b></p>
                                <img alt="" src="/assets/icons/target.svg" style="vertical-align: middle;"><span style="vertical-align: middle;"> <?= isset($track["target"]) ? strip_tags($track["target"]) : "<span class='text-muted'>" . $lang["target"]["none"] . "</span>" ?></span>
                            </div>
                            <div class="list-group-item">
                                <p style="margin-bottom: 0.5rem;"><b><?= $lang["types"]["title"] ?></b></p>
                                <?php foreach ($track["tracks"] as $type): ?>

                                    <div>
                                        <img alt="" src="/assets/icons/<?= match ($type) {
                                            "city", "center" => "city",
                                            "motorway", "highway" => "motorway",
                                            "road" => "road",
                                            default => "type",
                                        } ?>.svg" style="vertical-align: middle;"><span style="vertical-align: middle;"> <?= match ($type) {
                                                "city", "center" => $lang["types"]["city"],
                                                "motorway" => $lang["types"]["motorway"],
                                                "highway" => $lang["types"]["highway"],
                                                "road" => $lang["types"]["road"],
                                                default => $lang["unknown"] . " ($type)",
                                            } ?></span>
                                    </div>

                                <?php endforeach; ?>
                            </div>
                            <div class="list-group-item">
                                <p style="margin-bottom: 0.5rem;"><b><?= $lang["traffic"]["title"] ?></b></p>
                                <?php foreach ($track["traffic"] as $info): ?>

                                    <div>
                                        <img alt="" src="/assets/icons/<?= match ($info) {
                                            "low" => "traffic-no",
                                            "sunny" => "sunny",
                                            "rain" => "rain-light",
                                            "shower" => "rain-heavy",
                                            "snow" => "snow",
                                            "mist" => "mist",
                                            "crowded" => "traffic-major",
                                            "medium" => "traffic-minor",
                                            default => "traffic",
                                        } ?>.svg" style="vertical-align: middle;"><span style="vertical-align: middle;"> <?= match ($info) {
                                                "low" => $lang["traffic"]["low"],
                                                "sunny" => $lang["traffic"]["sunny"],
                                                "rain" => $lang["traffic"]["rain"],
                                                "shower" => $lang["traffic"]["shower"],
                                                "snow" => $lang["traffic"]["snow"],
                                                "mist" => $lang["traffic"]["mist"],
                                                "crowded" => $lang["traffic"]["crowded"],
                                                "medium" => $lang["traffic"]["medium"],
                                                default => $lang["unknown"] . " ($info)",
                                            } ?></span>
                                    </div>

                                <?php endforeach; ?>
                            </div>
                            <div class="list-group-item">
                                <p style="margin-bottom: 0.5rem;"><b><?= $lang["comments"]["intro"] ?></b></p>
                                <img alt="" src="/assets/icons/comment.svg" style="vertical-align: middle;"><span style="vertical-align: middle;"> <?= isset($track["comments"]) ? strip_tags($track["comments"]) : "<span class='text-muted'>" . $lang["comments"]["none"] . "</span>" ?></span>
                            </div>
                            <div class="list-group-item">
                                <p style="margin-bottom: 0.5rem;"><b><?= $lang["speed"]["intro"] ?></b></p>

                                <?php if (isset($track["speed"]["average"]) && isset($track["speed"]["max"])): ?>
                                    <div class="progress">
                                        <div class="progress-bar bg-secondary" style="display: grid; grid-template-columns: max-content 1fr max-content; width: 100%;">
                                            <span style="margin-left: 5px;">0 km·h<sup>-1</sup></span>
                                            <span></span>
                                            <span style="margin-right: 5px;"><?= $track["speed"]["max"] ?> km·h<sup>-1</sup></span>
                                        </div>
                                    </div>
                                    <div>
                                        <img src="/assets/icons/pointer.svg" style="margin-left: calc(<?= ($track["speed"]["average"] / $track["speed"]["max"]) * 100 ?>% - 12px);" title="<?= $lang["speed"]["average"] ?> <?= $track["speed"]["average"] ?> km·h<sup>-1</sup>" data-bs-html="true" data-bs-toggle="tooltip">
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted"><img src="/assets/icons/speed.svg"><span style="vertical-align: middle;"> <?= $lang["speed"]["none"] ?></span></span>
                                <?php endif; ?>
                            </div>
                            <div class="list-group-item">
                                <p style="margin-bottom: 0.5rem;"><b><?= $lang["altitude"]["intro"] ?></b></p>

                                <?php if (isset($track["altitude"]["up"]) && isset($track["altitude"]["down"]) && isset($track["altitude"]["range"][0]) && isset($track["altitude"]["range"][1]) && isset($track["altitude"]["average"])): ?>
                                    <div class="progress">
                                        <div class="progress-bar bg-secondary" style="display: grid; grid-template-columns: max-content 1fr max-content; width: 100%;">
                                            <span style="margin-left: 5px;"><?= $track["altitude"]["range"][0] ?> m</span>
                                            <span></span>
                                            <span style="margin-right: 5px;"><?= $track["altitude"]["range"][1] ?> m</span>
                                        </div>
                                    </div>
                                    <div>
                                        <img src="/assets/icons/pointer.svg" style="margin-left: calc(<?= ($track["altitude"]["average"] / $track["altitude"]["range"][1]) * 100 ?>% - 12px);" title="<?= $lang["altitude"]["average"] ?> <?= $track["altitude"]["average"] ?> m" data-bs-toggle="tooltip">
                                    </div>
                                    <div>
                                        <div title="<?= $lang["altitude"]["upwards"] ?>" data-bs-toggle="tooltip" style="width: max-content; display: inline-block;"><img alt="" src="/assets/icons/upwards.svg" style="vertical-align: middle;"><span style="vertical-align: middle;"> <?= $track["altitude"]["up"] ?> m</span></div>
                                        <div title="<?= $lang["altitude"]["downwards"] ?>" data-bs-toggle="tooltip" style="width: max-content; display: inline-block;"><img alt="" src="/assets/icons/downwards.svg" style="vertical-align: middle;"><span style="vertical-align: middle;"> <?= $track["altitude"]["down"] ?> m</span></div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted"><img src="/assets/icons/altitude.svg"><span style="vertical-align: middle;"> <?= $lang["altitude"]["none"] ?></span></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <hr>

    <p class="small text-muted"><?= $lang["disclaimer"] ?></p>
</div>

<script>
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
</script>
</body>
</html>
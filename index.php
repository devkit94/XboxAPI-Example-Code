<?php

date_default_timezone_set('Europe/London');
define('GAMERTAGS_CSV', 'gamertags.csv');

header('Content-Type: text/plain');

// get the composer autoloader
include __DIR__ . '/vendor/autoload.php';

if (!file_exists(GAMERTAGS_CSV)) {
    touch(GAMERTAGS_CSV);
    include __DIR__ . '/generate_gamertags.php';
    generate_gamertags();
}

// get our config file
include __DIR__ . '/config.php';

// load the helper files
include __DIR__ . '/helpers/database.php';
include __DIR__ . '/helpers/xboxapi.php';

$count = Gamertag::whereNull('xuid')->count();

while ($count > 0) {
    $gamertags = Gamertag::whereNull('xuid')->take(500)->get();

    foreach ($gamertags as $gamertag) {
        $gamertag = getXuid($gamertag);

        if ($gamertag !== false) {
            print $gamertag . PHP_EOL;
            $count--;
        }
    }

    $count = Gamertag::whereNull('xuid')->count();
}

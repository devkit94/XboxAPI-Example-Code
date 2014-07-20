<?php

function generate_gamertags() {
    $gamertag = isset($_GET['gamertag']) ? urldecode($_GET['gamertag']) : 'djekl';
    $url = 'https://xboxapi.com/v1/friends/' . rawurlencode($gamertag);

    print "Fetching friends for {$gamertag}\n";

    file_put_contents(GAMERTAGS_CSV, $gamertag . PHP_EOL, FILE_APPEND);

    $guzzle = new GuzzleHttp\Client();
    $response = $guzzle->get($url);
    $friends = $response->json();
    $count = 0;

    foreach ($friends['Friends'] as $friend) {
        file_put_contents(GAMERTAGS_CSV, $friend['GamerTag'] . PHP_EOL, FILE_APPEND);
        $count++;
    }

    print "Fetched {$count} friends for {$gamertag}\n\n";

    unset($guzzle);
    unset($response);
    unset($friends);
    unset($friend);
    unset($count);
}

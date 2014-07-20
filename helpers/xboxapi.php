<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;

$xboxapi = new GuzzleHttp\Client();

if (!defined('XboxAPI_URL')) {
    define('XboxAPI_URL', 'https://xboxapi.com');
}

function getXuid(Gamertag $gamertag, $attempts = 0)
{
    global $xboxapi;

    if (!empty($gamertag->xuid)) {
        return $gamertag->gamertag . ': ' . $xuid . PHP_EOL;
    }

    $xuid = 'failed to get xuid';
    $url = XboxAPI_URL . '/v2/xuid/' . rawurlencode($gamertag->gamertag);
    print $url . PHP_EOL;

    try {
        $response = $xboxapi->get($url, [
            'headers' => ['X-Auth' => XboxAPI_Key],
        ]);
    } catch (Exception $e) {
        $message = $e->getMessage();
        print $message . PHP_EOL;

        if ($attempts < 2) {
            $attempts++;

            return getXuid($gamertag, $attempts);
        }

        if (strpos($message, '[status code] 404') === false) {
            die(PHP_EOL . PHP_EOL . $message . PHP_EOL);
        }

        $gamertag->error = $message;
        $gamertag->xuid = $xuid;
        $gamertag->save();

        print PHP_EOL;

        return false;
    }

    $xuid = $response->getBody();

    // store in the db
    $gamertag->xuid = $xuid;
    $gamertag->save();

    return 'XUID for ' . $gamertag->gamertag . ' = ' . $xuid . PHP_EOL;
}

// echo $response->getStatusCode();           // 200
// echo $response->getHeader('content-type'); // 'application/json; charset=utf8'
// echo $response->getBody();                 // {"type":"User"...'
// var_export($response->json());             // Outputs the JSON decoded data

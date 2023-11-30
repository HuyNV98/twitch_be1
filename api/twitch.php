<?php

$clientId = "kimne78kx3ncx6brgo4mv6wki5h1ko";

function getAccessToken($id, $isVod) {
    global $clientId;

    $data = json_encode([
        "operationName" => "PlaybackAccessToken",
        "extensions" => [
            "persistedQuery" => [
                "version" => 1,
                "sha256Hash" => "3093517e37e4f4cb48906155bcd894150aef92617939236d2508f3375ab732ce"
            ]
        ],
        "variables" => [
            "isLive" => !$isVod,
            "login" => ($isVod ? "" : $id),
            "isVod" => $isVod,
            "vodID" => ($isVod ? $id : ""),
            "playerType" => "popout"
        ]
    ]);

    $options = [
        "http" => [
            "method" => "POST",
            "header" => "Content-Type: application/json\r\n" .
                        "Client-id: $clientId\r\n",
            "content" => $data
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents("https://gql.twitch.tv/gql", false, $context);

    $resData = json_decode($response, true);

    if (false) {
        throw new Exception($resData['message']);
    } else {
        return ($isVod ? $resData['data']['videoPlaybackAccessToken'] : $resData['data']['streamPlaybackAccessToken']);
    }
}

function getPlaylist($id, $accessToken, $vod) {
    global $clientId;
    $url = "https://usher.ttvnw.net/" . ($vod ? 'vod' : 'api/channel/hls') . "/$id.m3u8?client_id=$clientId&token={$accessToken['value']}&sig={$accessToken['signature']}&allow_source=true&allow_audio_only=true";

    $response = file_get_contents($url);
    return $response;
}

function parsePlaylist($playlist) {
    $parsedPlaylist = [];
    $lines = explode("\n", $playlist);

    for ($i = 2; $i < count($lines) - 1; $i += 3) {
        $parsedPlaylist[] = [
            "quality" => explode('NAME="', $lines[$i])[1],
            "resolution" => (strpos($lines[$i + 1], 'RESOLUTION') !== false ? explode('RESOLUTION=', $lines[$i + 1])[1] : null),
            "url" => $lines[$i + 2]
        ];
    }

    return $parsedPlaylist;
}

function getStream($channel, $raw) {
    try {
        $accessToken = getAccessToken($channel, false);
        $playlist = getPlaylist($channel, $accessToken, false);
        return ($raw ? $playlist : parsePlaylist($playlist));
    } catch (Exception $error) {
        return $error->getMessage();
    }
}

?>
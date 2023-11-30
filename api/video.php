<?php
require __DIR__.'/twitch.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");

if(isset($_GET['channel'])){
    echo str_replace('https://', '/video.php?load=https://', file_get_contents(getStream($_GET['channel'], false)[1]['url']));
}

if(isset($_GET['load'])){
    echo file_get_contents($_GET['load']);
}
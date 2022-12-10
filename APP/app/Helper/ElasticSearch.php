<?php

use Illuminate\Support\Arr;

function postReq($data)
{
    $data = json_encode($data);
    // echo $data."</br>";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://" . env("ES_HOST") . ":" . env("ES_PORT") . "/" . env("ES_INDEX") . "/_search?scroll=1m");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLINFO_CONTENT_TYPE, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result, true);
}

function getStopWords()
{
    $words = Arr::pluck(\App\StopWord::select("word")->get(), "word");
    return str_replace("+OR+", " OR ", str_replace(" ", "+", implode(" OR ", $words)));
}
function getVector($text){
    return json_decode(file_get_contents(env("VECTOR_API").urlencode($text)),true);
}

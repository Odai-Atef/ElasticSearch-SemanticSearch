<?php


function postReq($data){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,"http://3.125.9.240:9221/products/_doc");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLINFO_CONTENT_TYPE, 1);
    curl_setop t( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

    curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    echo "<pre>$result</pre>";
    curl_close ($ch);
}
$dir=__DIR__."/var/www/html/vectors";
$docs=scandir($dir);
foreach ($docs as $doc){
    if(!is_dir($dir."/$doc")){
        $data=file_get_contents($dir."/$doc");
        postReq($data);

    }
}

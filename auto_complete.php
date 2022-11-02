<?php
   $stop_words=file_get_contents("http://3.125.9.240/ElasticSearch-SemanticSearch/stop_words/public/index.php/api/words");
   $data_fuzzy = postReq([
    "aggs"=>[
        "my-agg-name"=>[
            "terms"=>[
                "field"=>"category_id"
            ]
        ]
            ],
    "size" => 0,
    "query" => [
        "query_string" => [
            "fields" => ["product_name", "product_description"],
            "query" => trim($_GET['keyword']) . " AND !($stop_words)",
            "fuzziness" => "1"
        ]
       
    ]
]);
$data=[];
if(isset($data_fuzzy['aggregations']['my-agg-name']['buckets'])){
    foreach($data_fuzzy['aggregations']['my-agg-name']['buckets'] as $categri){
        $data[]=$_GET["keyword"]. " in category_".$categri['key']. "(".$categri['doc_count'].")";
    }
    echo json_encode($data);

}

function postReq($data)
{
    $data = json_encode($data);
    // echo $data."</br>";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://3.125.9.240:9221/products2/_search");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLINFO_CONTENT_TYPE, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    return json_decode($result,true);
}
?>
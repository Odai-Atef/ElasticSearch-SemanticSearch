<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get("/aggs/categories","AggsController@categories");
Route::get("/aggs/variants","AggsController@variants");

Route::get("/update",function (){
    $data=json_decode(file_get_contents("http://".env("ES_HOST").":".env("ES_PORT")."/".env("ES_INDEX")."/_search?scroll=1m"),true);
    \Illuminate\Support\Facades\DB::table("cms_settings")->where("name","=","number_of_products")->update(["content"=>$data['hits']['total']['value']]);
    $asw=new \App\Http\Controllers\AdminStopWordsController();
    $data=$asw->getStopTweets(false);
    \Illuminate\Support\Facades\DB::table("cms_settings")->where("name","=","number_of_unwanted")->update(["content"=>$data['data_fuzzy']['hits']['total']['value']]);
});
Route::get("/words", function () {
    return getStopWords();
});
Route::get("/auto_complete", function (Request $request) {
    $stop_words = getStopWords();
    $data_fuzzy = postReq([
        "aggs" => [
            "my-agg-name" => [
                "terms" => [
                    "field" => "category_name.keyword"
                ]
            ]
        ],
        "query" => [
            "query_string" => [
                "fields" => [ "description","name"],
                "query" => trim($_GET['keyword']) . " AND !($stop_words)",
                "fuzziness" => "1"
            ]
        ]
    ]);
    $data = [];
    if (isset($data_fuzzy['aggregations']['my-agg-name']['buckets'])) {
        foreach ($data_fuzzy['aggregations']['my-agg-name']['buckets'] as $categri) {
//            $data[] = $_GET["keyword"] . " in category_" . $categri['key'] . "(" . $categri['doc_count'] . ")";
            $data[] = $_GET["keyword"] . " في " . $categri['key'] ;

        }
    }
    return json_encode($data);
});
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

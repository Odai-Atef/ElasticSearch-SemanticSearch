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
Route::get("/words", function () {
    return getStopWords();
});
Route::get("/auto_complete", function (Request $request) {
    $stop_words = getStopWords();
    $data_fuzzy = postReq([
        "aggs" => [
            "my-agg-name" => [
                "terms" => [
                    "field" => "category_id"
                ]
            ]
        ],
        "query" => [
            "query_string" => [
                "fields" => ["product_name", "product_description", "brand_name", "option_description"],
                "query" => trim($_GET['keyword']) . " AND !($stop_words)",
                "fuzziness" => "AUTO"
            ]
        ]
    ]);
    $data = [];
    if (isset($data_fuzzy['aggregations']['my-agg-name']['buckets'])) {
        foreach ($data_fuzzy['aggregations']['my-agg-name']['buckets'] as $categri) {
            $data[] = $_GET["keyword"] . " in category_" . $categri['key'] . "(" . $categri['doc_count'] . ")";
        }
    }
    return json_encode($data);
});
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

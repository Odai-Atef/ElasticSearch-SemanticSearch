<?php

namespace App\Http\Controllers;

use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    //
    function fuzzy(Request $request)
    {
        if (CRUDBooster::myId() == null) {
            return CRUDBooster::redirect(url("/admin"), cbLang('denied_access'));
        }
        $data = [];
        $stop_words = getStopWords();
        $data['page_title'] = 'Fuzzy Search Products';
        if ($request->query("keyword")) {
            $data['data_fuzzy'] = postReq([
                "size" => 100,
                "query" => [
                    "query_string" => [
                        "fields" => ["product_name", "product_description"],
                        "query" => trim($_GET['keyword']) . " AND !($stop_words)",
                        "fuzziness" => "AUTO"
                    ],
                ],
                "_source" => [
                    "includes" => [
                        "product_name", "product_description", "product_id"
                    ]
                ],
                "sort" => [
                    [
                        "_score" => [
                            "order" => "desc"
                        ]
                    ]
                ]
            ]);
        }
        return view('search', $data);
    }
}

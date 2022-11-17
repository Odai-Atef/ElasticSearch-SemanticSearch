<?php

namespace App\Http\Controllers;

use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class SearchController extends Controller
{
    //
    function suggest($keyword)
    {
        $data= postReq([
                "suggest" => [
                    "my-suggestion-description" => [
                        "text" => "$keyword",
                        "term" => [
                            "field" => "product_description.keyword"
                        ]
                    ],
                    "my-suggestion-title" => [
                        "text" => "$keyword",
                        "term" => [
                            "field" => "product_name.keyword"
                        ]
                    ]
                ]
            ]
        );
        $results=[];
        if(isset($data['suggest'])){
            foreach ($data['suggest'] as $suggestions){
                if(isset($suggestions[0]['options'])){
                    $results=array_merge($results,Arr::pluck($suggestions[0]['options'],"score","text"));
                }
            }
        }
        arsort($results);
        return array_keys($results);
    }

    function fuzzy_with_category($stop_words, $category_id)
    {
        return postReq([
            "size" => 100,
            "query" => [
                "bool" => [
                    "must" => [
                        [
                            "query_string" => [
                                "fields" => [
                                    "product_name",
                                    "product_description"
                                ],
                                "query" => trim($_GET['keyword']) . " AND !($stop_words)",
                                "fuzziness" => "1"
                            ]
                        ],
                        [
                            "match" => [
                                "category_name" => $category_id
                            ]
                        ]
                    ]
                ]
            ],
            "_source" => [
                "includes" => [
                    "product_name",
                    "product_description",
                    "product_id",
                    "category_id"
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

    function fuzzy(Request $request)
    {
        if (CRUDBooster::myId() == null) {
            return CRUDBooster::redirect(url("/admin"), cbLang('denied_access'));
        }
        $keyword=trim($_GET['keyword']);
        $data = [];
        $stop_words = getStopWords();
        $data['page_title'] = 'Fuzzy Search Products';
        if ($request->query("category_id")) {
            $data['data_fuzzy'] = $this->fuzzy_with_category($stop_words, $request->query("category_id"));
        } else if ($request->query("keyword")) {
            $data['data_fuzzy'] = postReq([
                "size" => 100,
                "query" => [
                    "query_string" => [
                        "fields" => ["product_name", "product_description"],
                        "query" =>   "$keyword AND !($stop_words)",
                        "fuzziness" => "1"
                    ],
                ],
                "_source" => [
                    "includes" => [
                        "product_name", "product_description", "product_id", "category_id"
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
        $data["suggestions"]=$this->suggest($keyword);
        return view('search', $data);
    }
}

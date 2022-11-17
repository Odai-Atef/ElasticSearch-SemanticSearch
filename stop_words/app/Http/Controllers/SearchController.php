<?php

namespace App\Http\Controllers;

use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class SearchController extends Controller
{
    public $query = [];

    //
    function suggest($keyword)
    {
        $data = postReq([
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
        $results = [];
        if (isset($data['suggest'])) {
            foreach ($data['suggest'] as $suggestions) {
                if (isset($suggestions[0]['options'])) {
                    $results = array_merge($results, Arr::pluck($suggestions[0]['options'], "score", "text"));
                }
            }
        }
        arsort($results);
        return array_keys($results);
    }

    function mapping($request)
    {
        $map_text = ["category_id", "tags", "store_id"];
        foreach ($map_text as $keyword) {
            if ($request->query($keyword)) {
                $this->filter_by_text($keyword, $request->query($keyword));

            }
        }

    }

    function filter_by_text($key, $value)
    {
        $this->query["query"]['bool']['must'][1]['match'][$key] = $value;
    }

    function fuzzy_with_category($stop_words, $category_id)
    {
        $query = [
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
        ];
        $query = $this->filter_by_text("category_name", $category_id, $query);
        return postReq($query);


    }

    function fuzzy(Request $request)
    {
        if (CRUDBooster::myId() == null) {
            return CRUDBooster::redirect(url("/admin"), cbLang('denied_access'));
        }
        $keyword = trim($_GET['keyword']);
        $data = [];
        $stop_words = getStopWords();
        $data['page_title'] = 'Fuzzy Search Products';
        if ($request->query("keyword")) {
            $this->query = $query = [
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
            ];
            $this->mapping($request);
            $data['data_fuzzy'] = postReq($this->query);
        }
        $data["suggestions"] = $this->suggest($keyword);
        return view('search', $data);
    }
}

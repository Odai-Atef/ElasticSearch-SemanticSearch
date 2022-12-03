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
                            "field" => "description.text.keyword"
                        ]
                    ],
                    "my-suggestion-title" => [
                        "text" => "$keyword",
                        "term" => [
                            "field" => "name.text.keyword"
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
        $map_text = ["variants_details_value" => "variants.details.value", "categories_name_text_keyword" => "categories.name.text.keyword", "tags", "store_id", "tags"];
        $map_min = ["min_price", "min_rating"];
        $map_max = ["max_price", "max_rating"];
        foreach ($map_text as $key => $keyword) {
            if (is_numeric($key)) {
                $map_key = $keyword;
            } else {
                $map_key = $key;
            }
            if ($request->query($map_key)) {
                $this->filter_by_text($keyword, $request->query($map_key));
            }
        }
        foreach ($map_min as $keyword) {
            if ($request->query($keyword)) {
                $this->filter_by_range(str_replace("min_", "", $keyword), $request->query($keyword), "gte");
            }
        }
        foreach ($map_max as $keyword) {
            if ($request->query($keyword)) {
                $this->filter_by_range(str_replace("max_", "", $keyword), $request->query($keyword), "lte");
            }
        }

    }

    function filter_by_text($key, $value)
    {
        $this->query["query"]['bool']['must'][1]['match'][$key] = $value;
    }

    function filter_by_range($key, $value, $op)
    {
        $this->query["query"]['bool']['must'][]['range'][$key][$op] = $value;
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
        $this->query = [
            "size" => 250,
            "query" => [
                "bool" => [
                    "must" => [
                        [
                            "query_string" => [
                                "fields" => [
                                    "name.text",
                                    "description.text"
                                ],
                                "query" => trim($_GET['keyword']) . " AND !($stop_words)",
                                "fuzziness" => "1"
                            ]
                        ]
                    ]
                ]
            ],

            "sort" => [
                [
                    $_GET['sort_by'] => [
                        "order" => "desc"
                    ]
                ]
            ]
        ];
        $this->mapping($request);
        if ($keyword == "") {
            unset($this->query["query"]["bool"]["must"][0]);
        }
        $aggs = new AggsController();
        $this->query["query"]["bool"]["must"] = array_values($this->query["query"]["bool"]["must"]);
        $data['data_fuzzy'] = postReq($this->query);
        $data["suggestions"] = $this->suggest($keyword);
        $data['categories'] = $aggs->categories($keyword);
        $data['variants_data'] = $aggs->variants($keyword);
        $data['variants_value_data'] = $aggs->variants_values($keyword);
        $data['stores'] = $aggs->stores($keyword);
        $data['tags'] = $aggs->tags_values($keyword);

        return view('search', $data);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AggsController extends Controller
{
    //
    public $query = [
        "aggs" => [
            "my-agg-name" => [
                "terms" => [
                    "field" => "",
                    "size" => 500
                ]
            ]
        ]
    ];

    function setField($field)
    {
        $this->query['aggs']['my-agg-name']['terms']['field'] = $field;
    }

    function withFilter($words)
    {
        $stop_words = getStopWords();
        $this->query["query"] = ["bool" => [
            "must" => [
                [
                    "query_string" => [
                        "fields" => ["name.text", "description.text"],
                        "query" => "$words AND !($stop_words)",
                        "fuzziness" => "1"
                    ]
                ]
            ]
        ]
        ];
    }

    function getData($data_fuzzy)
    {
        if (isset($data_fuzzy['aggregations']['my-agg-name']['buckets'])) {
            return Arr::pluck($data_fuzzy['aggregations']['my-agg-name']['buckets'], "key");
        }
        return [];
    }

    function categories($keyword)
    {
        $this->setField("categories.name.text.keyword");
        if ($keyword != "") {
            $this->withFilter($keyword);
        }
        $data_fuzzy = postReq($this->query);
        return $this->getData($data_fuzzy);
    }

    function stores($keyword)
    {
        $this->setField("store_id");
        if ($keyword != "") {
            $this->withFilter($keyword);
        }
        $data_fuzzy = postReq($this->query);
        return $this->getData($data_fuzzy);
    }

    function tags_values($keyword)
    {
        $this->setField("tags.keyword");
        if ($keyword != "") {
            $this->withFilter($keyword);
        }
        $data_fuzzy = postReq($this->query);
        return $this->getData($data_fuzzy);
    }

    function variants($keyword)
    {
        $this->setField("variants.details.attribute.keyword");
        if ($keyword != "") {
            $this->withFilter($keyword);
        }
        $data_fuzzy = postReq($this->query);
        return $this->getData($data_fuzzy);
    }

    function variants_values($keyword)
    {
        $this->setField("variants.details.value.keyword");
        if ($keyword != "") {
            $this->withFilter($keyword);
        }
        $data_fuzzy = postReq($this->query);
        return $this->getData($data_fuzzy);
    }

    function tags()
    {

    }
}

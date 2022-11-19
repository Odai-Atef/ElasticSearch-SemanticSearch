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

    function getData($data_fuzzy)
    {
        if (isset($data_fuzzy['aggregations']['my-agg-name']['buckets'])) {
            return Arr::pluck($data_fuzzy['aggregations']['my-agg-name']['buckets'], "key");
        }
        return [];
    }

    function categories()
    {
        $this->setField("categories.name.text.keyword");
        $data_fuzzy = postReq($this->query);
        return $this->getData($data_fuzzy);
    }

    function variants()
    {
        $this->setField("variants.details.attribute.keyword");
        $data_fuzzy = postReq($this->query);
        return $this->getData($data_fuzzy);
    }

    function tags()
    {

    }
}

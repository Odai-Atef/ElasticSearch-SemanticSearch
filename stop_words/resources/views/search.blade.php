@extends('crudbooster::admin_template')
@section('content')
    <div class="col-12">
        <form method="get">
            <div class="input-group" style="width: 100%;margin-bottom: 40px">
                <input id="keyword" value="<?php echo isset($_GET['keyword']) ? $_GET['keyword'] : "" ?>" name="keyword"
                       type="text" class="search-query form-control col-12" placeholder="Search"/>
            </div>
            <div class="input-group" style="width: 100%;margin-bottom: 40px">

                <input type="number" name="store_id" class="col-3" placeholder="Store ID"
                       value="{{$_GET['store_id']?$_GET['store_id']:""}}"/>
                <input type="text" name="tags" class="col-3" placeholder="Tags"
                       value="{{$_GET['tags']?$_GET['tags']:""}}"/>
                <input type="number" name="min_price" class="col-3" placeholder="Min Price"
                       value="{{$_GET['min_price']?$_GET['min_price']:""}}"/>
                <input type="number" name="max_price" class="col-3" placeholder="Max Price"
                       value="{{$_GET['max_price']?$_GET['max_price']:""}}"/>
                <input type="number" name="min_rating" class="col-3" placeholder="Min Rating"
                       value="{{$_GET['min_rating']?$_GET['min_rating']:""}}"/>
                <input type="number" name="max_rating" class="col-3" placeholder="Max Rating"
                       value="{{$_GET['max_rating']?$_GET['max_rating']:""}}"/>
                <select class="col-3" name="categories_name_text_keyword">
                    <option></option>
                    @foreach($categories as $category)
                        <option
                            {{$_GET['categories_name_text_keyword']==$category?"selected":""}} value="{{$category}}">{{$category}}</option>
                    @endforeach
                </select>
                <select class="col-3" name="variants_details_attribute">
                    <option></option>
                    @foreach($variants_data as $variant)
                        <option
                            {{$_GET['variants_details_attribute']==$variant?"selected":""}} value="{{$variant}}">{{$variant}}</option>
                    @endforeach
                </select>
                <input type="text" name="variants_details_value" class="col-3" placeholder="Variant Value"
                       value="{{$_GET['variants_details_value']?$_GET['variants_details_value']:""}}"/>
            </div>
            <button type="submit">Submit</button>
        </form>
        <div class="table-responsive">
            <h4 class="text-right">

                (<?php echo number_format($data_fuzzy['hits']['total']['value'] ?? 0); ?>)
                <b>
                    {{isset($_GET['keyword'])?$_GET['keyword']:""}}
                </b>
                في
                <b>
                    {{isset($_GET['category_id'])?$_GET['category_id']:""}}
                </b>
                نتائج البحث</h4>
            @if(count($suggestions)>0)
                <p class="text-right">
                    هل تقصد
                    @foreach($suggestions as $suggest)
                        <a href="{{url("/admin/fuzzy?keyword=".$suggest)}}">{{$suggest}}</a>
                    @endforeach
                </p>
            @endif
            <table class="table table-striped table-bordered text-right" width="100%">
                <tbody>
                <?php
                if (isset($data_fuzzy['hits']['hits']))
                foreach ($data_fuzzy['hits']['hits'] as $tweet) {
                ?>
                <tr>
                    <td>
                        <h3>
                            <?php echo str_replace(strtolower($_GET['keyword']), "<b>" . $_GET['keyword'] . "</b>", strtolower($tweet['_source']['product_name'])) ?>
                        </h3>
                        <p><?php echo str_replace(strtolower($_GET['keyword']), "<b>" . $_GET['keyword'] . "</b>", strtolower($tweet['_source']['description'][0]['text'])) ?></p>
                        <small>Price: {{$tweet['_source']['price']}} SAR</small>
                        <br/>
                        <small>Rating: {{$tweet['_source']['rating']}} / 5</small>
                        <br/>
                        <small>Store ID: {{$tweet['_source']['store_id']}}</small>
                        <br/>
                        <small>Category: {{$tweet['_source']['categories'][0]['name'][0]['text']}}</small>
                        <br/>
                        <small>Tags: {{is_array($tweet['_source']['tags'])?implode(",",$tweet['_source']['tags']):""}}</small>
                        <br/>
                        <small>Variants:
                            @foreach($tweet["_source"]['variants'] as $vv)
                                @foreach($vv['details'] as $v)
                                   <b> {{$v['attribute']}}:{{$v['value']}} </b>
                                @endforeach
                            @endforeach
                    </td>
                </tr>
                <?php } else {
                ?>
                <tr>
                    <td colspan="2">No Result Found</td>
                </tr>
                <?php
                } ?>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push("bottom")
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <script>
        $(function () {
            $("#keyword").autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: site_url + "/api/auto_complete",
                        dataType: "json",
                        data: {
                            keyword: request.term
                        },
                        success: function (data) {
                            response(data);
                        }
                    });
                },
                minLength: 2,
                select: function (event, ui) {
                    var keywords = ui.item.value.split(" في ");
                    window.open(site_url + "/admin/fuzzy?keyword=" + keywords[0] + "&category_id=" + keywords[1]);
                }
            });
        });
    </script>
@endpush

@extends('crudbooster::admin_template')
@section('content')

    <div class="col-12">
        <form method="get">
            <div class="margin-bottom">
                <div class="row ">
                    <div class="col-sm-12 margin-bottom">
                        <div class="form-group">
                            <label for="">Search For</label>
                            <input id="keyword" value="<?php echo isset($_GET['keyword']) ? $_GET['keyword'] : "" ?>"
                                   name="keyword"
                                   type="text" class="form-control " placeholder="ex: iphone"/>
                        </div>
                    </div>
                    <div class="row margin-bottom">
                        <div class="col-sm-12">
                            @if($tags || true)
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="">Tags</label>
                                        <select class="form-control" name="tags">
                                            <option value="">Tags</option>
                                            @foreach($tags as $tag)
                                                <option
                                                    {{$_GET['tags']==$tag?"selected":""}} value="{{$tag}}">{{$tag}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif
                            @if($categories || true)
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="">Categories</label>
                                        <select class="form-control" name="categories_name_text_keyword">
                                            <option value="">Categories</option>
                                            @foreach($categories as $category)
                                                <option
                                                    {{$_GET['categories_name_text_keyword']==$category?"selected":""}} value="{{$category}}">{{$category}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif
                            @if($variants_data || true)
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="">Variants</label>
                                        <select class="form-control" name="variants_details_attribute">
                                            <option value="">Variant</option>
                                            @foreach($variants_data as $variant)
                                                <option
                                                    {{$_GET['variants_details_attribute']==$variant?"selected":""}} value="{{$variant}}">{{$variant}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif
                            @if($variants_value_data || true)
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label for="">Variants Value</label>
                                        <select class="form-control" name="variants_details_value">
                                            <option value="">Variant Value</option>
                                            @foreach($variants_value_data as $variant)
                                                <option
                                                    {{$_GET['variants_details_value']==$variant?"selected":""}} value="{{$variant}}">{{$variant}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="row margin-bottom">
                        <div class="col-sm-12">
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="">Store</label>
                                    <select class="form-control" name="store_id">
                                        <option value="">Store</option>
                                        @foreach($stores as $store)
                                            <option
                                                {{$_GET['store_id']==$store?"selected":""}} value="{{$store}}">{{$store}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="">Min Price</label>
                                    <input type="number" name="min_price"
                                           value="{{$_GET['min_price']?$_GET['min_price']:""}}" class="form-control"/>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="">Max Price</label>
                                    <input type="number" class="form-control" name="max_price"
                                           value="{{$_GET['max_price']?$_GET['max_price']:""}}"/>
                                </div>
                            </div>

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="">Min Rating</label>
                                    <input type="number" class="form-control" name="min_rating"
                                           value="{{$_GET['min_rating']?$_GET['min_rating']:""}}"/>
                                </div>
                            </div>

                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label for="">Max Rating</label>
                                    <input type="number" class="form-control" name="max_rating"
                                           value="{{$_GET['max_rating']?$_GET['max_rating']:""}}"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="col-sm-12 btn btn-sm btn-primary margin-bottom"><i
                        class="fa fa-search"></i> Search
                </button>
            </div>
            @php
                $sorting=[
                    "_score"=>"Most Relevant",
                    "price"=>"Price",
                    "rating"=>"Rating",
                    //"_score"=>"Near to me",
                    ];
            @endphp
            <div class="col-12 ">
                <div class="col-3 pull-left">
                    <div class="form-group">
                        <select class="form-control" name="sort_by">
                            @foreach($sorting as $k=>$v)
                                <option {{$k==$_GET['sort_by']??"selected"}} value="{{$k}}">{{$v}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-4 pull-right">
                    <h4 class="text-right">

                        (<?php echo number_format($data_fuzzy['hits']['total']['value'] ?? 0); ?>)
                        <b>
                            {{isset($_GET['keyword'])?$_GET['keyword']:""}}
                        </b>
                        في
                        <b>
                            {{isset($_GET['category_id'])?$_GET['category_id']:""}}
                        </b>
                        نتائج البحث
                    </h4>
                </div>
            </div>
        </form>
        <div class="clearfix"></div>
        <div class="table-responsive">

            @if(count($suggestions)>0)
                <p class="text-right">
                    @foreach($suggestions as $suggest)
                        <span class="badge  badge-success">
                          <a style="color: white" href="{{url("/admin/fuzzy?keyword=".$suggest)}}">{{$suggest}}</a>
                        </span>
                    @endforeach
                    هل تقصد
                </p>
            @endif
            <table class="table table-striped table-bordered text-right" width="100%">
                <tbody>
                @if(isset($data_fuzzy['hits']['hits']))
                    @foreach ($data_fuzzy['hits']['hits'] as $product)
                        <tr>
                            <td>
                                <h3>
                                    <?php echo str_replace(strtolower($_GET['keyword']), "<b>" . $_GET['keyword'] . "</b>", strtolower($product['_source']['product_name'])) ?>
                                </h3>
                                <p><?php echo str_replace(strtolower($_GET['keyword']), "<b>" . $_GET['keyword'] . "</b>", strtolower($product['_source']['description'][0]['text'])) ?></p>
                                <small>Price: {{$product['_source']['price']}} SAR</small>
                                <br/>
                                <small>Rating: {{$product['_source']['rating']}} / 5</small>
                                <br/>
                                <small>Store ID: {{$product['_source']['store_id']}}</small>
                                <br/>
                                <small>Category: {{$product['_source']['categories'][0]['name'][0]['text']}}</small>
                                <br/>
                                <small>Tags: {{is_array($product['_source']['tags'])?implode(",",$product['_source']['tags']):""}}</small>
                                <br/>
                                <small>Variants:
                                    @foreach($product["_source"]['variants'] as $vv)
                                        @foreach($vv['details'] as $v)
                                            <span
                                                class="badge badge-success"> {{$v['attribute']}}:{{$v['value']}} </span>
                                @endforeach
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="2">No Result Found</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push("bottom")
    <style>
        .margin-bottom {
            margin-bottom: 20px;
        }
    </style>
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

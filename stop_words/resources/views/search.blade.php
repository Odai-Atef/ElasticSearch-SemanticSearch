@extends('crudbooster::admin_template')
@section('content')
    <div class="col-12">
        <form method="get">
            <div class="input-group" style="width: 100%;margin-bottom: 40px">
                <input id="keyword" value="<?php echo isset($_GET['keyword']) ? $_GET['keyword'] : "" ?>" name="keyword"
                       type="text" class="search-query form-control col-12" placeholder="Search"/>
            </div>
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
                        <p><?php echo str_replace(strtolower($_GET['keyword']), "<b>" . $_GET['keyword'] . "</b>", strtolower($tweet['_source']['product_description'])) ?></p>
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

@extends('crudbooster::admin_template')
@section('content')
    <div class="col-12">
        <div class="table-responsive">
            <h4 class="text-right"> (<?php echo number_format($data_fuzzy['hits']['total']['value'] ?? 0); ?>) نتائج
                البحث</h4>
            <table class="table table-striped table-bordered text-right" width="100%">
                <tbody>
                @if(isset($data_fuzzy['hits']['hits']))
                    @foreach ($data_fuzzy['hits']['hits'] as $tweet)
                        <tr>
                            <td>
                                <h3>
                                    <?php echo str_replace(strtolower($_GET['keyword']), "<b>" . $_GET['keyword'] . "</b>", strtolower($tweet['_source']['product_name'])) ?>
                                </h3>
                                <p><?php echo str_replace(strtolower($_GET['keyword']), "<b>" . $_GET['keyword'] . "</b>", strtolower($tweet['_source']['product_description'])) ?></p>
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


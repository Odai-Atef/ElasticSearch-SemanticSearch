<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>

    <title>Search Engine Products</title>
</head>
<body>

<div class="container" style="margin-top: 50px;">
    <div class="row">
        <div class="col-12"><h2 class="text-center">Search Engine Demo</h2></div>
        <div class="col-12">
            <form method="get">
                <div id="custom-search-input">
                    <div class="input-group">
                        <input id="keyword" value="<?php echo isset($_GET['keyword']) ? $_GET['keyword'] : "" ?>" name="keyword"
                               type="text" class="search-query form-control" placeholder="Search"/>
                        <span class="input-group-btn">
                        <button class="btn btn-lg btn-success" type="submit">
                            Search
                        </button>
                    </span>
                    </div>
                </div>
            </form>
        </div>
        <?php
        if (isset($_GET['keyword']) && $_GET['keyword'] != "") {
            $stop_words=file_get_contents("http://3.125.9.240/ElasticSearch-SemanticSearch/stop_words/public/index.php/api/words");
            $vector = json_decode(file_get_contents("http://3.125.9.240:5000/vector/" . urlencode(trim($_GET["keyword"]))), true);
            $data_fuzzy = postReq([
                "size" => 100,
                "query" => [
                    "query_string" => [
                        "fields" => ["product_name", "product_description"],
                        "query" => trim($_GET['keyword']) . " AND !($stop_words)",
                        "fuzziness" => "AUTO"
                    ],
                    // "query_string" =>[
                    //     "query" => "!($stop_words)",
                    //     "fields"  => ["product_name"]
                    // ]
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
            $data_search = postReq([
                "query" => [
                    "bool" => [
                        "must" => [
                            [
                                "wildcard" => [
                                    "product_name" => "*" .trim($_GET['keyword']) . "*"
                                ]
                            ],
                            [
                                "wildcard" => [
                                    "product_description" => "*" .trim($_GET['keyword']) . "*"
                                ]
                            ]
                        ]
                    ]
                ]
            ]);


            $data = postReq([
                "size" => 100,
                "query" => [
                    "script_score" => [
                        "query" => [
                            "match_all" => ["boost" => 0]
                        ],
                        "script" => [
                            "source" => "cosineSimilarity(params.query_vector, 'product_name_vector') + 1.0",
                            "params" => [
                                "query_vector" => $vector
                            ]
                        ],
                        // "min_score"=> 2
                    ]
                ],
                "_source" => [
                    "includes" => [
                        "product_name", "product_description", "product_id"
                    ]
                ]



            ]);


        }
        function agg($data){
            $data["aggs"]=[
                "my-agg-name"=>[
                    "terms"=>[
                        "field"=>"category_id"
                    ]
                ]
            ];
            $data['size']=0;
            return postReq($data);
        }
        function postReq($data)
        {
            $data = json_encode($data);
            // echo $data."</br>";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://3.125.9.240:9221/products2/_search?scroll=1m");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLINFO_CONTENT_TYPE, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            return json_decode($result, true);
        }

        ?>


        <div class="col-12" style="margin-top: 50px;">
            <div class="table-responsive">
                <h3 class="text-left">Fuzzy Search (<?php echo number_format($data_fuzzy['hits']['total']['value']??0); ?>)</h3>
                <table class="table table-striped table-bordered" width="100%">
                    <tbody>
                    <?php
                    if (isset($data_fuzzy['hits']['hits']))
                        foreach ($data_fuzzy['hits']['hits'] as $tweet) {
                            ?>
                            <tr>
                                <td>
                                    <h5>
                                        <?php echo str_replace(strtolower($_GET['keyword']), "<b>" . $_GET['keyword'] . "</b>", strtolower($tweet['_source']['product_name'])) ?>
                                    </h5>
                                    <p><?php echo str_replace(strtolower($_GET['keyword']), "<b>" . $_GET['keyword'] . "</b>",strtolower($tweet['_source']['product_description'])) ?></p></td>
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



    </div>
</div>
    <script >
        
        $( function() {
  
   

    $("#keyword").autocomplete({
      source: function( request, response ) {
        $.ajax( {
          url: "./auto_complete.php",
          dataType: "json",
          data: {
            keyword: request.term
          },
          success: function( data ) {
            response( data );
          }
        } );
      },
      minLength: 1,
      select: function( event, ui ) {
        log( "Selected: " + ui.item.value + " aka " + ui.item.id );
      }
    } );



  } );
        // $(document).ready(function(){

        //     $("#keyword").on("keyup",function(){
        //         var v=$(this).val();
        //         if(v.length >3 ){
        //             $.get("./auto_complete.php?keyword="+v).then((res)=>{
        //                 availableTags=res;
        //                 console.log(res);
        //             });
        //         }
        //     })

        // });
       

    </script>

</body>
</html>

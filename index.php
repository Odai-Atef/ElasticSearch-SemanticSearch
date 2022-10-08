<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <title>Semantic Search</title>
</head>
<body>

<div class="container" style="margin-top: 50px;">
    <div class="row">
        <div class="col-12"><h2>Semantic search for premier league tweets</h2></div>
        <div class="col-12">
            <form method="get">
                <div id="custom-search-input">
                    <div class="input-group">
                        <input value="<?php echo isset($_GET['keyword']) ? $_GET['keyword'] : "" ?>" name="keyword"
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
            $vector = json_decode(file_get_contents("http://3.125.9.240:5000/vector/" . urlencode($_GET["keyword"])), true);
            $data = postReq([
                "size" => 1000,
                "query" => [
                    "script_score" => [
                        "query" => [
                            "match_all" => ["boost" => 1.2]
                        ],
                        "script" => [
                            "source" => "cosineSimilarity(params.query_vector, 'Doc_vector') + 0.5",
                            "params" => [
                                "query_vector" => $vector
                            ]
                        ]
                    ]
                ],
                "_source" => [
                    "includes" => [
                        "Document_name"
                    ]
                ]
            ]);


        }
        function postReq($data)
        {
            $data = json_encode($data);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://3.125.9.240:9221/tweets/_search");
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
            <div class="table-responsive" id="sailorTableArea">
                <table id="sailorTable" class="table table-striped table-bordered" width="100%">
                    <tbody>
                    <?php
                    if (isset($data['hits']['hits']))
                        foreach ($data['hits']['hits'] as $tweet) {
                            ?>
                            <tr>
                                <td><?php echo str_replace(strtolower($_GET['keyword']), "<b>" . $_GET['keyword'] . "</b>", strtolower($tweet['_source']['Document_name'])) ?></td>
                            </tr>
                        <?php }else{
                            ?>
                        <tr ><td colspan="2">No Result Found</td></tr>
                    <?php
                    } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>

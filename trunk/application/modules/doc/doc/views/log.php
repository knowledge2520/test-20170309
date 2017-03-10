<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>The Log</title>

    <!-- Bootstrap Core CSS -->
    <link href="<?php echo siteURL();?>themes/public/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        body {
            padding-top: 70px;
            /* Required padding for .navbar-fixed-top. Remove if using .navbar-static-top. Change if height of navigation changes. */
        }
    </style>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body>

<!-- Navigation -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container">
        <!-- Brand and toggle get grouped for better mobile display -->

    </div>
    <!-- /.container -->
</nav>

<!-- Page Content -->
<div class="container">

    <div class="row">
        <div class="col-lg-12">
            <form method="post" name="thelog_form" action="<?php echo site_url('doc/log/index'); ?>">
                <div class="form-group">
                    <label for="exampleInputEmail1">Search</label>
                    <input type="text" class="form-control" id="exampleInputEmail1" placeholder="search..." name="keyword" value="<?php echo !empty($keyword) ? $keyword : ''; ?>">
                    <input type="hidden" name="start" value="<?php echo $start; ?>">
                </div>
                <button type="button" class="logSearch btn btn-default">Search</button>
            </form>
            <p>&nbsp;</p>
        </div>
        <div class="col-lg-12">
            <?php if($items): ?>
            <?php foreach($items as $item): ?>
            <div class="panel panel-info">
                <div class="panel-heading">
                    <a class="btn btn-primary pull-left" role="button" data-toggle="collapse" href="#collapseExample<?php echo $item->id ?>" aria-expanded="false" aria-controls="collapseExample<?php echo $item->id ?>">
                        <?php echo $item->apiUrl; ?>
                    </a>
                    <span class="pull-right"><?php echo $item->id; ?></span>
                    <div class="clearfix"></div>
                </div>
                <div class="panel-body">
                    <div class="collapse" id="collapseExample<?php echo $item->id ?>">
                        <div class="well">
                            <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseRequest<?php echo $item->id ?>" aria-expanded="false" aria-controls="collapseRequest<?php echo $item->id ?>">
                            Request Data
                            </button>
                            <div class="collapse" id="collapseRequest<?php echo $item->id ?>">
                                <pre><?php echo json_encode(json_decode($item->requestData), JSON_PRETTY_PRINT); ?></pre>
                            </div>
                        </div>
                        <div class="well">
                            <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseResponse<?php echo $item->id ?>" aria-expanded="false" aria-controls="collapseResponse<?php echo $item->id ?>">
                                Response Data
                            </button>
                            <div class="collapse" id="collapseResponse<?php echo $item->id ?>">
                                <pre><?php echo json_encode(json_decode($item->responseData), JSON_PRETTY_PRINT); ?></pre>
                            </div>
                        </div>
                        <div class="well">
                            <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseFile<?php echo $item->id ?>" aria-expanded="false" aria-controls="collapseFile<?php echo $item->id ?>">
                                Request File
                            </button>
                            <div class="collapse" id="collapseFile<?php echo $item->id ?>">
                                <pre><?php echo !empty($item->requestFile) ? json_encode(json_decode($item->requestFile), JSON_PRETTY_PRINT) : ''; ?></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <nav aria-label="Page navigation" class="my-paging"><?php //echo $pagging->create_links()?></nav>
            <?php endif; ?>
        </div>
    </div>
    <!-- /.row -->

</div>
<!-- /.container -->

<!-- jQuery Version 1.11.1 -->
<script src="<?php echo siteURL();?>themes/public/js/jquery-1.10.2.min.js"></script>

<!-- Bootstrap Core JavaScript -->
<script src="<?php echo siteURL();?>themes/public/js/bootstrap.min.js"></script>

<script>
    $(document).ready(function() {
       $('body').on('click', 'nav.my-paging li a', function(e) {
           e.preventDefault();
           $("input[name=start]").val($(this).attr('data-ci-pagination-page'));
           //$('form[name=thelog_form]').attr('action', $(this).attr('href'));
           $('form[name=thelog_form]').submit();
       });
        $('body').on('click', '.logSearch', function(e) {
            $("input[name=start]").val(0);
            //$('form[name=thelog_form]').attr('action', '');
            $('form[name=thelog_form]').submit();
        });
    });
</script>

</body>

</html>

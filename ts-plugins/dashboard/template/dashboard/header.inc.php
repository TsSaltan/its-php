<?$this->incFunctions()?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?=$this->title?></title>

    <?jsFrame()?>

    <!-- Bootstrap Core CSS -->
    <?$this->css(
        "css/style.css", 
        "css/bootstrap.min.css", 
        "css/metisMenu.min.css", 
        "css/timeline.css", 
        "css/startmin.css",
        "css/bootstrap-social.css",
        "css/morris.css",
        "css/font-awesome.min.css",
        "css/dataTables/dataTables.bootstrap.css",
        "css/dataTables/dataTables.responsive.css"
    )?>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->


    <!-- jQuery -->
    <?$this->js("js/jquery.min.js")?>

    <!-- Bootstrap Core JavaScript -->
    <?$this->js("js/bootstrap.min.js")?>

    <!-- Metis Menu Plugin JavaScript -->
    <?$this->js("js/metisMenu.min.js")?>

    <!-- Custom Theme JavaScript -->
    <?$this->js("js/startmin.js")?>

    <?$this->js('js/dataTables/jquery.dataTables.min.js')?>
    <?$this->js('js/dataTables/dataTables.bootstrap.min.js')?>

    <!-- JSON editor -->
    <?$this->css("css/jsoneditor.css")?>
    <?$this->js("js/jquery.jsoneditor.min.js")?>

    <?$this->hook("header")?>
</head>
<body>


<div id="preloader"></div>
<div id="wrapper">
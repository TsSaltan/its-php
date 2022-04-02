<?php $this->incFunctions(); ?>
<!DOCTYPE html>
<html lang="<?=$currentLang?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title><?=$this->title?></title>

    <?php jsFrame(); ?>

    <!-- Bootstrap Core CSS -->
    <?php $this->css(
        "css/style.css", 
        "cdn:bootstrap@3.3.2/dist/css/bootstrap.min.css", 
        "cdn:metismenu@1.1.3/dist/metisMenu.min.css", 
        "css/timeline.css", 
        "css/startmin.css",
        "css/bootstrap-social.css",
        "css/morris.css",
        "cdn:font-awesome@4.7.0/css/font-awesome.css",
        "css/dataTables/dataTables.bootstrap.css",
        "css/dataTables/dataTables.responsive.css"
    ); ?>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <?php $this->js("cdn:html5shiv@3.7.3/src/html5shiv.js"); ?>
    <?php $this->js("cdn:respond.js@1.4.2/src/respond.js"); ?>
    <![endif]-->


    <!-- jQuery -->
    <?php $this->js("cdn:jquery@2.1.3/dist/jquery.min.js"); ?>

    <!-- Bootstrap Core JavaScript -->
    <?php $this->js("cdn:bootstrap@3.3.2/dist/js/bootstrap.min.js"); ?>

    <!-- Metis Menu Plugin JavaScript -->
    <?php $this->js("cdn:metismenu@1.1.3/dist/metisMenu.min.js"); ?>

    <!-- Custom Theme JavaScript -->
    <?php $this->js("js/startmin.js"); ?>

    <?php $this->js('cdn:datatables@1.10.18/media/js/jquery.dataTables.js'); ?>
    <?php $this->js('js/dataTables/dataTables.bootstrap.min.js'); ?>

    <!-- JSON editor -->
    <?php $this->css("css/jsoneditor/jsoneditor.css"); ?>
    <?php $this->js("js/jsoneditor.js"); ?>

    <?php $this->themeCSS(); ?>
    <?php $this->hook("header"); ?>
</head>
<body>


<div id="preloader"></div>
<div id="wrapper">
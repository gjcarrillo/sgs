<!DOCTYPE html>
<html  ng-app="sgdp" >
    <head>
        <!--Import Google Icon Font-->
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
        <!-- Import Google Roboto Font Family -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,400italic">
        <!-- Import jQuery -->
        <script type="text/javascript" src="<?php echo base_url(); ?>js/jquery-2.2.4.js"></script>
        <!-- Angular Material css -->
        <link rel="stylesheet" href="<?php echo base_url(); ?>css/angular/angular-material.min.css">
        <!-- Angular Material requires Angular.js Libraries -->
        <script type="text/javascript" src="<?php echo base_url(); ?>js/angular/angular.min.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/angular/angular-animate.min.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/angular/angular-aria.min.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/angular/angular-messages.min.js"></script>
        <!-- Angular Material Library -->
        <script type="text/javascript" src="<?php echo base_url(); ?>js/angular/angular-material.min.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/angular/angular-ui-router.min.js"></script>
        <!-- App module, controllers & utilities -->
        <script type="text/javascript" src="<?php echo base_url(); ?>js/angular/myApp.module.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/cookies.min.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/angular/login/login.module.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/angular/login/authenticate.factory.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/angular/login/controllers/LoginController.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/angular/home/controllers/UserHomeController.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/angular/home/controllers/AgentHomeController.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/angular/home/controllers/ManagerHomeController.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/angular/history/controllers/HistoryController.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/angular/users/controllers/UserInfoController.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/angular/generator/controllers/DocumentGenerator.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/angular/angular-ui-router.min.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/webcam.min.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/moment/moment.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/chart.js/dist/Chart.min.js"></script>
        <!-- Import Angular CSV for excel documents generation -->
        <script type="text/javascript" src="<?php echo base_url(); ?>js/ng-csv/angular-sanitize.min.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/ng-csv/ng-csv.min.js"></script>
        <!-- Import Angular File Upload -->
        <script type="text/javascript" src="<?php echo base_url(); ?>js/ng-file-upload/ng-file-upload.min.js"></script>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/ng-file-upload-shim/ng-file-upload-shim.min.js"></script>
        <!-- Import trip.js for HELPS -->
        <link type="text/css" rel="stylesheet" href="<?php echo base_url(); ?>css/trip.js/trip.css"/>
        <script type="text/javascript" src="<?php echo base_url(); ?>js/trip.js/trip.js"></script>
        <!--Import custom style.css -->
        <link type="text/css" rel="stylesheet" href="<?php echo base_url(); ?>css/style.css"/>
        <!--Let browser know website is optimized for mobile-->
        <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"/> -->
        <meta charset="utf-8">
        <title>SGDP</title>
</head>
<body>
    <ui-view autoscroll="false"></ui-view>

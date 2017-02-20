<!DOCTYPE html>
<html  ng-app="sgdp" >
<head>
    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Import Google Roboto Font Family -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,400italic">

    <!-- Angular Material Library -->
    <link rel="stylesheet" href="<?php echo base_url(); ?>node_modules/angular-material/angular-material.min.css">

    <!-- Modified version of trip js plugin. Fixed for truly responsive design :) -->
    <link type="text/css" rel="stylesheet" href="<?php echo base_url(); ?>lib/trip.js/trip.css"/>

    <!--Import custom style.css -->
    <link type="text/css" rel="stylesheet" href="<?php echo base_url(); ?>css/style.css"/>

    <link href="images/favicon.ico" type="image/x-icon" rel="shortcut icon">

    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta charset="utf-8">
    <title>IPAPEDI</title>
</head>
<body>
    <div ui-view="content"></div>
    <div ui-view="footer"></div>


    <!-- Import jQuery -->
    <script type="text/javascript" src="<?php echo base_url(); ?>node_modules/jquery/dist/jquery.min.js"></script>

    <!-- Angular libraries -->
    <script type="text/javascript" src="<?php echo base_url(); ?>node_modules/angular/angular.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>node_modules/angular-animate/angular-animate.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>node_modules/angular-aria/angular-aria.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>node_modules/angular-messages/angular-messages.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>node_modules/angular-cookies/angular-cookies.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>node_modules/angular-ui-router/release/angular-ui-router.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>node_modules/angular-sanitize/angular-sanitize.min.js"></script>

    <!-- Angular Material Library -->
    <script type="text/javascript" src="<?php echo base_url(); ?>node_modules/angular-material/angular-material.min.js"></script>

    <!-- App module, controllers & utilities -->
    <script type="text/javascript" src="<?php echo base_url(); ?>js/app.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>js/services/service-auth.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>js/services/service-constants.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>js/services/service-utils.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>js/services/service-requests.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>js/services/service-file-upload.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>js/services/service-helps.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>js/services/service-manager.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>js/services/service-agent.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>js/services/service-config.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>js/controllers/controller-login.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>js/controllers/controller-home-applicant.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>js/controllers/controller-home-agent.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>js/controllers/controller-home-manager.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>js/controllers/controller-history.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>js/controllers/controller-user-info.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>js/controllers/controller-perspective.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>js/controllers/controller-validation.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>js/controllers/controller-delete.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>js/controllers/controller-incompatibility.js"></script>

    <!-- Plugins -->
    <script type="text/javascript" src="<?php echo base_url(); ?>node_modules/moment/min/moment.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>node_modules/chart.js/dist/Chart.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>node_modules/ng-file-upload/dist/ng-file-upload.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>node_modules/ng-file-upload/dist/ng-file-upload-shim.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>node_modules/lodash/lodash.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>node_modules/mobile-detect/mobile-detect.min.js"></script>
    <script type="text/javascript" src="<?php echo base_url(); ?>node_modules/bowser/bowser.min.js"></script>

    <!-- Modified version of trip js plugin. Fixed for truly responsive design :) -->
    <script type="text/javascript" src="<?php echo base_url(); ?>lib/trip.js/trip.js"></script>
</body>
</html>

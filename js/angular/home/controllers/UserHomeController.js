angular
    .module('sgdp')
    .controller('UserHomeController', userHome);

userHome.$inject = ['$scope', '$rootScope', '$http', '$cookies', '$timeout', '$mdSidenav'];

function userHome($scope, $rootScope, $http, $cookies, $timeout, $mdSidenav) {
    'use strict';
    $scope.loading = true;
    $scope.selectedReq = -1;
    $scope.requests = [];
    $scope.docs = [];
    $scope.showList = false;
    $scope.fetchError = '';
    // contentAvailable will indicate whether sidenav can be visible
    $scope.contentAvailable = false;
    // contentLoaded will indicate whether sidenav can be locked open
    $scope.contentLoaded = false;

    var fetchId = $cookies.getObject('session').id;
    $http.get('index.php/home/UserHomeController/getUserRequests', {params:{fetchId:fetchId}})
        .then(function (response) {
            if (response.data.message === "success") {
                $scope.requests = response.data.requests;
                $scope.contentAvailable = true;
                $timeout(function() {
                    $scope.contentLoaded = true;
                    $mdSidenav('left').open();
                    $timeout(function() {
                        if ($scope.requests.length > 0) {
                            $scope.showList = true;
                            // $scope.selectRequest(0);
                        }
                    }, 600);
                }, 600);
            } else {
                $scope.fetchError = response.data.message;
            }
            $scope.loading = false;
        });

    $scope.toggleList = function() {
        $scope.showList = !$scope.showList;
    };

    $scope.getSidenavHeight = function() {
        return {
            // 129 = header and footer height, approx
            'height':($(window).height() - 129)
        };
    };

    $scope.getDocumentContainerStyle = function() {
        return {
            'background-color': '#F5F5F5',
            'max-height':($(window).height() - 129)
        };
    };

    $scope.selectRequest = function(req) {
        $scope.selectedReq = req;
        if (req != -1) {
            $scope.docs = $scope.requests[req].docs;
        }
        $mdSidenav('left').toggle();
    };

    // Helper function for formatting numbers with leading zeros
    $scope.pad = function(n, width, z) {
        z = z || '0';
        n = n + '';
        return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
    };

    $scope.downloadDoc = function(doc) {
        window.open('index.php/home/UserHomeController/download?lpath=' + doc.lpath, '_blank');
    };

    $scope.downloadAll = function() {
        // Bits of pre-processing before passing objects to URL
        var paths = new Array();
        angular.forEach($scope.docs, function(doc) {
            paths.push(doc.lpath);
        });
        location.href = 'index.php/home/UserHomeController/downloadAll?docs=' + JSON.stringify(paths);
    };

    $scope.openMenu = function() {
       $mdSidenav('left').toggle();
    };

    $scope.showHelp = function() {
        var options = {
            showNavigation : true,
            showCloseBox : true,
            delay : -1,
            tripTheme: "dark",
            prevLabel: "Anterior",
            nextLabel: "Siguiente",
            finishLabel: "Entendido"
        };
        if ($scope.docs.length == 0) {
            // User has not selected any request yet, tell him to do it.
            showSidenavHelp(options);
        } else {
            // Guide user through request selection's possible actions
            showRequestHelp(options);
        }
    };

    /**
     * Shows tour-based help of side navigation panel
     * @param options: Obj containing tour.js options
     */
    function showSidenavHelp(options) {
        if ($mdSidenav('left').isLockedOpen()) {
            options.showHeader = true;
            var tripToShowNavigation = new Trip([
                { sel : $("#requests-list"),
                    content : "Seleccione alguna de sus solicitudes en la lista para ver más detalles.",
                    position : "e", expose : true, header: "Panel de navegación", animation: 'fadeInUp' }
            ], options);
            tripToShowNavigation.start();
        } else {
            var tripToShowNavigation = new Trip([
                { sel : $("#nav-panel"),
                    content : "Haga click en el ícono para abrir el panel de navegación" +
                    " y seleccionar alguna de sus solicitudes para ver más detalles",
                    position : "e", animation: 'fadeInUp'}
            ], options);
            tripToShowNavigation.start();
        }
    }

    /**
     * Shows tour-based help of selected request details section.
     * @param options: Obj containing tour.js options
     */
    function showRequestHelp(options) {
        options.showHeader = true;
        // options.showSteps = true;
        var tripToShowNavigation = new Trip([
            // Request summary information
            { sel : $("#request-summary"), content : "Aquí se muestra información acerca de " +
                "la fecha de creación, monto solicitado por usted, y un posible comentario.",
                position : "s", header: "Resumen de la solicitud", expose : true },
            // Request status information
            { sel : $("#request-status-summary"), content : "Esta sección provee información " +
                "acerca del estatus de su solicitud.",
                position : "s", header: "Resumen de estatus", expose : true, animation: 'fadeInDown' },
            // Request documents information
            { sel : $("#request-docs"), content : "Éste y los siguientes items contienen " +
                "el nombre y una posible descripción de cada documento en su solicitud. " +
                "Puede verlos/descargarlos haciendo click encima de ellos.",
                position : "s", header: "Documentos", expose : true, animation: 'fadeInDown' },
            // Download as zip information
            { sel : $("#request-summary-actions"), content : "También puede descargar todos los documentos " +
                "haciendo click aquí.",
                position : "w", header: "Descargar todo", expose : true, animation: 'fadeInLeft' }
        ], options);
        tripToShowNavigation.start();
    }
}

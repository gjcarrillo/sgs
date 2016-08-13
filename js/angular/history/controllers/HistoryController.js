angular
    .module('sgdp')
    .controller('HistoryController', history);

history.$inject = ['$scope', '$rootScope', '$http', '$mdBottomSheet'];

function history($scope, $rootScope, $http, $mdBottomSheet) {
    'use strict';

    // If no data has been sent, show nothing.
    if (sessionStorage.getItem("requests") === null &&
        sessionStorage.getItem("pendingRequests") === null) { return; }

    $scope.loading = true;
    // Take the stored data of interest
    var requests = JSON.parse(sessionStorage.getItem("requests"));
    var selectedReq = parseInt(sessionStorage.getItem("selectedReq"));
    if (requests === null) {
        requests = JSON.parse(sessionStorage.getItem("pendingRequests"));
        selectedReq = parseInt(sessionStorage.getItem("selectedPendingReq"));
    }

    $http.get('index.php/history/HistoryController/fetchRequestHistory', {params:requests[selectedReq]})
        .then(function (response) {
            if (response.data.message === "success") {
                $scope.history = response.data.history;
                console.log($scope.history);
            }
            $scope.loading = false;
        });

    $scope.showListBottomSheet = function(selectedHistory) {
        $mdBottomSheet.show({
            templateUrl: 'index.php/history/DetailsBottomSheetController',
            locals:{
                actions:selectedHistory.actions
            },
            controller: ListBottomSheetCtrl
        });

        function ListBottomSheetCtrl($mdBottomSheet, $scope, actions) {
            $scope.actions = actions;
        }
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
        showHistoryHelp(options);
    };

    function showHistoryHelp(options) {
        options.showHeader = true;
        var tripToShowNavigation = new Trip([
            { sel : $("#action-summary"),
                content : "Por cada acción realizada, se proporciona el nombre del " +
                "usuario que ejecutó la acción, tipo de acción realizada y fecha-hora de ejecución." +
                " Para ver más detalles acerca de la acción realizada, haga click encima del item.",
                position : "s", header: "Resumen de acciones", animation: 'fadeInUp' },
            { sel : $("#filter"),
                content : "También puede filtrar la lista de acciones escribiendo contenido clave. " +
                "Ej: 05/08/2016",
                position : "s", header: "Filtro de búsqueda", animation: 'fadeInUp' }
        ], options);
        tripToShowNavigation.start();
    }
}

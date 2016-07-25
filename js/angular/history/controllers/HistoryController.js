angular
    .module('sgdp')
    .controller('HistoryController', history);

history.$inject = ['$scope', '$rootScope', '$http', '$mdBottomSheet'];

function history($scope, $rootScope, $http, $mdBottomSheet) {
    'use strict';
    $scope.loading = true;
    // Take the stored data of interest
    var requests = JSON.parse(sessionStorage.getItem("requests"));
    var selectedReq = parseInt(sessionStorage.getItem("selectedReq"));

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
                actions:$scope.history[selectedHistory].actions
            },
            controller: ListBottomSheetCtrl
        });

        function ListBottomSheetCtrl($mdBottomSheet, $scope, actions) {
            $scope.actions = actions;
        }
    };
}

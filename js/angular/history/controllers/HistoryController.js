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
                $scope.historyList = response.data.historyList;
                console.log($scope.historyList);
            }
            $scope.loading = false;
        });

    $scope.showListBottomSheet = function() {
      $mdBottomSheet.show({
        templateUrl: 'index.php/history/DetailsBottomSheetController',
        //controller: 'ListBottomSheetCtrl'
      }).then(function(clickedItem) {
      });
    };
}

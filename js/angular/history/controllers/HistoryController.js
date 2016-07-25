angular
    .module('sgdp')
    .controller('HistoryController', history);

history.$inject = ['$scope', '$rootScope', '$http', '$mdBottomSheet'];

function history($scope, $rootScope, $http, $mdBottomSheet) {
    'use strict';

    // Take the stored data of interest
    $scope.requests = JSON.parse(sessionStorage.getItem("requests"));
    $scope.selectedReq = parseInt(sessionStorage.getItem("selectedReq"));


    $scope.showListBottomSheet = function() {
      $mdBottomSheet.show({
        templateUrl: 'index.php/history/DetailsBottomSheetController',
        //controller: 'ListBottomSheetCtrl'
      }).then(function(clickedItem) {
      });
    };
}

angular
    .module('sgdp')
    .controller('HistoryController', history);

history.$inject = ['$scope', '$rootScope', '$http', '$mdBottomSheet'];

function history($scope, $rootScope, $http, $mdBottomSheet) {
    'use strict';

    // Check if there is stored data
    // var requests = JSON.parse(sessionStorage.getItem("requests"));
    // if (requests != null) {
    //     $scope.requests = requests;
    //     $scope.fetchId = sessionStorage.getItem("fetchId");
    //     // fetchId is used for several database queries.
    //     // that is why we don't use searchInput value, which is bind to search input.
    //     $scope.searchInput = $scope.fetchId;
    //     var selectedReq = sessionStorage.getItem("selectedReq");
    //     if (selectedReq != null) {
    //         $scope.selectedReq = parseInt(selectedReq);
    //         if ($scope.selectedReq != -1) {
    //             $scope.docs = $scope.requests[$scope.selectedReq].docs;
    //         }
    //     }
    // }
    $scope.showListBottomSheet = function() {
      $mdBottomSheet.show({
        templateUrl: 'index.php/history/DetailsBottomSheetController',
        //controller: 'ListBottomSheetCtrl'
      }).then(function(clickedItem) {
      });
    };
}

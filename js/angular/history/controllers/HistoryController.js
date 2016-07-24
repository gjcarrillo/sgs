angular
    .module('sgdp')
    .controller('HistoryController', history);

history.$inject = ['$scope', '$rootScope', '$http', '$mdBottomSheet'];

function history($scope, $rootScope, $http, $mdBottomSheet) {
    'use strict';

    $scope.showListBottomSheet = function() {
      $mdBottomSheet.show({
        templateUrl: 'index.php/history/DetailsBottomSheetController',
        //controller: 'ListBottomSheetCtrl'
      }).then(function(clickedItem) {
      });
    };
}

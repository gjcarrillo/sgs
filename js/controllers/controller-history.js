angular
    .module('sgdp')
    .controller('HistoryController', history);

history.$inject = ['$scope', '$http', '$mdBottomSheet', '$timeout'];

function history($scope, $http, $mdBottomSheet, $timeout) {
    'use strict';
    // This will enable / disable search bar in mobile screens
    $scope.searchEnabled = false;

    // If no data has been sent, show nothing.
    if (sessionStorage.getItem("req") === null) { return; }

    $scope.loading = true;
    // Take the stored data of interest
    var request = JSON.parse(sessionStorage.getItem("req"));

    $http.get('HistoryController/fetchRequestHistory', {params:request})
        .then(function (response) {
            if (response.data.message === "success") {
                $scope.history = response.data.history;
            }
            $scope.loading = false;
        });

    $scope.showListBottomSheet = function(selectedHistory) {
        $mdBottomSheet.show({
            templateUrl: 'DetailsBottomSheetController',
            locals:{
                actions:selectedHistory.actions
            },
            controller: ListBottomSheetCtrl
        });

        function ListBottomSheetCtrl($scope, actions) {
            $scope.actions = actions;
        }
    };

    // Enables / disables search bar (for mobile screens)
    $scope.toggleSearch = function() {
        $scope.searchEnabled = !$scope.searchEnabled;
        $timeout(function () {
            $("#filter-input").focus();
        }, 300);
    };

    $scope.clearInput = function() {
        $scope.filterInput = '';
    };
}

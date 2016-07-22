angular
    .module('sgdp')
    .controller('HomeController', home);

home.$inject = ['$scope', '$rootScope', '$timeout', '$mdDialog'];

function home($scope, $rootScope, $timeout, $mdDialog) {
    'use strict';

    $scope.isOpen = false;

    $scope.getSidenavHeight = function() {
        return {
            // 129 = header and footer height, approx
            'height':($(window).height() - 129)
        };
    }

    $scope.getDocumentContainerStyle = function() {
        return {
            'background-color': '#F5F5F5',
            'max-height':($(window).height() - 129)
        };
    }

    // On opening, add a delayed property which shows tooltips after the speed dial has opened
    // so that they have the proper position; if closing, immediately hide the tooltips
    $scope.$watch('fab.isOpen', function(isOpen) {
        if (isOpen) {
            $timeout(function() {
                $scope.tooltipVisible = true;
            }, 600);
        } else {
            $scope.tooltipVisible = false;
        }
    });

    /**
    * Custom dialog for creating a new request
    */
    $scope.openNewRequestDialog = function($event) {
        // $scope.documentsContainer = [];
        var parentEl = angular.element(document.body);
        $mdDialog.show({
            parent: parentEl,
            targetEvent: $event,
            templateUrl: 'templates/dialogs/newRequest.html',
            clickOutsideToClose: true,
            // fullscreen: true,
            controller: DialogController
        });
        // Isolated dialog controller
        function DialogController($scope, $mdDialog) {
            $scope.enabledComment = -1;
            
            $scope.closeDialog = function() {
                $mdDialog.hide();
            };

            $scope.$watch('files.length',function(newVal,oldVal) {
                console.log($scope.files);
            });

            $scope.removeDoc = function(index) {
                $scope.files.splice(index, 1);
            }
        }
    };

}

angular
    .module('sgdp')
    .controller('HomeController', home);

home.$inject = ['$scope', '$rootScope', '$timeout'];

function home($scope, $rootScope, $timeout) {
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
          console.log("Opened!");
        $timeout(function() {
          $scope.tooltipVisible = true;
        }, 600);
      } else {
          console.log("Closed!");
        $scope.tooltipVisible = false;
      }
    });

}

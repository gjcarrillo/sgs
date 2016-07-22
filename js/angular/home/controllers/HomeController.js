angular
    .module('sgdp')
    .controller('HomeController', home);

home.$inject = ['$scope', '$rootScope'];

function home($scope, $rootScope) {
    'use strict';

    $scope.isOpen = false;

    $scope.getSidenavHeight = function() {
        return {
            // 129 = header and footer height, approx
            'max-height':($(window).height() - 129)
        };
    }

    $scope.getDocumentContainerStyle = function() {
        return {
            'background-color': '#F5F5F5',
            'max-height':($(window).height() - 129)
        };
    }


}

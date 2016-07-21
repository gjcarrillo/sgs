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
            'height':($(window).height() - 129)
        };
    }

    $scope.getMainContentWidth = function() {
        var width = document.getElementById('sidenav').offsetWidth;
        console.log(width);
        return {
            'margin-left':width
        };
    }


}

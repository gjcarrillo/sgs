angular
    .module('sgdp')
    .controller('HomeController', home);

home.$inject = ['$scope', '$rootScope'];

function home($scope, $rootScope) {
    'use strict';

    $scope.isOpen = false;

}

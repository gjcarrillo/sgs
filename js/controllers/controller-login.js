angular
    .module('sgdp.login')
    .controller('LoginController', login);

login.$inject = ['$scope', '$rootScope', 'Auth', '$http'];

function login($scope, $rootScope, Auth, $http) {
    'use strict';
    $scope.idPrefix = "V";
    $rootScope.model = {};
    $scope.loginImagePath = "images/avatar_circle.png";

    $scope.login = function() {
        if (typeof $scope.model.login === "undefined" || $scope.model.login == "" ||
            typeof $scope.model.password === "undefined" || $scope.model.password == "") {
            $rootScope.model.loginError = "Debe llenar todos los campos";
        } else {
            Auth.login($scope.idPrefix + $scope.model.login, $scope.model.password);
            $rootScope.model.loginError = "";
        }
    };

    $scope.onIdOpen = function() {
        $scope.backup = $scope.idPrefix;
        $scope.idPrefix = null;
    };

    $scope.onIdClose = function() {
        if ($scope.idPrefix === null) {
            $scope.idPrefix = $scope.backup;
        }
    };
}

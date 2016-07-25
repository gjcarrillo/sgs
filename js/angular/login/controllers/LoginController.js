angular
    .module('sgdp.login')
    .controller('LoginController', login);

login.$inject = ['$scope', '$rootScope', 'auth', '$http'];

function login($scope, $rootScope, auth, $http) {
    'use strict';
    $rootScope.model = {};
    $scope.loginImagePath = "images/avatar_circle_grey.png";
    $scope.login = function() {
        if (typeof $scope.model.login === "undefined" || $scope.model.login == "" ||
            typeof $scope.model.password === "undefined" || $scope.model.password == "") {
            console.log("true");
            $rootScope.model.loginError = "Debe llenar todos los campos";
        } else {
            auth.login($scope.model.login, $scope.model.password);
            $rootScope.model = {};
            $rootScope.model.loginError = "";
        }
    };
}

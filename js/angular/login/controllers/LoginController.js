angular
    .module('sgdp.login')
    .controller('LoginController', login);

login.$inject = ['$scope', '$rootScope', 'auth', '$http'];

function login($scope, $rootScope, auth, $http) {
    'use strict';
    $rootScope.loading = false;
    $scope.loginImagePath = "images/icon-profile.png";
    $scope.login = function() {
        if (typeof $scope.model.login === "undefined" || $scope.model.login == "" ||
            typeof $scope.model.password === "undefined" || $scope.model.password == "") {
                $rootScope.model.loginError = "Debe ingresar todos los campos"
            } else {
            $rootScope.loading = true;
            auth.login($scope.model.login, $scope.model.password);
            $rootScope.model = {};
            $rootScope.model.loginError = "";
        }
    };
}

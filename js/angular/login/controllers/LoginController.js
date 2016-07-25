angular
    .module('sgdp.login')
    .controller('LoginController', login);

login.$inject = ['$scope', '$rootScope', 'auth', '$http'];

function login($scope, $rootScope, auth, $http) {
    'use strict';
    $scope.loading = false;
    $scope.model = {};
    $scope.loginImagePath = "images/avatar_circle_grey.png";
    $scope.login = function() {
        if (typeof $scope.model.login === "undefined" || $scope.model.login == "" ||
            typeof $scope.model.password === "undefined" || $scope.model.password == "") {
            console.log("true");
            $scope.model.loginError = "Debe llenar todos los campos";
        } else {
            $scope.loading = true;
            auth.login($scope.model.login, $scope.model.password);
            $scope.model = {};
            $scope.model.loginError = "";
        }
    };
}

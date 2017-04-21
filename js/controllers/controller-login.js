angular
    .module('sgdp.login')
    .controller('LoginController', login);

login.$inject = ['$scope', 'Auth', '$state', '$timeout', 'Utils', '$stateParams', 'Constants'];

function login($scope, Auth, $state, $timeout, Utils, $stateParams, Constants) {
    'use strict';
    $scope.idPrefix = "V";
    $scope.model = {};
    $scope.loginImagePath = "images/avatar_circle.png";

    // Check for cross-compatibility first!
    checkCompatibilityRequirements();

    // Transitioning....
    if ($stateParams.token) {
        Auth.verifyUser($stateParams.token).then (
            function (userType) {
                if (userType == Constants.Users.AGENT) {
                    $state.go('perspective');
                } else {
                    Auth.sendHome();
                }
            },
            function (error) {
                $scope.message = error;
            }
        );
    }

    $scope.login = function() {
        if (typeof $scope.model.login === "undefined" || $scope.model.login == "" ||
            typeof $scope.model.password === "undefined" || $scope.model.password == "") {
            $scope.model.loginError = "Debe llenar todos los campos";
        } else {
            $scope.loading = true;
            Auth.login($scope.idPrefix + $scope.model.login, $scope.model.password)
                .then (
                function () {
                    Auth.sendHome();
                    $timeout(function () {
                        $scope.loading = false;
                    }, 1000);
                },
                function (error) {
                    $scope.loading = false;
                    $scope.model.loginError = error;
                }
            );
            $scope.model.loginError = "";
        }
    };

    function checkCompatibilityRequirements() {
        var md = new MobileDetect(window.navigator.userAgent);
        var supported = Utils.getSupportedBrowsers();
        var browser = bowser.name;
        var version = bowser.version;
        if ((md.is('iPhone') && md.version('iPhone') < supported.iPhone) ||
            (md.is('AndroidOS') && md.version('Android') < supported.Android) ||
            (version < supported[browser])) {
            $state.go('incompatibility');
        }
    }
}

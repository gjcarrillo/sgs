/**
 * Created by Kristopher on 12/4/2016.
 */
angular
    .module('sgdp')
    .controller('ValidationController', validation);

validation.$inject = ['$scope', 'Validation', '$stateParams', 'Auth'];

function validation($scope, Validation, $stateParams, Auth) {

    $scope.loading = false;
    $scope.userLogged = Auth.isLoggedIn();
    $scope.validating = false;

    $scope.idPrefix = "V";
    $scope.model = {};
    $scope.userLogged = Auth.isLoggedIn();

    if ($scope.userLogged) {
        $scope.validating = true;
        Validation.validate($stateParams.token)
            .then(
            function () {
                $scope.validating = false;
            },
            function (errorMsg) {
                $scope.errorMsg = errorMsg;
                $scope.validating = false;
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
                    Validation.validate($stateParams.token)
                        .then(
                        function () {
                            $scope.eliminating = false;
                            $scope.userLogged = true;
                        },
                        function (error) {
                            $scope.userLogged = true;
                            $scope.errorMsg = error;
                        }
                    );
                },
                function (error) {
                    $scope.loading = false;
                    $scope.model.loginError = error;
                }
            );
            $scope.model.loginError = "";
        }
    };

    $scope.onIdOpen = function () {
        $scope.backup = $scope.idPrefix;
        $scope.idPrefix = null;
    };

    $scope.onIdClose = function () {
        if ($scope.idPrefix === null) {
            $scope.idPrefix = $scope.backup;
        }
    };

    $scope.go = function () {
        Auth.sendHome();
    };
}
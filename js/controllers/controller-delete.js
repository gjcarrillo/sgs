/**
 * Created by Kristopher on 12/10/2016.
 */
angular
    .module('sgdp')
    .controller('DeleteController', eliminate);

eliminate.$inject = ['$scope', '$stateParams', 'Auth', 'Requests'];

function eliminate($scope, $stateParams, Auth, Requests) {

    $scope.idPrefix = "V";
    $scope.model = {};
    $scope.eliminating = false;
    $scope.title = 'Eliminación de solicitud';
    $scope.userLogged = Auth.isLoggedIn();

    if ($scope.userLogged) {
        $scope.eliminating = true;
        Requests.deleteRequestJWT($stateParams.rid)
            .then(
            function () {
                $scope.eliminating = false;
            },
            function (error) {
                $scope.eliminating = false;
                $scope.errorMsg = error;
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
                    Requests.deleteRequestJWT($stateParams.rid)
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
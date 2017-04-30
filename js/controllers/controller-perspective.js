angular
    .module('sgdp.login')
    .controller('PerspectiveController', selection);

selection.$inject = ['$scope', '$state', 'Constants', 'Auth', 'Utils'];

function selection($scope, $state, Constants, Auth, Utils) {
    'use strict';

    var user = Auth.getLocalSession();

    $scope.welcomeMsg = "Bienvenido, " + user.firstName + ".";

    $scope.goBack = function () {
        $window.history.go(-1);
    };

    $scope.goAgent = function() {
        if (user.type == Constants.Users.AGENT) {
            $state.go("agentHome");
        } else {
            // re-write the session cookie
            user.type = Constants.Users.AGENT;
            $scope.loading = true;
            Auth.setLocalSession(user);
            Auth.updateSession(Constants.Users.AGENT).then (
                function () {
                    $state.go("agentHome");
                    $scope.loading = false;
                },
                function (error) {
                    $scope.loading = false;
                    Utils.handleError(error);
                }
            );
        }
    };

    $scope.goReviser = function() {
        if (user.type == Constants.Users.REVISER) {
            $state.go("reviserHome");
        } else {
            // re-write the session cookie
            user.type = Constants.Users.REVISER;
            $scope.loading = true;
            Auth.setLocalSession(user);
            Auth.updateSession(Constants.Users.REVISER).then (
                function () {
                    $state.go("reviserHome");
                    $scope.loading = false;
                },
                function (error) {
                    $scope.loading = false;
                    Utils.handleError(error);
                }
            );
        }
    };
}
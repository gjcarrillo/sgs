angular
    .module('sgdp.login')
    .controller('PerspectiveController', selection);

selection.$inject = ['$scope', '$state', 'Constants', 'Auth', 'Utils'];

function selection($scope, $state, Constants, Auth, Utils) {
    'use strict';

    var user = Auth.getLocalSession();

    $scope.welcomeMsg = "Bienvenido, " + user.firstName + ".";

    $scope.goAgent = function() {
        $state.go("agentHome");
    };

    $scope.goReviser = function() {
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
    };
}
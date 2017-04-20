angular
    .module('sgdp.login')
    .controller('PerspectiveController', selection);

selection.$inject = ['$scope', '$state', 'Constants', 'Auth'];

function selection($scope, $state, Constants, Auth) {
    'use strict';

    var user = Auth.getLocalSession();
    //var now = new Date();
    //user.timeToExpire = new Date(now.getFullYear()+1, now.getMonth(), now.getDate());

    $scope.welcomeMsg = "Bienvenido, " + user.firstName + ".";

    $scope.goApplicant = function() {
        // re-write the session cookie
        user.type = Constants.Users.APPLICANT;
        Auth.setLocalSession(user);
        Auth.updateSession(Constants.Users.APPLICANT)
            .then (
            function () {
                $state.go("applicantHome");
            },
            function (error) {
            }
        );
    };

    $scope.goAgent = function() {
        if (Auth.userType(Constants.Users.AGENT)) {
            $state.go("agentHome");
        } else {
            // re-write the session cookie
            user.type = Constants.Users.AGENT;
            Auth.setLocalSession(user);
            Auth.updateSession(Constants.Users.AGENT)
                .then (
                function () {
                    $state.go("agentHome");
                },
                function (error) {
                }
            );
        }
    };

    $scope.goManager = function() {
        if (Auth.userType(Constants.Users.MANAGER)) {
            $state.go('managerHome');
        } else {
            // re-write the session cookie
            user.type = Constants.Users.MANAGER;
            Auth.setLocalSession(user);
            Auth.updateSession(Constants.Users.MANAGER)
                .then (
                function () {
                    $state.go("managerHome");
                },
                function (error) {
                }
            );
        }
    };
}
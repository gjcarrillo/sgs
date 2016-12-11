angular
    .module('sgdp.login')
    .controller('PerspectiveController', selection);

selection.$inject = ['$scope', '$rootScope', '$state', '$cookies', 'Constants', 'Auth'];

function selection($scope, $rootScope, $state, $cookies, Constants, Auth) {
    'use strict';

    var now = new Date();
    var id = $cookies.getObject('session').id;
    var name = $cookies.getObject('session').name;
    var lastName = $cookies.getObject('session').lastName;
    var timeToExpire =  new Date(now.getFullYear()+1, now.getMonth(), now.getDate());

    $scope.welcomeMsg = "Bienvenido, " + name + ".";

    if (userType(Constants.Users.MANAGER)) {
        // User is manager, disable go-agent
        $("#go-agent").toggle();
        $("#agent-help").toggle();
    } else if (userType(Constants.Users.AGENT)) {
        // If user is agent, disable go-manager
        $("#go-manager").toggle();
        $("#manager-help").toggle();
    }
    $scope.goApplicant = function() {
        // re-write the session cookie
        $cookies.putObject('session', {
            id: id,
            type: Constants.Users.APPLICANT,
            name: name,
            lastName: lastName
        }, {
            expires : timeToExpire
        });
        Auth.updateSession(Constants.Users.APPLICANT)
            .then (
            function () {
                $state.go("applicantHome");
            },
            function (error) {
                console.log(error);
            }
        );
    };

    $scope.goAgent = function() {
        if (userType(Constants.Users.AGENT)) {
            $state.go("agentHome");
        } else {
            // re-write the session cookie
            $cookies.putObject('session', {
                id: id,
                type: Constants.Users.AGENT,
                name: name,
                lastName: lastName
            }, {
                expires : timeToExpire
            });
            Auth.updateSession(Constants.Users.AGENT)
                .then (
                function () {
                    $state.go("agentHome");
                },
                function (error) {
                    console.log(error);
                }
            );
        }
    };

    $scope.goManager = function() {
        if (userType(Constants.Users.MANAGER)) {
            $state.go('managerHome');
        } else {
            // re-write the session cookie
            $cookies.putObject('session', {
                id: id,
                type: Constants.Users.MANAGER,
                name: name,
                lastName: lastName
            }, {
                expires : timeToExpire
            });
            Auth.updateSession(Constants.Users.MANAGER)
                .then (
                function () {
                    $state.go("managerHome");
                },
                function (error) {
                    console.log(error);
                }
            );
        }
    };

    function userType(type) {
        return type == $cookies.getObject('session').type;
    }
}

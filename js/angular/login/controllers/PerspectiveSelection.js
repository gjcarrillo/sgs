angular
    .module('sgdp.login')
    .controller('PerspectiveSelection', selection);

selection.$inject = ['$scope', '$rootScope', '$state', '$cookies'];

function selection($scope, $rootScope, $state, $cookies) {
    'use strict';

    var now = new Date();
    var id = $cookies.getObject('session').id;
    var name = $cookies.getObject('session').name;
    var lastName = $cookies.getObject('session').lastName;
    var timeToExpire =  new Date(now.getFullYear()+1, now.getMonth(), now.getDate());

    $scope.welcomeMsg = "Bienvenido, " + name + ".";

    if (userType(2)) {
        // User is manager, disable go-agent
        $("#go-agent").toggle();
        $("#agent-help").toggle();
    } else if (userType(1)) {
        // If user is agent, disable go-manager
        $("#go-manager").toggle();
        $("#manager-help").toggle();
    }
    $scope.goApplicant = function() {
        // re-write the session cookie
        $cookies.putObject('session', {
            id: $cookies.getObject('session').id,
            type: 3,
            name: $cookies.getObject('session').name,
            lastName: $cookies.getObject('session').lastName
        }, {
            expires : timeToExpire
        });
        $state.go("userHome");
    };

    $scope.goAgent = function() {
        // re-write the session cookie
        $cookies.putObject('session', {
            id: $cookies.getObject('session').id,
            type: 1,
            name: $cookies.getObject('session').name,
            lastName: $cookies.getObject('session').lastName
        }, {
            expires : timeToExpire
        });
        $state.go("agentHome");
    };

    $scope.goManager = function() {
        // re-write the session cookie
        $cookies.putObject('session', {
            id: $cookies.getObject('session').id,
            type: 2,
            name: $cookies.getObject('session').name,
            lastName: $cookies.getObject('session').lastName
        }, {
            expires : timeToExpire
        });
        $state.go("managerHome");
    };

    function userType(type) {
        return type == $cookies.getObject('session').type;
    }
}

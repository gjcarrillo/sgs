var app = angular.module('sgdp.directive-perspective', []);

app.directive('perspective', function(Auth, Constants) {
    return {
        restrict: 'A',
        scope: {
            agentBtn: '@',
            managerBtn: '@',
            agentHelp: '@',
            managerHelp: '@'
        },
        link: function (scope, elem) {
            if (Auth.userType(Constants.Users.MANAGER)) {
                // User is manager, disable agent options
                $('#' + scope.agentBtn).toggle();
                $('#' + scope.agentHelp).toggle();
            } else if (Auth.userType(Constants.Users.AGENT)) {
                // If user is agent, disable manager options
                $('#' + scope.managerBtn).toggle();
                $('#' + scope.managerHelp).toggle();
            }
        }
    };
});
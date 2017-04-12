angular
    .module('sgdp')
    .controller('SessionExpiredController', sessionExpired);

sessionExpired.$inject = ['$scope', 'Auth'];

function sessionExpired($scope, Auth) {
    // Get user type before deleting session.
    var type = Auth.permission();
    // If we are here it means back-end session expired, so remove browser client session.
    if (type) {
        Auth.removeSession();
    }

    $scope.goToLogin = function () {
        Auth.logout(type);
    };
}
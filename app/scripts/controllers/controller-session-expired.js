angular
    .module('sgdp')
    .controller('SessionExpiredController', function ($scope, Auth) {
    // Get user type before deleting session.
    var type = Auth.permission();
    // If we are here it means back-end session expired, so remove browser client session.
    if (type) {
        Auth.removeSession();
    }

    $scope.goToLogin = function () {
        Auth.logout(type);
    };
});
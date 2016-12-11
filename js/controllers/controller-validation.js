/**
 * Created by Kristopher on 12/4/2016.
 */
angular
    .module('sgdp')
    .controller('ValidationController', validation);

validation.$inject = ['$scope', 'Validation', '$stateParams', 'Auth'];

function validation($scope, Validation, $stateParams, Auth) {

    $scope.loading = true;

    Validation.validate($stateParams.token)
        .then(
        function () {
            $scope.loading = false;
        },
        function (errorMsg) {
            $scope.errorMsg = errorMsg;
            $scope.loading = false;
        }

    );

    $scope.go = function () {
        Auth.sendHome();
    };
}
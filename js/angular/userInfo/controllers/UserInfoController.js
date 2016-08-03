angular
    .module('sgdp')
    .controller('UserInfoController', info);

info.$inject = ['$scope', '$rootScope', '$http'];

function info($scope, $rootScope, $http) {
    'use strict';

    // If no data has been sent, show nothing.
    if (sessionStorage.getItem("fetchId") === null) { return; }

    $scope.loading = true;
    // Take the stored data of interest
    var fetchId = sessionStorage.getItem("fetchId");
    $http.get('index.php/userInfo/UserInfoController/getUserInfo', {params:{userId:fetchId}})
        .then(function(response) {
            console.log(response);
            if (response.data.message = "success") {
                $scope.userData = response.data.data;
                $scope.userName = response.data.userName;
            }
            $scope.loading = false;
        });

    $scope.getConcurranceWarn = function() {
        if ($scope.userData.concurrencia < 15) {
            return {'color':'green'};
        } else if ($scope.userData.concurrencia >= 15 && $scope.userData.concurrencia < 35) {
            return {'color':'orange'};
        } else {
            return {'color':'red'};
        }
    }
}

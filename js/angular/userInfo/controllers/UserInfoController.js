angular
    .module('sgdp')
    .controller('UserInfoController', info);

info.$inject = ['$scope', '$rootScope', '$http', '$filter'];

function info($scope, $rootScope, $http, $filter) {
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
                // Format the result
                $scope.userData = response.data.data;
                $scope.userData.pcj_aporte += '%';
                $scope.userData.fianzas += '%';
                $scope.userData.concurrencia += '%';
                $scope.userData.carga_egs += '%';
                $scope.userData.carga_emi += '%';
                $scope.userData.carga_gmm += '%';
                $scope.userData.carga_gms += '%';
                $scope.userData.carga_hcm += '%';
                $scope.userData.carga_sem += '%';
                $scope.userData.carga_sf += '%';

                $scope.userData.dependencia = 'Bs ' + $filter('number')($scope.userData.dependencia);
                $scope.userData.sueldo = 'Bs ' + $filter('number')($scope.userData.sueldo);

                $scope.userName = response.data.userName;
            }
            $scope.loading = false;
        });
}

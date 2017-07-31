angular
    .module('sgdp')
    .controller('UserInfoController', function ($scope, $http, Utils, Constants, $window) {
    'use strict';

    // If no data has been sent, show nothing.
    if (sessionStorage.getItem("fetchId") === null) { return; }

    $scope.loading = true;
    // Take the stored data of interest
    var fetchId = sessionStorage.getItem("fetchId");
    $http.get(Constants.SERVER_URL + 'UserInfoController/getUserInfo', {params:{userId:fetchId}})
        .then(function(response) {
            console.log(response);
            if (response.data.message == "success") {
                $scope.userData = response.data.personal.data;
                $scope.userContribution = response.data.contribution;
                $scope.userName = response.data.personal.userName;
                $scope.picture = response.data.personal.picture ? Constants.IPAPEDI_URL + 'img/profiles_img/' +
                                                         response.data.personal.picture : 'images/avatar_circle.png';
            } else {
                Utils.handleError(response.data.message);
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
    };

    $scope.goBack = function () {
        $window.history.go(-1);
    };
});
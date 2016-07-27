angular
    .module('sgdp')
    .controller('UserHomeController', userHome);

userHome.$inject = ['$scope', '$rootScope', '$http', '$cookies'];

function userHome($scope, $rootScope, $http, $cookies) {
    'use strict';
    $scope.loading = true;
    $scope.selectedReq = -1;
    $scope.requests = [];
    $scope.docs = [];

    var fetchId = $cookies.getObject('session').id;
    $http.get('index.php/home/HomeController/getUserRequests', {params:{fetchId:fetchId}})
        .then(function (response) {
            if (response.data.message === "success") {
                $scope.requests = response.data.requests;
            }
            $scope.loading = false;
        });

    $scope.getSidenavHeight = function() {
        return {
            // 129 = header and footer height, approx
            'height':($(window).height() - 129)
        };
    }

    $scope.getDocumentContainerStyle = function() {
        return {
            'background-color': '#F5F5F5',
            'max-height':($(window).height() - 129)
        };
    }


    $scope.selectRequest = function(req) {
        $scope.selectedReq = req;
        if (req != -1) {
            $scope.docs = $scope.requests[req].docs;
        }
    };

}

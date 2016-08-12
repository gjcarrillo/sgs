angular
    .module('sgdp')
    .controller('UserHomeController', userHome);

userHome.$inject = ['$scope', '$rootScope', '$http', '$cookies', '$timeout', '$mdSidenav'];

function userHome($scope, $rootScope, $http, $cookies, $timeout, $mdSidenav) {
    'use strict';
    $scope.loading = true;
    $scope.selectedReq = -1;
    $scope.requests = [];
    $scope.docs = [];
    $scope.showList = false;
    $scope.fetchError = '';
    // contentAvailable will indicate whether sidenav can be visible
    $scope.contentAvailable = false;
    // contentLoaded will indicate whether sidenav can be locked open
    $scope.contentLoaded = false;

    var fetchId = $cookies.getObject('session').id;
    $http.get('index.php/home/UserHomeController/getUserRequests', {params:{fetchId:fetchId}})
        .then(function (response) {
            if (response.data.message === "success") {
                $scope.requests = response.data.requests;
                $scope.contentAvailable = true;
                $timeout(function() {
                    $scope.contentLoaded = true;
                    $mdSidenav('left').open();
                    $timeout(function() {
                        if ($scope.requests.length > 0) {
                            $scope.showList = true;
                            // $scope.selectRequest(0);
                        }
                    }, 600);
                }, 600);
            } else {
                $scope.fetchError = response.data.message;
            }
            $scope.loading = false;
        });

    $scope.toggleList = function() {
        $scope.showList = !$scope.showList;
    };

    $scope.getSidenavHeight = function() {
        return {
            // 129 = header and footer height, approx
            'height':($(window).height() - 129)
        };
    };

    $scope.getDocumentContainerStyle = function() {
        return {
            'background-color': '#F5F5F5',
            'max-height':($(window).height() - 129)
        };
    };

    $scope.selectRequest = function(req) {
        $scope.selectedReq = req;
        if (req != -1) {
            $scope.docs = $scope.requests[req].docs;
        }
        $mdSidenav('left').toggle();
    };

    // Helper function for formatting numbers with leading zeros
    $scope.pad = function(n, width, z) {
        z = z || '0';
        n = n + '';
        return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
    };

    $scope.downloadDoc = function(doc) {
        window.open('index.php/home/UserHomeController/download?lpath=' + doc.lpath, '_blank');
    };

    $scope.downloadAll = function() {
        // Bits of pre-processing before passing objects to URL
        var paths = new Array();
        angular.forEach($scope.docs, function(doc) {
            paths.push(doc.lpath);
        });
        location.href = 'index.php/home/UserHomeController/downloadAll?docs=' + JSON.stringify(paths);
    };

    $scope.openMenu = function() {
       $mdSidenav('left').toggle();
    };

    $scope.showHelp = function() {
        
    };
}

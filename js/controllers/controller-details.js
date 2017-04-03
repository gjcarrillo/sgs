angular
    .module('sgdp')
    .controller('DetailsController', details);

details.$inject = ['$scope', 'Utils', 'Requests', 'Auth', 'Config'];

function details($scope, Utils, Requests, Auth, Config) {
    'use strict';

    // If no data has been sent, show nothing.
    if (sessionStorage.getItem("req") === null) { return; }
    $scope.req = JSON.parse(sessionStorage.getItem("req"));

    if (!Config.loanConcepts) {
        Config.loanConcepts = JSON.parse(sessionStorage.getItem("loanConcepts"));
    }


    $scope.pad = function (n, width, z) {
        return Utils.pad(n, width, z);
    };

    // Calculates the request's payment fee.
    $scope.calculatePaymentFee = function() {
        return $scope.req ? Requests.calculatePaymentFee($scope.req.reqAmount,
                                                         $scope.req.due,
                                                         Requests.getInterestRate($scope.req.type)) : 0;
    };

    $scope.downloadDoc = function (doc) {
        window.open(Requests.getDocDownloadUrl(doc.id));
    };

    $scope.downloadAll = function () {
        location.href = Requests.getAllDocsDownloadUrl($scope.req.docs);
    };

    $scope.goHome = function () {
        Auth.sendHome();
    };
}

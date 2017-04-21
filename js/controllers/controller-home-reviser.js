angular
    .module('sgdp')
    .controller('ReviserHomeController', reviserHome);

reviserHome.$inject = ['$scope', 'Reviser', 'Utils', 'Requests', 'Constants', '$state', 'Config'];

function reviserHome($scope, Reviser, Utils, Requests, Constants, $state, Config) {
    'use strict';

    $scope.loanTypes = Reviser.data.loanTypes;
    $scope.requests = Reviser.data.requests;

    $scope.selected = [];

    $scope.query = {
        order: 'name',
        limit: 5,
        page: 1
    };

    function getPreApprovedRequests() {
        $scope.loading = true;
        Reviser.getPreApprovedRequests().then(
            function (requests) {
                $scope.requests = requests;
                $scope.loading = false;
            },
            function (error) {
                $scope.loading = false;
                $scope.requests = Requests.filterRequests([]);
                Utils.handleError(error);
            }
        );
    }

    $scope.goToDetails = function (req) {
        goToDetails(req);
    };

    /**
     * Saves the necessary information and goes to request details view.
     * (Made as a simple function so that isolated controllers can have access)
     *
     * @param req - request object.
     */
    function goToDetails(req) {
        // Save controller state before navigating away.
        preserveState();
        sessionStorage.setItem("uid", req.userOwner);
        sessionStorage.setItem("req", JSON.stringify(req));
        sessionStorage.setItem("loanConcepts", JSON.stringify(Config.loanConcepts));
        $state.go('details');
    }

    /**
     * Determines whether the specified object is empty (i.e. has no attributes).
     *
     * @param obj - object to test.
     * @returns {boolean}
     */
    $scope.isObjEmpty = function(obj) {
        return Utils.isObjEmpty(obj);
    };

    // Helper function for formatting numbers with leading zeros
    $scope.pad = function (n, width, z) {
        return Utils.pad(n, width, z);
    };

    $scope.downloadManual = function () {
        window.open(Constants.BASEURL + 'public/manualUsuario.pdf');
    };

    function preserveState() {
        var data = {};
        data.requests = $scope.requests;
        data.loanTypes = $scope.loanTypes;

        Reviser.updateData(data);
    }

    $scope.loadUserData = function(user) {
        sessionStorage.setItem("fetchId", user);
        window.open(Utils.getUserDataUrl(), '_blank');
    };

    if (!$scope.loanTypes) {
        $scope.loading = true;
        Requests.initializeListType().then(
            function (list) {
                $scope.loanTypes = list;
                getPreApprovedRequests();
            },
            function (error) {
                $scope.loading = false;
                Utils.handleError(error);
            }
        );
    } else {
        getPreApprovedRequests();
    }
}

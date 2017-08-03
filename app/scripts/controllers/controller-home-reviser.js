angular.module('sgdp').controller('ReviserHomeController', function ($scope, Reviser, Utils, Requests, Constants, $state, Config, $window, $mdSidenav) {
    'use strict';

    $scope.loanTypes = Reviser.data.loanTypes;
    $scope.requests = Reviser.data.requests;
    $scope.selectedAction = Reviser.data.selectedAction;

    $scope.selected = [];

    $scope.query = {
        order: 'name',
        limit: 5,
        page: 1
    };

    $scope.goBack = function () {
        $window.history.go(-1);
    };

    function getPreApprovedRequests() {
        $scope.fetching = true;
        Reviser.getPreApprovedRequests().then(
            function (requests) {
                $scope.requests = requests;
                $scope.fetching = false;
            },
            function (error) {
                console.log(error);
                $scope.fetching = false;
                $scope.requests = Requests.filterRequests([]);
                Utils.handleError(error);
            }
        );
    }

    function getWaitingForRegistrationRequests() {
        $scope.fetching = true;
        Reviser.getWaitingForRegistrationRequests().then(
            function (requests) {
                $scope.requests = requests;
                $scope.fetching = false;
            },
            function (error) {
                console.log(error);
                $scope.fetching = false;
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
        data.selectedAction = $scope.selectedAction;

        Reviser.updateData(data);
    }

    $scope.loadUserData = function(user) {
        sessionStorage.setItem("fetchId", user);
        window.open(Utils.getUserDataUrl(), '_blank');
    };

    $scope.showWatermark = function () {
        return !$scope.loading && !$scope.fetching &&
               $scope.selectedAction != 1 && $scope.selectedAction != 2;
    };

    $scope.selectAction = function (id) {
        $mdSidenav('left').close();
        $scope.requests = {};
        $scope.selectedAction = id;
        performAction(id);
    };

    function performAction (action) {
        switch (action) {
            case 1:
                // Waiting-for-registration-requests
                getWaitingForRegistrationRequests();
                break;
            case 2:
                // Pre-Approved requests
                getPreApprovedRequests();
                break;
        }
    }

    if (!$scope.loanTypes) {
        $scope.loading = true;
        Requests.initializeListType().then(
            function (list) {
                $scope.loading = false;
                $scope.contentLoaded = true;
                $scope.loanTypes = list;
            },
            function (error) {
                $scope.loading = false;
                Utils.handleError(error);
            }
        );
    } else if ($scope.selectedAction) {
        $scope.contentLoaded = true;
        $scope.selectAction($scope.selectedAction);
    }
});

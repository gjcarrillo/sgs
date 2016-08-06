angular
    .module('sgdp')
    .controller('ManagerHomeController', managerHome);

managerHome.$inject = ['$scope', '$rootScope', '$mdDialog', '$cookies', '$http', '$state'];

function managerHome($scope, $rootScope, $mdDialog, $cookies, $http, $state) {
    'use strict';
    $scope.model = {};
    $scope.model.query = -1;
    $scope.selectedQuery = -1;
    $scope.idPrefix = "V";
    $scope.loading = false;
    $scope.showApprovedAmount = false;
    $scope.loadingContent = false;
    $scope.showOptions = true;
    $scope.statuses = ["Recibida", "Aprobada", "Rechazada"];
    $scope.queries = [
        { category: 'req', name: 'Por cédula', id: 0},
        { category: 'req', name: 'Por estatus', id: 1},
        { category: 'date', name: 'Intervalo de fecha', id: 2},
        { category: 'date', name: 'Fecha exacta', id: 3},
        { category: 'money', name: 'Intervalo de fecha', id: 4},
        { category: 'money', name: 'Por cédula', id: 5}
    ];
    $scope.model.perform = new Array($scope.queries.length);
    // initialize all ng-model variables.
    for(var i=0; i<$scope.queries.length; i++) {
        $scope.model.perform[i] = {};
    }
    $scope.selectedReq = -1;
    $scope.requests = [];
    $scope.docs = [];
    $scope.fetchError = "";
    $scope.showList = false;

    $scope.test = true;
    $scope.testMe = function() {
        $scope.test = !$scope.test;
    };

    // Check if there is stored data before we went to History
    var requests = JSON.parse(sessionStorage.getItem("requests"));
    if (requests != null) {
        $scope.requests = requests;
        $scope.fetchId = sessionStorage.getItem("fetchId");
        // fetchId is used for several database queries.
        // that is why we don't use model's value, which is bind to search input.
        $scope.model.perform[0].id = $scope.fetchId.replace('V', '');
        $scope.model.perform[0].showResult = true;
        $scope.showOptions = false;
        $scope.selectedReq = parseInt(sessionStorage.getItem("selectedReq"));
        $scope.docs = $scope.requests[$scope.selectedReq].docs;
        $scope.showList = parseInt(sessionStorage.getItem("showList")) ? true : false;
        // Got back what we wanted -- erase them from storage
        sessionStorage.removeItem("requests");
        sessionStorage.removeItem("fetchId");
        sessionStorage.removeItem("selectedReq");
        sessionStorage.removeItem("showList");
    }

    $scope.fetchUserRequests = function(index) {
        $scope.fetchId = $scope.idPrefix + $scope.model.perform[index].id;
        $scope.requests = [];
        $scope.selectedReq = -1;
        $scope.loading = true;
        $scope.docs = [];
        $scope.showList = false;
        $scope.fetchError = "";
        $http.get('index.php/home/HomeController/getUserRequests', {params:{fetchId:$scope.fetchId}})
            .then(function (response) {
                console.log(response);
                if (response.data.message === "success") {
                    $scope.requests = response.data.requests;
                    $scope.showOptions = false;
                    $scope.model.perform[index].showResult = true;
                } else {
                    $scope.fetchError = response.data.error;
                }
                $scope.loading = false;

            });
    };

    $scope.fetchRequestsByStatus = function(status, index) {
        $scope.requests = [];
        $scope.selectedReq = -1;
        $scope.loading = true;
        $scope.docs = [];
        $scope.showList = false;
        $scope.fetchError = "";
        $http.get('index.php/documents/ManageRequestController/fetchRequestsByStatus', {params:{status:status}})
            .then(function (response) {
                console.log(response);
                if (response.data.message === "success") {
                    $scope.requests = response.data.requests;
                    $scope.showOptions = false;
                    $scope.model.perform[index].showResult = true;
                } else {
                    $scope.fetchError = response.data.error;
                }
                $scope.loading = false;

            });
    };

    $scope.fetchRequestsByDateInterval = function(from, to, index) {
        $scope.requests = [];
        $scope.selectedReq = -1;
        $scope.loading = true;
        $scope.docs = [];
        $scope.showList = false;
        $scope.fetchError = "";
        $http.get('index.php/documents/ManageRequestController/fetchRequestsByDateInterval',
            {params:{from:moment(from).format('DD/MM/YYYY'), to:moment(to).format('DD/MM/YYYY')}})
            .then(function (response) {
                console.log(response);
                if (response.data.message === "success") {
                    $scope.requests = response.data.requests;
                    $scope.showOptions = false;
                    $scope.model.perform[index].showResult = true;
                } else {
                    $scope.fetchError = response.data.error;
                }
                $scope.loading = false;

            });
    };

    $scope.fetchRequestsByExactDate = function(date, index) {
        $scope.requests = [];
        $scope.selectedReq = -1;
        $scope.loading = true;
        $scope.docs = [];
        $scope.showList = false;
        $scope.fetchError = "";
        $http.get('index.php/documents/ManageRequestController/fetchRequestsByDateInterval',
            {params:{from:moment(date).format('DD/MM/YYYY'), to:moment(date).format('DD/MM/YYYY')}})
            .then(function (response) {
                console.log(response);
                if (response.data.message === "success") {
                    $scope.requests = response.data.requests;
                    $scope.showOptions = false;
                    $scope.model.perform[index].showResult = true;
                } else {
                    $scope.fetchError = response.data.error;
                }
                $scope.loading = false;

            });
    };

    $scope.getApprovedAmountByDateInterval = function(from, to) {
        $scope.showApprovedAmount = false;
        $scope.fetchError = "";
        $scope.loadingContent = true;
        $http.get('index.php/documents/ManageRequestController/getApprovedAmountByDateInterval',
            {params:{from:moment(from).format('DD/MM/YYYY'), to:moment(to).format('DD/MM/YYYY')}})
            .then(function (response) {
                console.log(response);
                if (response.data.message === "success") {
                    $scope.approvedAmount = response.data.approvedAmount;
                    $scope.approvedAmountTitle = "Monto aprobado total entre la fecha especificada:";
                    $scope.showApprovedAmount = true;
                } else {
                    $scope.fetchError = response.data.error;
                }
                $scope.loadingContent = false;

            });
    };

    $scope.getApprovedAmountById = function(index) {
        var userId = $scope.idPrefix + $scope.model.perform[index].id;
        $scope.showApprovedAmount = false;
        $scope.fetchError = "";
        $scope.loadingContent = true;
        $http.get('index.php/documents/ManageRequestController/getApprovedAmountById',
            {params:{userId:userId}})
            .then(function (response) {
                console.log(response);
                if (response.data.message === "success") {
                    $scope.approvedAmount = response.data.approvedAmount;
                    $scope.approvedAmountTitle = "Monto aprobado total para " + response.data.username + ":";
                    $scope.showApprovedAmount = true;
                } else {
                    $scope.fetchError = response.data.error;
                }
                $scope.loadingContent = false;
            });
    };

    $scope.getDocumentContainerStyle = function() {
        return {
            'background-color': '#F5F5F5',
            'max-height':($(window).height() - 129),
        };
    };

    $scope.getSidenavHeight = function() {
        return {
            // 129 = header and footer height, approx
            'height':($(window).height() - 129)
        };
    };

    $scope.toggleList = function() {
        $scope.showList = !$scope.showList;
    };

    $scope.toggleReqList = function(req) {
        req.showList = !req.showList;
    }

    $scope.loadUserData = function(userId) {
        sessionStorage.setItem("fetchId", userId);
        window.open('http://localhost:8080/sgdp/#/userInfo', '_blank');
    };

    $scope.selectRequest = function(req) {
        $scope.selectedReq = req;
        if (req != -1) {
            $scope.docs = $scope.requests[req].docs;
        }
    };

    // Helper function for formatting numbers with leading zeros
    $scope.pad = function(n, width, z) {
        z = z || '0';
        n = n + '';
        return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
    };

    $scope.loadHistory = function() {
        // Save data before going to history page
        sessionStorage.setItem("requests", JSON.stringify($scope.requests));
        sessionStorage.setItem("fetchId", $scope.fetchId);
        sessionStorage.setItem("selectedReq", $scope.selectedReq);
        sessionStorage.setItem("showList", $scope.showList ? 1 : 0);

        $state.go('history');

    };

    $scope.downloadDoc = function(doc) {
        window.open('index.php/home/HomeController/download?lpath=' + doc.lpath, '_blank');
    };

    $scope.downloadAll = function() {
        // Bits of parsing before passing objects to URL
        var paths = new Array();
        angular.forEach($scope.docs, function(doc) {
            paths.push(doc.lpath);
        });
        location.href = 'index.php/home/HomeController/downloadAll?docs=' + JSON.stringify(paths);
    };

    /**
    * Custom dialog for updating an existing request
    */
    $scope.openEditRequestDialog = function($event) {
        var parentEl = angular.element(document.body);
        $mdDialog.show({
            parent: parentEl,
            targetEvent: $event,
            templateUrl: 'index.php/documents/ManageRequestController',
            clickOutsideToClose: false,
            escapeToClose: false,
            locals: {
                fetchId: $scope.fetchId,
                request: $scope.requests[$scope.selectedReq],
            },
            controller: DialogController
        });
        // Isolated dialog controller
        function DialogController($scope, $mdDialog, fetchId, request) {
            $scope.files = [];
            $scope.fetchId = fetchId;
            $scope.uploading = false;
            $scope.request = request;
            $scope.statuses = ["Recibida", "Aprobada", "Rechazada"];
            $scope.model = {};
            $scope.model.status = $scope.statuses[0];
            $scope.model.comment = $scope.request.comment;

            $scope.closeDialog = function() {
                $mdDialog.hide();
            };

            $scope.missingField = function() {
                if ($scope.model.status == "Aprobada") {
                    return typeof $scope.model.approvedAmount === "undefined";
                } else {
                    return ($scope.model.status != "Rechazada" && (typeof $scope.model.comment === "undefined"
                        || $scope.model.comment == ""
                        || $scope.model.comment == $scope.request.comment));
                }
            };

            // Creates new request in database and uploads documents
            $scope.updateRequest = function() {
                $scope.uploading = true;
                $scope.request.status = $scope.model.status;
                $scope.request.comment = $scope.model.comment;
                $scope.request.reunion = $scope.model.reunion;
                $scope.request.approvedAmount = $scope.model.approvedAmount;
                $http.get('index.php/documents/ManageRequestController/updateRequest', {params:$scope.request})
                    .then(function (response) {
                        $scope.uploading = false;
                        console.log(response.data);
                        if (response.data.message === "success") {
                            console.log("Request update succeded...");
                            // Close dialog and alert user that operation was successful
                            $mdDialog.hide();
                            $mdDialog.show(
                                $mdDialog.alert()
                                    .parent(angular.element(document.body))
                                    .clickOutsideToClose(true)
                                    .title('Solicitud actualizada')
                                    .textContent('La solicitud fue actualizada exitosamente.')
                                    .ariaLabel('Successful request update dialog')
                                    .ok('Ok'));
                        } else {
                            console.log("FAILED!");
                        }
                    });
            };
        }
    };

    $scope.fetchedRequests = function() {
        return $scope.model.perform[1].showResult ||
            $scope.model.perform[2].showResult ||
            $scope.model.perform[3].showResult;
    };

    /**
     * onOpen & onClose select helpers which
     * 'fixes' selection usability problem (selection menu translation)
     */
    $scope.onQueryOpen = function() {
        $scope.backup = $scope.selectedQuery;
        $scope.selectedQuery = -1;
    };
    $scope.onQueryClose = function() {
        if ($scope.selectedQuery === -1) {
            $scope.selectedQuery = $scope.backup;
        }
        $scope.model.query = $scope.selectedQuery;
        // re-initialize any error message
        $scope.fetchError = '';
    };

    $scope.onStatusOpen = function() {
        $scope.backup = $scope.model.perform[1].status;
        $scope.model.perform[1].status = null;
    };

    $scope.onStatusClose = function() {
        if ($scope.model.perform[1].status === null) {
            $scope.model.perform[1].status = $scope.backup;
        }
    };

    $scope.onIdOpen = function() {
        $scope.backup = $scope.idPrefix;
        $scope.idPrefix = null;
    };

    $scope.onIdClose = function() {
        if ($scope.idPrefix === null) {
            $scope.idPrefix = $scope.backup;
        }
    };

    /**
     * Goes back to query selection options
     */
    $scope.goBack = function() {
        // $scope.requests = [];
        // $scope.selectedReq = -1;
        // $scope.docs = [];
        for (var i=0; i<$scope.model.perform.length; i++) {
            $scope.model.perform[i].showResult = false;
        }
        $scope.showOptions = true;
    };

}

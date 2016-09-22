angular
    .module('sgdp')
    .controller('ManagerHomeController', managerHome);

managerHome.$inject = ['$scope', '$mdDialog', '$http',
    '$state', '$timeout', '$mdSidenav', '$mdMedia'];

function managerHome($scope, $mdDialog, $http, $state,
    $timeout, $mdSidenav, $mdMedia) {
    'use strict';
    $scope.model = {};
    $scope.model.query = -1;
    $scope.selectedQuery = -1;
    $scope.idPrefix = "V";
    $scope.loading = false;
    $scope.showApprovedAmount = false;
    $scope.loadingContent = false;
    $scope.showOptions = true;
    $scope.showResult = -1;
    $scope.chart = null;
    $scope.loadingReport = false;
    $scope.statuses = ["Recibida", "Aprobada", "Rechazada"];
    $scope.queries = [
        { category: 'req', name: 'Por cédula', id: 0},
        { category: 'req', name: 'Por estatus', id: 1},
        { category: 'date', name: 'Intervalo de fecha', id: 2},
        { category: 'date', name: 'Fecha exacta', id: 3},
        { category: 'money', name: 'Intervalo de fecha', id: 4},
        { category: 'money', name: 'Por cédula', id: 5},
        { category: 'report', name: 'Intervalo de fecha', id: 6},
        { category: 'report', name: 'Semana actual', id: 7}
    ];
    $scope.model.perform = new Array($scope.queries.length);
    // initialize all ng-model variables.
    for(var i=0; i<$scope.queries.length; i++) {
        $scope.model.perform[i] = {};
    }
    $scope.selectedReq = -1;
    $scope.selectedPendingReq = -1;
    $scope.requests = [];
    $scope.pendingRequests = [];
    $scope.docs = [];
    $scope.fetchError = '';
    $scope.approvalReportError = '';
    $scope.pie = null;
    $scope.pieError = '';
    $scope.showList = false;
    $scope.showPendingReq = false;
    $scope.showAdvSearch = false;
    // dataChanged notifies whether data changed through user interaction,
    // thus needing to update pie and report data
    var dataChanged = false;

    // Check if there is stored data for requests before we went to History
    var requests = JSON.parse(sessionStorage.getItem("requests"));
    if (requests != null) {
        $scope.requests = requests;
        recoverResult(parseInt(sessionStorage.getItem("showResult")));
        $scope.selectedReq = parseInt(sessionStorage.getItem("selectedReq"));
        $scope.docs = $scope.requests[$scope.selectedReq].docs;
        $scope.showList =
            parseInt(sessionStorage.getItem("showList")) ? true : false;
        $scope.showAdvSearch = true;
        $scope.selectedQuery =
            parseInt(sessionStorage.getItem("selectedQuery"));
        $scope.model.query = parseInt(sessionStorage.getItem("model.query"));
        $scope.showOptions = $scope.showResult == -1 ? true : false;
        // Got back what we wanted -- erase them from storage
        sessionStorage.removeItem("requests");
        sessionStorage.removeItem("fetchId");
        sessionStorage.removeItem("selectedReq");
        sessionStorage.removeItem("showList");
    }
    // Check if there is stored data for pendingRequests before we
    // went to History
    var pendingRequests =
        JSON.parse(sessionStorage.getItem("pendingRequests"));
    if (pendingRequests != null) {
        $scope.pendingRequests = pendingRequests;
        $scope.selectedPendingReq =
            parseInt(sessionStorage.getItem("selectedPendingReq"));
        $scope.showPendingReq =
            parseInt(sessionStorage.getItem("showPendingReq")) ? true : false;
        if ($scope.selectedPendingReq != -1) {
            $scope.docs =
                $scope.pendingRequests[$scope.selectedPendingReq].docs;
            $scope.pendingRequests[$scope.selectedPendingReq].showList = (
                parseInt(sessionStorage.getItem("showReq")) ? true : false
            );
        }
        // Got back what we wanted -- erase them from storage
        sessionStorage.removeItem("pendingRequests");
        sessionStorage.removeItem("selectedPendingReq");
        sessionStorage.removeItem("showReq");
    }

    // Fetch pending requests and automatically show first one to user (if any)
    if ($scope.selectedReq == -1 && $scope.selectedPendingReq == -1) {
        $scope.loadingContent = true;
        $http.get('index.php/home/ManagerHomeController/fetchRequestsByStatus',
            {params:{status:"Recibida"}})
            .then(function (response) {
                console.log(response);
                if (response.data.message === "success") {
                    $scope.pendingRequests = response.data.requests;
                    if ($scope.pendingRequests.length > 0) {
                        $scope.showPendingReq = true;
                        $mdSidenav('left').open();
                    }
                }
                $scope.loadingContent = false;

            });
    }

    // Retrieval and clean up of stored values
    function recoverResult(index) {
        if (sessionStorage.getItem("fetchId")) {
            $scope.fetchId = sessionStorage.getItem("fetchId");
            $scope.model.perform[0].id =
                parseInt($scope.fetchId.replace('V', ''));
            sessionStorage.removeItem("fetchId");
        }
        if (sessionStorage.getItem("status")) {
            $scope.model.perform[1].status = sessionStorage.getItem("status");
            sessionStorage.removeItem("status");
        }
        if (sessionStorage.getItem("from")) {
            $scope.model.perform[2].from =
                moment(sessionStorage.getItem("from"),
                'DD/MM/YYYY', true).toDate();
            $scope.model.perform[2].to = moment(sessionStorage.getItem("to"),
                'DD/MM/YYYY', true).toDate();
            sessionStorage.removeItem("from");
            sessionStorage.removeItem("to");
        }
        if (sessionStorage.getItem("date")) {
            $scope.model.perform[3].date =
                moment(sessionStorage.getItem("date"),
                'DD/MM/YYYY', true).toDate();
            sessionStorage.removeItem("date");
        }
        if (sessionStorage.getItem("pie")) {
            // Retrieve report data
            $scope.pie = JSON.parse(sessionStorage.getItem("pie"));
            $scope.report = JSON.parse(sessionStorage.getItem("reportData"));
            // Re-draw the pie.
            drawPie($scope.pie);
        }
        $scope.showResult = index;
    }

    $scope.fetchUserRequests = function(index) {
        resetContent();
        $scope.loading = true;
        $scope.fetchId = $scope.idPrefix + $scope.model.perform[index].id;
        $http.get('index.php/home/ManagerHomeController/getUserRequests',
            {params:{fetchId:$scope.fetchId}})
            .then(function (response) {
                console.log(response);
                if (response.data.message === "success") {
                    $scope.requests = response.data.requests;
                    $scope.showOptions = false;
                    $scope.showResult = index;
                    $scope.pieloaded = true;
                    $scope.report = response.data.report;
                    $scope.pie = response.data.pie;
                    drawPie(response.data.pie);
                } else {
                    $scope.fetchError = response.data.error;
                }
                $scope.loading = false;

            });
    };

    $scope.fetchRequestsByStatus = function(status, index) {
        resetContent();
        $scope.loading = true;
        $http.get('index.php/home/ManagerHomeController/' +
            'fetchRequestsByStatus',
            {params:{status:status}})
            .then(function (response) {
                console.log(response);
                if (response.data.message === "success") {
                    $scope.requests = response.data.requests;
                    $scope.showOptions = false;
                    $scope.showResult = index;
                    $scope.pieloaded = true;
                    $scope.pie = response.data.pie;
                    drawPie(response.data.pie);
                    $scope.report = response.data.report;
                    $scope.report.status = status;
                } else {
                    $scope.fetchError = response.data.error;
                }
                $scope.loading = false;

            });
    };

    $scope.fetchRequestsByDateInterval = function(from, to, index) {
        resetContent();
        $scope.loading = true;
        $http.get('index.php/home/ManagerHomeController/' +
            'fetchRequestsByDateInterval',
            {params:{from:moment(from).format('DD/MM/YYYY'),
                to:moment(to).format('DD/MM/YYYY')}})
            .then(function (response) {
                console.log(response);
                if (response.data.message === "success") {
                    $scope.requests = response.data.requests;
                    $scope.showOptions = false;
                    $scope.showResult = index;
                    $scope.pieloaded = true;
                    $scope.pie = response.data.pie;
                    drawPie(response.data.pie);
                    $scope.report = response.data.report;
                } else {
                    $scope.fetchError = response.data.error;
                }
                $scope.loading = false;

            });
    };

    $scope.fetchRequestsByExactDate = function(date, index) {
        resetContent();
        $scope.loading = true;
        $http.get('index.php/home/ManagerHomeController/' +
            'fetchRequestsByDateInterval',
            {params:{from:moment(date).format('DD/MM/YYYY'),
                to:moment(date).format('DD/MM/YYYY')}})
            .then(function (response) {
                console.log(response);
                if (response.data.message === "success") {
                    $scope.requests = response.data.requests;
                    $scope.showOptions = false;
                    $scope.showResult = index;
                    $scope.pieloaded = true;
                    $scope.pie = response.data.pie;
                    drawPie(response.data.pie);
                    $scope.report = response.data.report;
                } else {
                    $scope.fetchError = response.data.error;
                }
                $scope.loading = false;

            });
    };

    $scope.getApprovedAmountByDateInterval = function(from, to) {
        $mdSidenav('left').toggle();
        resetContent();
        $scope.loadingContent = true;
        $http.get('index.php/home/ManagerHomeController/' +
            'getApprovedAmountByDateInterval',
            {params:{from:moment(from).format('DD/MM/YYYY'),
                to:moment(to).format('DD/MM/YYYY')}})
            .then(function (response) {
                console.log(response);
                if (response.data.message === "success") {
                    $scope.approvedAmount = response.data.approvedAmount;
                    $scope.approvedAmountTitle = "Monto aprobado total " +
                        "para el intervalo de fecha especificado:";
                    $scope.showApprovedAmount = true;
                } else {
                    $scope.fetchError = response.data.error;
                }
                $scope.loadingContent = false;

            });
    };

    $scope.getApprovedAmountById = function(index) {
        $mdSidenav('left').toggle();
        resetContent();
        $scope.loadingContent = true;
        var userId = $scope.idPrefix + $scope.model.perform[index].id;
        $http.get('index.php/home/ManagerHomeController/getApprovedAmountById',
            {params:{userId:userId}})
            .then(function (response) {
                console.log(response);
                if (response.data.message === "success") {
                    $scope.approvedAmount = response.data.approvedAmount;
                    $scope.approvedAmountTitle = "Monto aprobado total para " +
                        response.data.username + ":";
                    $scope.showApprovedAmount = true;
                } else {
                    $scope.fetchError = response.data.error;
                }
                $scope.loadingContent = false;
            });
    };

    $scope.getApprovedReportByCurrentWeek = function() {
        $mdSidenav('left').toggle();
        resetContent();
        $scope.loadingReport = true;
        $http.get('index.php/home/ManagerHomeController/' +
            'getApprovedReportByCurrentWeek')
            .then(function (response) {
                console.log(response);
                if (response.data.message === "success") {
                    $scope.report = response.data.report;
                    $scope.generateExcelReport();
                } else {
                    $scope.approvalReportError = response.data.error;
                    $scope.loadingReport = false;
                }
            });
    };

    $scope.getApprovedReportByDateInterval = function(from, to) {
        $mdSidenav('left').toggle();
        resetContent();
        $scope.loadingReport = true;
        $http.get('index.php/home/ManagerHomeController/' +
            'getApprovedReportByDateInterval',
            {params:{from:moment(from).format('DD/MM/YYYY'),
                to:moment(to).format('DD/MM/YYYY')}})
            .then(function (response) {
                console.log(response);
                if (response.data.message === "success") {
                    $scope.report = response.data.report;
                    $scope.generateExcelReport();
                } else {
                    $scope.approvalReportError = response.data.error;
                    $scope.loadingReport = false;
                }
            });
    };
    $scope.toggleList = function() {
        $scope.showList = !$scope.showList;
    };

    $scope.toggleReqList = function(req) {
        req.showList = !req.showList;
    };

    $scope.loadUserData = function(userId) {
        sessionStorage.setItem("fetchId", userId);
        window.open('http://localhost:8080/sgdp/#/userInfo', '_blank');
        // Data not being changed so far - don't request data again if
        // user clicks on Stats
        setDataChanged(false);
    };

    $scope.selectRequest = function(req) {
        $scope.selectedPendingReq = -1;
        $scope.selectedReq = req;
        if (req != -1) {
            $scope.docs = $scope.requests[req].docs;
        }
        // Data not being changed so far - don't request data again if
        // user clicks on Stats
        setDataChanged(false);
        // Close sidenav
        $mdSidenav('left').toggle();
    };

    $scope.selectPendingReq = function(req) {
        $scope.selectedReq = -1;
        $scope.selectedPendingReq = req;
        if (req != -1) {
            $scope.docs = $scope.pendingRequests[req].docs;
        }
        // Data not being changed so far - don't request data again if
        // user clicks on Stats
        setDataChanged(false);
        // Close sidenav
        $mdSidenav('left').toggle();
    };

    // Helper function for formatting numbers with leading zeros
    $scope.pad = function(n, width, z) {
        z = z || '0';
        n = n + '';
        return n.length >= width ? n :
            new Array(width - n.length + 1).join(z) + n;
    };

    $scope.loadHistory = function() {
        // Save data before going to history page
        if ($scope.selectedReq != -1) {
            sessionStorage.setItem("requests",
                JSON.stringify($scope.requests));
            sessionStorage.setItem("selectedReq", $scope.selectedReq);
            sessionStorage.setItem("showResult", $scope.showResult);
            sessionStorage.setItem("selectedQuery", $scope.selectedQuery);
            sessionStorage.setItem("model.query", $scope.model.query);
            storeResult();
        }
        if ($scope.pie != null) {
            // Save the pie
            sessionStorage.setItem("pie", JSON.stringify($scope.pie));
            sessionStorage.setItem("reportData",
                JSON.stringify($scope.report));
        }
        sessionStorage.setItem("pendingRequests",
            JSON.stringify($scope.pendingRequests));
        sessionStorage.setItem("selectedPendingReq",
            $scope.selectedPendingReq);
        sessionStorage.setItem("showPendingReq", $scope.showPendingReq ?
            1 : 0);
        if ($scope.selectedPendingReq != -1) {
            sessionStorage.setItem("showReq",
                $scope.pendingRequests[$scope.selectedPendingReq].showList ?
                1 : 0);
        }

        $state.go('history');

    };

    function storeResult() {
        if ($scope.fetchId) {
            sessionStorage.setItem("fetchId", $scope.fetchId);
            sessionStorage.setItem("showList", $scope.showList ? 1 : 0);
        }

        if ($scope.model.perform[1].status) {
            sessionStorage.setItem("status", $scope.model.perform[1].status);
            sessionStorage.setItem("showList",
                $scope.requests[$scope.selectedReq].showList ? 1: 0);
        }

        if ($scope.model.perform[2].from) {
            sessionStorage.setItem("from",
                moment($scope.model.perform[2].from).format('DD/MM/YYYY'));
            sessionStorage.setItem("to",
                moment($scope.model.perform[2].to).format('DD/MM/YYYY'));
            sessionStorage.setItem("showList",
                $scope.requests[$scope.selectedReq].showList ? 1: 0);
        }

        if ($scope.model.perform[3].date) {
            sessionStorage.setItem("date",
                moment($scope.model.perform[3].date).format('DD/MM/YYYY'));
            sessionStorage.setItem("showList",
                $scope.requests[$scope.selectedReq].showList ? 1: 0);
        }
    }

    $scope.downloadDoc = function(doc) {
        window.open('index.php/home/UserHomeController/download?lpath=' +
            doc.lpath, '_blank');
    };

    $scope.downloadAll = function() {
        // Bits of pre-processing before passing objects to URL
        var paths = [];
        angular.forEach($scope.docs, function(doc) {
            paths.push(doc.lpath);
        });
        location.href = 'index.php/home/UserHomeController/downloadAll?docs=' +
            JSON.stringify(paths);
    };

    /**
    * Custom dialog for updating an existing request
    */
    $scope.openEditRequestDialog = function($event) {
        var parentEl = angular.element(document.body);
        var req = ($scope.selectedPendingReq == -1 ?
            $scope.requests[$scope.selectedReq] :
            $scope.pendingRequests[$scope.selectedPendingReq]);
        $mdDialog.show({
            parent: parentEl,
            targetEvent: $event,
            templateUrl: 'index.php/documents/ManageRequestController',
            clickOutsideToClose: false,
            escapeToClose: false,
            locals: {
                fetchId: $scope.fetchId,
                request: req
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
            $scope.model.approvedAmount = $scope.request.reqAmount;

            $scope.closeDialog = function() {
                $mdDialog.hide();
            };

            $scope.missingField = function() {
                if ($scope.model.status == "Aprobada") {
                    return typeof $scope.model.approvedAmount === "undefined";
                } else {
                    return ($scope.model.status != "Rechazada" &&
                        (typeof $scope.model.comment === "undefined"
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
                $http.post('index.php/documents/ManageRequestController/' +
                    'updateRequest',
                    JSON.stringify($scope.request))
                    .then(function (response) {
                        $scope.uploading = false;
                        console.log(response.data);
                        if (response.status == 200) {
                            console.log("Request update succeded...");
                            // Update data on the pending request list so that
                            // changes would reflect even when updating from
                            // advanced query lists
                            updatePendingList(
                                $scope.request.id,
                                $scope.model.status,
                                $scope.model.comment,
                                $scope.model.reunion,
                                $scope.model.approvedAmount
                            );
                            // Notify that data has changed, thus updating
                            // stats when requested
                            setDataChanged(true);
                            // Close dialog and alert user that operation
                            // was successful
                            $mdDialog.hide();
                            $mdDialog.show(
                                $mdDialog.alert()
                                    .parent(angular.element(document.body))
                                    .clickOutsideToClose(true)
                                    .title('Solicitud actualizada')
                                    .textContent('La solicitud fue ' +
                                        'actualizada exitosamente.')
                                    .ariaLabel('Successful request update ' +
                                        'dialog')
                                    .ok('Ok'));
                        } else {
                            console.log("FAILED!");
                        }
                    });
            };

            $scope.showHelp = function() {
                var options = {
                    showNavigation : true,
                    showCloseBox : true,
                    delay : -1,
                    tripTheme: "dark",
                    prevLabel: "Anterior",
                    nextLabel: "Siguiente",
                    finishLabel: "Entendido"
                };
                showFormHelp(options);
            };

            /**
             * Shows tour-based help of all input fields.
             * @param options: Obj containing tour.js options
             */
            function showFormHelp(options) {
                var tripToShowNavigation = new Trip([], options);
                if (typeof $scope.model.comment === "undefined" ||
                    $scope.model.comment == "" ||
                    $scope.model.comment == $scope.request.comment) {
                    var content = "Agregue un comentario (opcional) " +
                    "hacia la solicitud.";
                    appendFieldHelp(tripToShowNavigation, "#comment", content, 's');
                }
                if ($scope.model.status == "Recibida") {
                    var content = "Seleccione el nuevo estatus de la " +
                        "solicitud.";
                    appendFieldHelp(tripToShowNavigation, "#status", content, 's');
                }
                if ($scope.model.status != "Recibida"
                    && typeof $scope.model.reunion === "undefined") {
                    var content = "Agrege el número de reunión (opcional).";
                    appendFieldHelp(tripToShowNavigation, "#reunion",
                        content, 'n');
                }
                if ($scope.model.status == "Aprobada"
                    && typeof $scope.model.approvedAmount === "undefined") {
                    var content = "Agrege el monto aprobado en Bs.";
                    appendFieldHelp(tripToShowNavigation, "#approved-amount",
                        content, 'n');
                }
                if (!$scope.missingField()) {
                    var content = "Haga click en ACTUALIZAR para guardar " +
                        "los cambios."
                    appendFieldHelp(tripToShowNavigation, "#edit-btn",
                        content, 'n');
                }
                tripToShowNavigation.start();
            }

            function appendFieldHelp(trip, id, content, pos) {
                trip.tripData.push(
                    {
                        sel : $(id), content: content, position: pos,
                        animation: 'fadeInUp'
                    }
                );
            }
        }
    };

    /**
     * Helper function that updates the pending requests list by deleting the
     * selected request from the list.
     * @param id - unique id of the request to update.
     * @param status - new status
     * @param comment - new comment
     * @param reunion - request closure's reunion
     * @param approvedAmount - request's approved amount
     */
    function updatePendingList(id, status, comment, reunion, approvedAmount) {
        var index = getPendingIndex(id);
        console.log(index);
        $scope.pendingRequests[index].status = status;
        $scope.pendingRequests[index].comment = comment;
        $scope.pendingRequests[index].reunion = reunion;
        $scope.pendingRequests[index].approvedAmount = approvedAmount;
    }

    /**
     * Helper function that returns the index of the specified pending request
     * @param id - unique id of the request
     * @return index of the specified request within pendingRequests array
     */
    function getPendingIndex(id) {
        var i = 0;
        while (i < $scope.pendingRequests.length &&
            id != $scope.pendingRequests[i].id) {
            i++;
        }
        // Updating request should always exist here, so no need to valdiate
        return i;
    }

    /**
     * Dialog that prompts for new agent user information
     */
    $scope.openManageUserAgentDialog = function($event) {
        var parentEl = angular.element(document.body);
        $mdDialog.show({
            parent: parentEl,
            targetEvent: $event,
            templateUrl: 'index.php/users/ManageAgentUsers',
            clickOutsideToClose: false,
            escapeToClose: false,
            controller: DialogController
        });

        function DialogController($mdDialog, $scope) {
            $scope.uploading = false;
            $scope.operationError = '';
            $scope.model = {};
            $scope.idPrefix = "V";
            $scope.selectedTab = 1;

            $scope.closeDialog = function() {
                $mdDialog.hide();
            };

            $scope.missingField = function() {
                return (
                    typeof $scope.userId === "undefined" ||
                    typeof $scope.model.psw === "undefined" ||
                    typeof $scope.model.name === "undefined" ||
                    typeof $scope.model.lastname === "undefined"
                );
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

            $scope.createNewAgent = function() {
                $scope.errorMsg = '';
                $scope.uploading = true;
                $scope.model.id = $scope.idPrefix + $scope.userId;
                $http.post('index.php/users/ManageAgentUsers/createNewAgent',
                    $scope.model)
                    .then(function(response) {
                        console.log(response);
                        if (response.data.message == "success") {
                            // Close dialog and alert user that operation
                            // was successful
                            $mdDialog.hide();
                            $mdDialog.show(
                                $mdDialog.alert()
                                    .parent(angular.element(document.body))
                                    .clickOutsideToClose(true)
                                    .title('Operación exitosa')
                                    .textContent('El nuevo usuario Gestor ' +
                                    'ha sido registrado exitosamente')
                                    .ariaLabel('Successful operation dialog')
                                    .ok('Ok'));
                        } else {
                            $scope.errorMsg = response.data.message;
                        }
                        $scope.uploading = false;
                    });
            };

            /**
             * Build `userAgents` list of key/value pairs
             */
            $scope.selectedUser = null;

            $http.get('index.php/users/ManageAgentUsers/fetchAllAgents')
                .then(function(response) {
                    var allAgents = response.data.agents;
                    $scope.userAgents = allAgents.map(function(agent) {
                        return {
                            value: agent.split('(')[0].trim(),
                            display: agent
                        };
                    });
                });

            $scope.onUsersOpen = function() {
                $scope.userBackup = $scope.selectedUser;
                $scope.selectedUser = null;
            };

            $scope.onUsersClose = function() {
                if ($scope.selectedUser === null) {
                    $scope.selectedUser = $scope.userBackup;
                }
            };

            $scope.deleteAgent = function() {
                $scope.errorMsg = '';
                $scope.uploading = true;
                $http.post('index.php/users/ManageAgentUsers/deleteAgentUser',
                    $scope.selectedUser.value)
                    .then(function(response) {
                        console.log(response);
                        if (response.status == 200) {
                            // Close dialog and alert user that operation
                            // was successful
                            $mdDialog.hide();
                            $mdDialog.show(
                                $mdDialog.alert()
                                    .parent(angular.element(document.body))
                                    .clickOutsideToClose(true)
                                    .title('Operación exitosa')
                                    .textContent('El usuario elegido ha ' +
                                        'sido eliminado del sistema ' +
                                        'exitosamente')
                                    .ariaLabel('Successful operation dialog')
                                    .ok('Ok'));
                        } else {
                            $scope.errorMsg = response.data.message;
                        }
                        $scope.uploading = false;
                    });
            };

            $scope.showHelp = function() {
                var options = {
                    showNavigation : true,
                    showCloseBox : true,
                    delay : -1,
                    tripTheme: "dark",
                    prevLabel: "Anterior",
                    nextLabel: "Siguiente",
                    finishLabel: "Entendido"
                };
                if ($scope.selectedTab == 1) {
                    showFormHelp(options);
                } else {
                    showUserSelectionHelp(options);
                }
            };

            /**
             * Shows tour-based help of all input fields.
             * @param options: Obj containing tour.js options
             */
            function showFormHelp(options) {
                var responsivePos = $mdMedia('xs') ? 'n' : 's';
                var tripToShowNavigation = new Trip([], options);

                var contentId = "Ingrese la cédula de identidad del " +
                "nuevo gestor.";
                var contentPsw = "Ingrese la contraseña con que el nuevo " +
                    "gestor ingresará al sistema.";
                var contentName = "Ingrese el nombre del gestor.";
                var contentLastName = "Ingrese el apellido del gestor.";
                if (typeof $scope.userId === "undefined") {
                    appendFieldHelp(tripToShowNavigation, "#user-id",
                        contentId, responsivePos);
                }
                if (typeof $scope.model.psw === "undefined") {
                    appendFieldHelp(tripToShowNavigation, "#user-psw",
                        contentPsw, responsivePos);
                }
                if (typeof $scope.model.name === "undefined") {
                    appendFieldHelp(tripToShowNavigation, "#user-name",
                        contentName, responsivePos);
                }
                if (typeof $scope.model.lastname === "undefined") {
                    appendFieldHelp(tripToShowNavigation, "#user-lastname",
                        contentLastName, responsivePos);
                }
                if (!$scope.missingField()) {
                    var content = "Haga click en REGISTRAR para crear " +
                        "el nuevo gestor.";
                    appendFieldHelp(tripToShowNavigation, "#register-btn",
                        content, 'n');
                }
                tripToShowNavigation.start();
            }

            function showUserSelectionHelp(options) {
                var responsivePos = $mdMedia('xs') ? 'n' : 's';
                var tripToShowNavigation = new Trip([], options);

                if (!$scope.selectedUser) {
                    var content = "Haga click para desplegar una lista " +
                        "con los usuarios gestores registrados en el " +
                        "sistema y escoja el usuario a eliminar."
                    appendFieldHelp(tripToShowNavigation, "#select-agent",
                        content, responsivePos);
                }
                if ($scope.selectedUser) {
                    var content = "Haga click en ELIMINAR para proceder " +
                        "con la eliminación del usuario seleccionado."
                    appendFieldHelp(tripToShowNavigation, "#remove-btn",
                        content, 'n');
                }
                tripToShowNavigation.start();
            }

            function appendFieldHelp(trip, id, content, pos) {
                trip.tripData.push(
                    { sel : $(id), content: content, position: pos,
                        animation: 'fadeInUp' }
                );
            }
        }
    };

    /**
     *  Will show result pane if any of the following queries were executed
     */
    $scope.fetchedRequests = function() {
        return $scope.showResult == 1||
            $scope.showResult == 2 ||
            $scope.showResult == 3;
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
        $scope.approvalReportError = '';
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
        // $scope.docs = [];
        // $scope.selectedReq = -1;
        $scope.showResult = -1;
        $scope.showOptions = true;
    };

    $scope.openMenu = function() {
       $mdSidenav('left').toggle();
    };

    $scope.getBulbColor = function(status, index) {
        // Requests by specific ID won't have coloured bulbs when selected ..
        // .. looks ugly
        if ($scope.showResult === 0 && $scope.selectedReq === index) {
            return;
        }
        if (status === "Recibida") {
            return {'color':'#FFC107'}; // 500 amber
        }
        if (status === "Aprobada") {
            return {'color':'#4CAF50'}; // 500 green
        }
        if (status === "Rechazada") {
            return {'color':'#F44336'}; // 500 red
        }
    };

    $scope.showPie = function() {
        $scope.pieloaded = false;
        if (dataChanged) {
            updatePie();
            setDataChanged(false);
        } else {
            console.log($scope.pieError);
            $scope.docs = [];
            if ($scope.pieError == '') { $scope.pieloaded = true; }
        }
        // Un-select requests
        $scope.selectedReq = -1;
    };

    function updatePie() {
        $scope.docs = [];
        $scope.loadingContent = true;
        if ($scope.showResult == 0) {
            // update user's pie
            $http.get('index.php/home/ManagerHomeController/getUserRequests',
                {params:{fetchId:$scope.fetchId}})
                .then(function (response) {
                    if (response.data.message == "success") {
                        $scope.report = response.data.report;
                        $scope.pie = response.data.pie;
                        drawPie(response.data.pie);
                        $scope.pieloaded = true;
                    } else {
                        $scope.pieError = response.data.error;
                    }
                    $scope.loadingContent = false;

                });
        } else if ($scope.showResult == 1 &&
                $scope.model.perform[1].status == 'Recibida') {
            // update status pie
            $http.get('index.php/home/ManagerHomeController/' +
                'fetchRequestsByStatus',
                {params:{status:"Recibida"}})
                .then(function (response) {
                    if (response.data.message == "success") {
                        $scope.report = response.data.report;
                        $scope.pie = response.data.pie;
                        drawPie(response.data.pie);
                        $scope.pieloaded = true;
                    } else {
                        $scope.pieError = response.data.error;
                    }
                    $scope.loadingContent = false;

                });
        } else if ($scope.showResult == 2 || $scope.showResult == 3) {
            // update date interval pie
            if ($scope.showResult == 2) {
                var from = $scope.model.perform[2].from;
                var to = $scope.model.perform[2].to;
            } else {
                var from = $scope.model.perform[3].date;
                var to = from;
            }
            $http.get('index.php/home/ManagerHomeController/' +
                'fetchRequestsByDateInterval',
                {params:{from:moment(from).format('DD/MM/YYYY'),
                    to:moment(to).format('DD/MM/YYYY')}})
                .then(function (response) {
                    if (response.data.message == "success") {
                        $scope.report = response.data.report;
                        $scope.pie = response.data.pie;
                        drawPie(response.data.pie);
                        $scope.pieloaded = true;
                    } else {
                        $scope.pieError = response.data.error;
                    }
                    $scope.loadingContent = false;

                });
        }
    }

    function drawPie(pie) {
        // Recycle the chart
        $scope.statisticsTitle = pie.title;
        $timeout(function() {
            // timeout will allow user to see the drawing animation
            if ($scope.chart !== null) {
                $scope.chart.destroy();
            }
            var ctx =  document.getElementById("piechart").getContext("2d");
            var data = {
                labels: pie.labels,
                datasets: [
                    {
                        data: pie.data,
                        backgroundColor: pie.backgroundColor,
                        hoverBackgroundColor: pie.hoverBackgroundColor
                    }]
            };
            var options = {
                tooltips: {
                  callbacks: {
                    label: function(tooltipItem, data) {
                        return data.datasets[tooltipItem.datasetIndex].
                            data[tooltipItem.index] + '%';
                    }
                  }
                },
                responsive: true
            };
            $scope.chart = new Chart(ctx, {
                type: 'pie',
                data: data,
                options: options
            });
        }, 200);
    }

    $scope.showHelp = function() {
        var options = {
            showNavigation : true,
            showCloseBox : true,
            delay : -1,
            tripTheme: "dark",
            prevLabel: "Anterior",
            nextLabel: "Siguiente",
            finishLabel: "Entendido"
        };
        if ($scope.pieloaded && $scope.docs.length == 0) {
            if ($scope.showResult == 0) {
                showSingleUserResultHelp(options);
            } else if ($scope.fetchedRequests()) {
                showMultipleUsersResultHelp(options);
            }
        } else if ($scope.docs.length == 0) {
            // User has not selected any request yet, tell him to do it.
            showSidenavHelp(options);
        } else {
            // Guide user through request selection's possible actions.
            showRequestHelp(options);
        }
    };

    /**
     * Shows tour-based help of single user result query
     * @param options: Obj containing tour.js options
     */
    function showSingleUserResultHelp(options) {
        options.showHeader = true;
        var tripToShowNavigation = new Trip([
            { sel : $("#piechart-tour"),
                content : "Esta tarjeta muestra las estadísticas de " +
                    "las solicitudes del afiliado. Los datos aparecen al " +
                    "mover el ratón hacia alguna de las divisiones de " +
                    "la gráfica.",
                position : "n", header: "Estadísticas", expose: true,
                animation: 'fadeInUp' },
            { sel : $("#report-btn"),
                content : "Puede generar un reporte detallado haciendo " +
                    "click aquí.",
                position : "s", header: "Generación de reporte", expose: true,
                animation: 'fadeInDown' }
        ], options);
        if ($mdSidenav('left').isLockedOpen()) {
            // Nav. panel information
            tripToShowNavigation.tripData.push(
                { sel : $("#user-data"),
                    content : "Consulte datos del afiliado",
                    position : "e", header: "Datos del afiliado",
                        animation: 'fadeInLeft' },
                { sel : $("#result-data"),
                    content : "Ésta es la lista de solicitudes del " +
                        "afiliado. Para facilitar la elección, el " +
                        "estatus de cada una está identificada por " +
                        "un bombillo amarillo, verde y rojo para " +
                        "Recibida, Aprobada y Rechazada, respectivamente.",
                    position : "e", header: "Préstamos personales",
                        animation: 'fadeInRight' },
                { sel : $("#back-to-query"),
                    content : "Para hacer otro tipo de consulta, haga " +
                        "click aquí.",
                    position : "e", header: "Atrás", animation: 'fadeInRight' }
            );
        }
        tripToShowNavigation.start();
    }

    /**
     * Shows tour-based help of multiple users result query
     * @param options: Obj containing tour.js options
     */
    function showMultipleUsersResultHelp(options) {
        options.showHeader = true;
        var tripToShowNavigation = new Trip([
            { sel : $("#piechart-tour"),
                content : "Esta tarjeta muestra las estadísticas de las " +
                    "solicitudes en cuestión. Los datos aparecen al mover" +
                    " el ratón hacia alguna de las divisiones de la gráfica.",
                position : "n", header: "Estadísticas", expose: true,
                animation: 'fadeInTop' },
            { sel : $("#report-btn"),
                content : "Puede generar un reporte detallado haciendo " +
                "click aquí.",
                position : "s", header: "Generación de reporte", expose: true,
                animation: 'fadeInDown' }
        ], options);
        if ($mdSidenav('left').isLockedOpen()) {
            // Nav. panel information
            if ($scope.showResult !== 1) {
                var content = "Éstas son las solicitudes resultantes de " +
                    "la búsqueda. Al hacer click en alguna de ellas, podrá" +
                    " consultar los datos del afiliado o ver los detalles " +
                    "de la solicitud. Para facilitar la elección, el " +
                    "estatus de cada una está identificada por un bombillo " +
                    " amarillo, verde y rojo para Recibida, Aprobada y " +
                    "Rechazada, respectivamente.";
            } else {
                var content = "Éstas son las solicitudes resultantes de " +
                "la búsqueda. Al hacer click en alguna de ellas, podrá " +
                "consultar los datos del afiliado o ver los detalles " +
                "de la solicitud.";
            }
            tripToShowNavigation.tripData.push(
                { sel : $("#result-data"),
                    content : content,
                    position : "e", header: "Solicitudes",
                        animation: 'fadeInRight' },
                { sel : $("#back-to-query"),
                    content : "Para hacer otro tipo de consulta, haga " +
                        "click aquí.",
                    position : "e", header: "Atrás",
                    animation: 'fadeInRight' }
            );
        }
        tripToShowNavigation.start();
    }

    /**
     * Shows tour-based help of side navigation panel
     * @param options: Obj containing tour.js options
     */
    function showSidenavHelp(options) {
        if ($mdSidenav('left').isLockedOpen()) {
            options.showHeader = true;
            var tripToShowNavigation = new Trip([
                {
                    sel : $("#pending-req"),
                    content : "Ésta es la lista de solicitudes por " +
                        "administrar. Al seleccionar alguna, puede " +
                        "verificar los datos del solicitante o ver " +
                        "los detalles de la solicitud para administrarla.",
                    position : "e", header: "Solicitudes pendientes",
                    animation: 'fadeInUp'
                },
                {
                    sel : $("#adv-search"),
                    content : "Puede realizar búsquedas más " +
                        "específicas de las solicitudes. Sólo seleccione" +
                        " el tipo de consulta e ingrese los datos solicitados.",
                    position : "e", header: "Búsqueda avanzada",
                    animation: 'fadeInUp'
                },
                {
                    sel: $("#approval-report"),
                    content : "También puede generar reportes de solicitudes " +
                              "cerradas durante la semana vigente o en un" +
                              " rango de fechas específico.",
                    position : "e", header: "Reporte de solicitudes cerradas",
                    animation: 'fadeInUp'
                }

            ], options);
            tripToShowNavigation.start();
        } else {
            var tripToShowNavigation = new Trip([
                { sel : $("#nav-panel"),
                    content : "Haga click en el ícono para abrir el " +
                        "panel de navegación, donde podrá elegir las" +
                        " solicitudes a administrar o realizar " +
                        "búsquedas avanzadas.",
                    position : "s", animation: 'fadeInUp'}
            ], options);
            tripToShowNavigation.start();
        }
    }

     /**
      * Shows tour-based help of selected request details section.
      * @param options: Obj containing tour.js options
      */
    function showRequestHelp(options) {
        options.showHeader = true;
        // options.showSteps = true;
        var tripToShowNavigation = new Trip([
            // Request summary information
            { sel : $("#request-summary"), content : "Aquí se muestra " +
                "información acerca de la fecha de creación, monto " +
                "solicitado, y un comentario de haberlo realizado.",
                position : "s", header: "Resumen de la solicitud",
                expose : true },
            // Request status information
            { sel : $("#request-status-summary"), content : "Esta sección " +
                "provee información acerca del estatus de la solicitud.",
                position : "s", header: "Resumen de estatus", expose : true,
                animation: 'fadeInDown' },
            // Request documents information
            { sel : $("#request-docs"), content : "Éste y los siguientes " +
                "items contienen el nombre y, de existir, una descripción " +
                "de cada documento en la solicitud. Puede " +
                "verlos/descargarlos haciendo click encima de ellos.",
                position : "s", header: "Documentos", expose : true,
                animation: 'fadeInDown' }
        ], options);

        if ($mdSidenav('left').isLockedOpen()) {
            tripToShowNavigation.tripData.push(
                // Download as zip information
                { sel : $("#request-summary-actions"), content : "Puede ver " +
                    "el historial de la solicitud, editarla " +
                    "(si la solicitud no se ha cerrado), o descargar todos " +
                    "sus documentos presionando el botón correspondiente.",
                    position : "w", header: "Acciones", expose : true,
                    animation: 'fadeInLeft' }
            );
        } else {
            tripToShowNavigation.tripData.push(
                // Download as zip information request-summary-actions-menu
                { sel : $("#request-summary-actions-menu"),
                    content : "Haga click en el botón de opciones para " +
                    "ver el historial de la solicitud, editarla " +
                    "(si la solicitud no se ha cerrado), o descargar " +
                    "todos sus documentos.",
                    position : "s", header: "Acciones", expose : true,
                    animation: 'fadeInLeft' }
            );
        }
        tripToShowNavigation.start();
    }


    $scope.generateExcelReport = function() {
        $scope.loadingReport = true;
        var url = '';
        if ($scope.showResult == 0 || $scope.showResult > 1) {
            $scope.report.sheetTitle = $scope.showResult > 1 ?
                "Reporte por fechas" : "Reporte de afiliado";
            url = 'index.php/documents/DocumentGenerator/' +
                'generateRequestsReport';
        } else if ($scope.showResult == 1) {
            url = 'index.php/documents/DocumentGenerator/' +
                'generateStatusRequestsReport';
        } else {
            // Approved requests report
            url = 'index.php/documents/DocumentGenerator/' +
                'generateApprovedRequestsReport';
        }
        var report = JSON.stringify($scope.report);
        $http.post(url, report).then(function (response) {
            if (response.data.message == "success") {
                location.href = 'index.php/documents/DocumentGenerator/' +
                    'downloadReport?lpath=' + response.data.lpath;
            }
            $scope.loadingReport = false;
        });
    };

    /**
     * Helper function that performs as setter for dataChanged var.
     * @param val: New value
     */
    function setDataChanged(val) {
        dataChanged = val;
    }

    /**
     * Helper function that resets UI for all query results
     */
    function resetContent() {
        $scope.requests = [];
        $scope.selectedReq = -1;
        $scope.docs = [];
        $scope.showList = false;
        $scope.showApprovedAmount = false;
        $scope.fetchError = '';
        $scope.approvalReportError = '';
        $scope.pieError = '';
        $scope.showApprovedAmount = false;
        $scope.pieloaded = false;
    }
}

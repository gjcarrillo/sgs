angular
    .module('sgdp')
    .controller('ManagerHomeController', managerHome);

managerHome.$inject = ['$scope', '$mdDialog', '$state', '$timeout', '$mdSidenav',
                       '$mdMedia', 'Utils', 'Requests', 'Helps', 'Constants', 'Manager', 'Config'];

function managerHome($scope, $mdDialog, $state, $timeout, $mdSidenav, $mdMedia,
                     Utils, Requests, Helps, Constants, Manager, Config) {
    'use strict';
    $scope.model = Manager.data.model;
    $scope.selectedQuery = Manager.data.selectedQuery;
    $scope.showApprovedAmount = Manager.data.showApprovedAmount;
    $scope.showOptions = Manager.data.showOptions;
    $scope.showResult = Manager.data.showResult;
    $scope.chart = Manager.data.chart;
    $scope.queries = Manager.data.queries;
    $scope.selectedReq = Manager.data.selectedReq;
    $scope.selectedLoan = Manager.data.selectedLoan;
    $scope.selectedPendingReq = Manager.data.selectedPendingReq;
    $scope.selectedPendingLoan = Manager.data.selectedPendingLoan;
    $scope.req = Manager.data.req; // Will contain the selected request object.
    $scope.requests = Manager.data.requests;
    $scope.pendingRequests = Manager.data.pendingRequests;
    $scope.fetchError = Manager.data.fetchError;
    $scope.approvalReportError = Manager.data.approvalReportError ;
    $scope.pie = Manager.data.pie;
    $scope.pieError = Manager.data.pieError;
    $scope.showList = Manager.data.showList;
    $scope.showPendingList = Manager.data.showPendingList;
    $scope.showPendingReq = Manager.data.showPendingReq;
    $scope.showAdvSearch = Manager.data.showAdvSearch;
    // Re-draw the pie if necessary.
    if ($scope.pie) {
        drawPie($scope.pie);
    }

    $scope.statuses = Requests.getAllStatuses();
    $scope.APPROVED_STRING = Constants.Statuses.APPROVED;
    $scope.REJECTED_STRING = Constants.Statuses.REJECTED;
    $scope.RECEIVED_STRING = Constants.Statuses.RECEIVED;
    $scope.listTitle = Requests.getRequestsListTitle();
    $scope.mappedStatuses = Requests.getAllStatuses();
    $scope.loanTypes = Requests.getAllLoanTypes();
    $scope.mappedLoanTypes = Requests.getLoanTypesTitles();

    $scope.loadingContent = false;
    $scope.idPrefix = "V";
    $scope.loading = false;
    $scope.loadingReport = false;

    // dataChanged notifies whether data changed through user interaction,
    // thus needing to update pie and report data
    var dataChanged = false;


    // Fetch pending requests and automatically show first one to user (if any)
    if ($scope.selectedReq == '' && $scope.selectedPendingReq == '') {
        loadPendingRequests();
    }

    /**
     * Helper function that loads pending requests.
     */
    function loadPendingRequests() {
        $scope.loadingContent = true;
        Manager.fetchPendingRequests().then(
            function (data) {
                $scope.pendingRequests = data.requests;
                if (!Utils.isObjEmpty(data.requests)) {
                    // Give 500ms to render the list in the view
                    // Otherwise 'Empty list' msg will appear briefly.
                    $timeout(function() {
                        $scope.showPendingReq = true;
                        $mdSidenav('left').open();
                    }, 500);
                }
                $scope.loadingContent = false;
            }, function () {
                $scope.loadingContent = false;
            });
    }

    $scope.fetchUserRequests = function(index) {
        resetContent();
        $scope.loading = true;
        $scope.fetchId = $scope.idPrefix + $scope.model.perform[index].id;
        Manager.getUserRequests($scope.fetchId)
            .then(
            function (data) {
                $scope.requests = data.requests;
                $scope.showOptions = false;
                $scope.showResult = index;
                console.log($scope.showResult);
                $scope.pieloaded = true;
                $scope.report = data.report;
                $scope.pie = data.pie;
                $scope.pieloaded = true;
                drawPie(data.pie);
                $scope.loading = false;
            },
            function (error) {
                $scope.fetchError = error;
                $scope.loading = false;
            }
        );
    };

    $scope.fetchRequestsByStatus = function(status, index) {
        resetContent();
        $scope.loading = true;
        Manager.fetchRequestsByStatus(status)
            .then(
            function (data) {
                $scope.requests = data.requests;
                $scope.showOptions = false;
                $scope.showResult = index;
                $scope.pieloaded = true;
                $scope.pie = data.pie;
                drawPie(data.pie);
                $scope.report = data.report;
                $scope.report.status = status;
                $scope.loading = false;
            },
            function (error) {
                $scope.fetchError = error;
                $scope.loading = false;
            }
        );
    };

    $scope.fetchRequestsByLoanType = function(loanType, index) {
        resetContent();
        $scope.loading = true;
        console.log(loanType);
        Manager.fetchRequestsByLoanType(loanType)
            .then(
            function (data) {
                $scope.requests = data.requests;
                $scope.showOptions = false;
                $scope.showResult = index;
                $scope.pieloaded = true;
                $scope.pie = data.pie;
                drawPie(data.pie);
                $scope.report = data.report;
                $scope.loading = false;
            },
            function (error) {
                $scope.fetchError = error;
                $scope.loading = false;
            }
        );
    };

    $scope.fetchPendingRequests = function(index) {
        $scope.loading = true;
        Manager.fetchPendingRequests().then(
            function (data) {
                $scope.requests = data.requests;
                $scope.showOptions = false;
                $scope.showResult = index;
                $scope.pieloaded = true;
                $scope.pie = data.pie;
                drawPie(data.pie);
                $scope.report = data.report;
                $scope.loading = false;
            },
            function (error) {
                $scope.fetchError = error;
                $scope.loading = false;
            }
        );
    };

    $scope.fetchRequestsByDateInterval = function(from, to, index) {
        resetContent();
        $scope.loading = true;
        Manager.fetchRequestsByDateInterval (from, to)
            .then(
            function (data) {
                $scope.requests = data.requests;
                $scope.showOptions = false;
                $scope.showResult = index;
                $scope.pieloaded = true;
                $scope.pie = data.pie;
                drawPie(data.pie);
                $scope.report = data.report;
                $scope.loading = false;
            },
            function (error) {
                $scope.fetchError = error;
                $scope.loading = false;
            }
        );
    };

    $scope.fetchRequestsByExactDate = function(date, index) {
        resetContent();
        $scope.loading = true;
        Manager.fetchRequestsByExactDate(date)
            .then(
            function (data) {
                $scope.requests = data.requests;
                $scope.showOptions = false;
                $scope.showResult = index;
                $scope.pieloaded = true;
                $scope.pie = data.pie;
                drawPie(data.pie);
                $scope.report = data.report;
                $scope.loading = false;
            },
            function (error) {
                $scope.fetchError = error;
                $scope.loading = false;
            }
        );
    };

    $scope.getApprovedAmountByDateInterval = function(from, to) {
        $mdSidenav('left').toggle();
        resetContent();
        $scope.loading = true;
        Manager.getApprovedAmountByDateInterval(from, to)
            .then(
            function (amount) {
                $scope.approvedAmount = amount;
                $scope.approvedAmountTitle = "Monto aprobado total " +
                                             "para el intervalo de fecha especificado:";
                $scope.showApprovedAmount = true;
                $scope.loading = false;
            },
            function (error) {
                $scope.fetchError = error;
                $scope.loading = false;
            }
        );
    };

    $scope.getApprovedAmountById = function(index) {
        $mdSidenav('left').toggle();
        resetContent();
        $scope.loading = true;
        var userId = $scope.idPrefix + $scope.model.perform[index].id;
        Manager.getApprovedAmountById(userId)
            .then(
            function (data) {
                $scope.approvedAmount = data.approvedAmount;
                $scope.approvedAmountTitle = "Monto aprobado total para " +
                                             data.username + ":";
                $scope.showApprovedAmount = true;
                $scope.loading = false;
            },
            function (error) {
                $scope.fetchError = error;
                $scope.loading = false;
            }
        );
    };

    $scope.getClosedReportByCurrentWeek = function() {
        $mdSidenav('left').toggle();
        resetContent();
        $scope.loadingReport = true;
        Manager.getClosedReportByCurrentWeek()
            .then(
            function (report) {
                $scope.report = report;
                $scope.generateExcelReport();
            },
            function (error) {
                $scope.approvalReportError = error;
                $scope.loadingReport = false;
            }
        );
    };

    $scope.getClosedReportByDateInterval = function(from, to) {
        $mdSidenav('left').toggle();
        resetContent();
        $scope.loadingReport = true;
        Manager.getClosedReportByDateInterval(from, to)
            .then(
            function (report) {
                $scope.report = report;
                $scope.generateExcelReport();
            },
            function (error) {
                $scope.approvalReportError = error;
                $scope.loadingReport = false;
            }
        );
    };

    $scope.toggleList = function(index) {
        $scope.showList[index] = !$scope.showList[index];
    };

    $scope.togglePendingList = function(index) {
        $scope.showPendingList[index] = !$scope.showPendingList[index];
    };

    $scope.loadUserData = function(userId) {
        sessionStorage.setItem("fetchId", userId);
        window.open(Utils.getUserDataUrl(), '_blank');
        // Data not being changed so far - don't request data again if
        // user clicks on Stats
        setDataChanged(false);
    };

    $scope.selectRequest = function(req, loan) {
        $scope.selectedPendingReq = '';
        $scope.selectedPendingLoan = -1;
        $scope.selectedReq = req;
        $scope.selectedLoan = loan;
        if (req != '' && loan != -1) {
            $scope.req = $scope.requests[req][loan];
        }
        // Data not being changed so far - don't request data again if
        // user clicks on Stats
        setDataChanged(false);
        // Close sidenav
        $mdSidenav('left').toggle();
    };

    $scope.selectPendingReq = function(req, loan) {
        $scope.selectedReq = '';
        $scope.selectedLoan = -1;
        $scope.selectedPendingReq = req;
        $scope.selectedPendingLoan = loan;
        if (req != '' && loan != -1) {
            $scope.req = $scope.pendingRequests[req][loan];
        }
        // Data not being changed so far - don't request data again if
        // user clicks on Stats
        setDataChanged(false);
        // Close sidenav
        $mdSidenav('left').toggle();
    };

    $scope.loadStatuses = function() {
        $scope.onStatusOpen();
        return Config.getStatuses().then(
            function (statuses) {
                $scope.statuses = Requests.getAllStatuses();
                $scope.statuses = $scope.statuses.concat(statuses);
            }
        );
    };

    // Calculates the request's payment fee.
    $scope.calculatePaymentFee = function() {
        return Requests.calculatePaymentFee($scope.req.reqAmount, $scope.req.due, 12);
    };

    // Helper function for formatting numbers with leading zeros
    $scope.pad = function(n, width, z) {
        return Utils.pad(n, width, z);
    };

    $scope.loadHistory = function() {
        // Save necessary data before changing views.
        preserveState();
        // Send required data to history
        sessionStorage.setItem("req", JSON.stringify($scope.req));
        $state.go('history');
    };

    $scope.downloadDoc = function(doc) {
        window.open(Requests.getDocDownloadUrl(doc.lpath));
    };

    $scope.downloadAll = function() {
        location.href = Requests.getAllDocsDownloadUrl($scope.req.docs);
    };

    function preserveState() {
        var data = {};
        data.model = $scope.model;
        data.selectedQuery = $scope.selectedQuery;
        data.showApprovedAmount = $scope.showApprovedAmount;
        data.showOptions = $scope.showOptions;
        data.showResult = $scope.showResult;
        data.chart = $scope.chart;
        data.queries = $scope.queries;
        data.selectedReq = $scope.selectedReq;
        data.selectedLoan = $scope.selectedLoan;
        data.selectedPendingReq = $scope.selectedPendingReq;
        data.selectedPendingLoan = $scope.selectedPendingLoan;
        data.req = $scope.req; // Will contain the selected request object.
        data.requests = $scope.requests;
        data.pendingRequests = $scope.pendingRequests;
        data.fetchError = $scope.fetchError;
        data.approvalReportError = $scope.approvalReportError ;
        data.pie = $scope.pie;
        data.pieError = $scope.pieError;
        data.showList = $scope.showList;
        data.showPendingList = $scope.showPendingList;
        data.showPendingReq = $scope.showPendingReq;
        data.showAdvSearch = $scope.showAdvSearch;
        Manager.updateData(data);
    }

    /**
    * Custom dialog for updating an existing request
    */
    $scope.openEditRequestDialog = function($event, obj) {
        var parentEl = angular.element(document.body);
        $mdDialog.show({
            parent: parentEl,
            targetEvent: $event,
            templateUrl: 'ManageRequestController',
            clickOutsideToClose: false,
            escapeToClose: false,
            fullscreen: $mdMedia('xs'),
            locals: {
                fetchId: $scope.fetchId,
                request: $scope.req,
                parentScope: $scope,
                obj: obj
            },
            controller: DialogController
        });

        // Isolated dialog controller
        function DialogController($scope, $mdDialog, fetchId, request, parentScope, obj) {
            $scope.files = [];
            $scope.fetchId = fetchId;
            $scope.uploading = false;
            $scope.request = request;
            $scope.mappedStatuses = Requests.getAllStatuses();
            $scope.APPROVED_STRING = Constants.Statuses.APPROVED;
            $scope.REJECTED_STRING = Constants.Statuses.REJECTED;
            $scope.RECEIVED_STRING = Constants.Statuses.RECEIVED;

            if (obj) {
                $scope.model = obj;
                if (obj.confirmed) updateRequest();
            } else {
                $scope.model = {};
                if ($scope.mappedStatuses.indexOf(request.status) == -1) {
                    $scope.mappedStatuses.push(request.status);
                }
                $scope.model.status = request.status;
                $scope.model.comment = $scope.request.comment;
                $scope.model.approvedAmount = $scope.request.reqAmount;
            }

            $scope.closeDialog = function() {
                $mdDialog.hide();
            };

            $scope.missingField = function() {
                if ($scope.model.status == $scope.APPROVED_STRING) {
                    return typeof $scope.model.approvedAmount === "undefined";
                } else {
                    return ($scope.model.status == request.status &&
                        (typeof $scope.model.comment === "undefined"
                        || $scope.model.comment == ""
                        || $scope.model.comment == $scope.request.comment));
                }
            };

            $scope.loadStatuses = function() {
                return Config.getStatuses().then(
                    function (statuses) {
                        $scope.mappedStatuses = Requests.getAllStatuses();
                        $scope.mappedStatuses = $scope.mappedStatuses.concat(statuses);
                    }
                );
            };

            /**
             * Verifies if this request is being closed. If so, warn user that
             * no more edition will be available after closure.
             *
             * @param ev - user event.
             */
            $scope.verifyEdition = function(ev) {
                if ($scope.model.status === $scope.APPROVED_STRING ||
                    $scope.model.status === $scope.REJECTED_STRING) {
                    confirmClosure(ev);
                } else {
                    updateRequest();
                }
            };

            // Shows a dialog asking user to confirm the request closure.
            function confirmClosure(ev) {
                Utils.showConfirmDialog(
                    'Advertencia',
                    'Al cambiar el estatus de la solicitud a ' + $scope.model.status +
                    ' se cerrará la solicitud y no se podrán realizar más cambios. ¿Desea proceder?',
                    'Sí', 'Cancelar', ev, true
                ).then(
                    function () {
                        // Re-open parent dialog and perform request creation
                        $scope.model.confirmed = true;
                        parentScope.openEditRequestDialog(null, $scope.model);
                    },
                    function () {
                        // Re-open parent dialog and do nothing
                        parentScope.openEditRequestDialog(null, $scope.model);
                    }
                );
            }

            // Updates the request.
            function updateRequest() {
                $scope.uploading = true;
                $scope.request.status = $scope.model.status;
                $scope.request.comment = $scope.model.comment;
                $scope.request.reunion = $scope.model.reunion;
                if ($scope.model.status == $scope.APPROVED_STRING) {
                    $scope.request.approvedAmount = $scope.model.approvedAmount;
                }
                Manager.updateRequest($scope.request)
                    .then(
                    function () {
                        // Update data on the pending request list so that changes would reflect
                        // even when updating from advanced query lists.
                        updatePendingList($scope.request.id, $scope.model.status,
                                          $scope.model.comment, $scope.model.reunion,
                                          $scope.model.approvedAmount);
                        // Notify that data has changed, thus updating stats when requested
                        setDataChanged(true);
                        // Close dialog and alert user that operation was successful
                        $mdDialog.hide();
                        $scope.uploading = false;
                        Utils.showAlertDialog('Solicitud actualizada',
                                              'La solicitud fue actualizada exitosamente.');
                    },
                    function (error) {
                        $scope.uploading = false;
                        Utils.showAlertDialog('Oops!', error);
                    }
                );
            }

            $scope.showHelp = function() {
                showFormHelp(Helps.getDialogsHelpOpt());
            };

            /**
             * Shows tour-based help of all input fields.
             * @param options: Obj containing tour.js options
             */
            function showFormHelp(options) {
                var trip = new Trip([], options);
                var content = '';
                if (typeof $scope.model.comment === "undefined" ||
                    $scope.model.comment == "" ||
                    $scope.model.comment == $scope.request.comment) {
                    content = "Agregue un comentario (opcional) " +
                    "hacia la solicitud.";

                    Helps.addFieldHelp(trip, "#comment", content, 's');
                }
                if ($scope.model.status == $scope.RECEIVED_STRING) {
                    content = "Seleccione el nuevo estatus de la " +
                        "solicitud.";
                    Helps.addFieldHelp(trip, "#status", content, 's');
                }
                if (($scope.model.status == $scope.APPROVED_STRING ||
                    $scope.model.status == $scope.REJECTED_STRING)
                    && typeof $scope.model.reunion === "undefined") {
                    content = "Agrege el número de reunión (opcional).";
                    Helps.addFieldHelp(trip, "#reunion",
                        content, 'n');
                }
                if ($scope.model.status == $scope.APPROVED_STRING
                    && typeof $scope.model.approvedAmount === "undefined") {
                    content = "Agrege el monto aprobado en Bs.";
                    Helps.addFieldHelp(trip, "#approved-amount",
                        content, 'n');
                }
                if (!$scope.missingField()) {
                    content = "Haga click en ACTUALIZAR para guardar " +
                        "los cambios.";
                    Helps.addFieldHelp(trip, "#edit-btn",
                        content, 'n');
                }
                trip.start();
            }
        }
    };

    /**
     * Helper function that updates the pending requests list by reflecting updates
     * from the selected request.
     *
     * @param id - unique id of the request to update.
     * @param status - new status
     * @param comment - new comment
     * @param reunion - request closure's reunion
     * @param approvedAmount - request's approved amount
     */
    function updatePendingList(id, status, comment, reunion, approvedAmount) {
        var index = Requests.findRequest($scope.pendingRequests, id);

        $scope.pendingRequests[index.request][index.loan].status = status;
        $scope.pendingRequests[index.request][index.loan].comment = comment;
        $scope.pendingRequests[index.request][index.loan].reunion = reunion;
        if (status == $scope.APPROVED_STRING) {
            $scope.pendingRequests[index.request][index.loan].approvedAmount = approvedAmount;
        }
    }

    /**
     * Dialog that prompts for new agent user information
     */
    $scope.openManageUserAgentDialog = function($event) {
        var parentEl = angular.element(document.body);
        $mdDialog.show({
            parent: parentEl,
            targetEvent: $event,
            templateUrl: 'ManageAgentUsers',
            fullscreen: $mdMedia('xs'),
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
                    typeof $scope.model.password === "undefined" ||
                    typeof $scope.model.firstName === "undefined" ||
                    typeof $scope.model.lastName === "undefined"
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
                var postData = JSON.parse(JSON.stringify($scope.model));
                postData.id = $scope.idPrefix + $scope.userId;
                postData.phone = Utils.pad($scope.model.phone, 11);
                Manager.createNewAgent(postData)
                    .then(
                    function (created) {
                        if (created) {
                            $mdDialog.hide();
                            Utils.showAlertDialog('Operación exitosa',
                                                  'El nuevo usuario Gestor ha sido registrado exitosamente');
                        } else {
                            Utils.showConfirmDialog(
                                'Advertencia',
                                'El usuario ' + $scope.model.id + ' se encuentra registrado.<br/><br/> ' +
                                '¿Desea concederle privilegios de AGENTE?',
                                'Sí', 'Cancelar', $event, true
                            ).then(
                                function() {
                                    // Re-open parent dialog and perform request creation
                                    Manager.upgradeApplicant($scope.model.id).then(
                                        function () {
                                            Utils.showAlertDialog('Operación exitosa',
                                                                  'Se han otorgado privilegios de AGENTE al usuario '
                                                                  + $scope.model.id);
                                        },
                                        function (error) {
                                            Utils.showAlertDialog('Oops!', error);
                                        }
                                    );
                                }
                            );
                        }
                        $scope.uploading = false;
                    },
                    function (error) {
                        $scope.uploading = false;
                        Utils.showAlertDialog('Oops!', error);
                    }
                );
            };

            /**
             * Build `userAgents` list of key/value pairs
             */
            $scope.selectedUser = null;

            $scope.fetchAllAgents = function () {
                $scope.onUsersOpen();
                return Manager.fetchAllAgents()
                    .then(
                    function (agents) {
                        $scope.userAgents = agents;
                    },
                    function (error) {
                        Utils.showAlertDialog('Oops!', error);
                    }
                );
            };

            // TODO: Implement directive.
            $scope.onUsersOpen = function() {
                $scope.userBackup = $scope.selectedUser;
                $scope.selectedUser = null;
            };

            $scope.onUsersClose = function() {
                if ($scope.selectedUser === null) {
                    $scope.selectedUser = $scope.userBackup;
                }
            };

            $scope.degradeUser = function() {
                $scope.errorMsg = '';
                $scope.uploading = true;
                Manager.degradeAgent($scope.selectedUser.value)
                    .then(
                    function () {
                        // Close dialog and alert user that operation was successful.
                        $mdDialog.hide();
                        $scope.uploading = false;
                        Utils.showAlertDialog('Operación exitosa',
                                              'Los privilegios de AGENTE han sido revocados del usuario ' +
                                              $scope.selectedUser.value);
                    },
                    function (error) {
                        $scope.uploading = false;
                        Utils.showAlertDialog('Oops!', error);
                    }
                );
            };

            $scope.showHelp = function() {
                if ($scope.selectedTab == 1) {
                    showFormHelp(Helps.getDialogsHelpOpt());
                } else {
                    showUserSelectionHelp(Helps.getDialogsHelpOpt());
                }
            };

            /**
             * Shows tour-based help of all input fields.
             * @param options: Obj containing tour.js options
             */
            function showFormHelp(options) {
                var responsivePos = $mdMedia('xs') ? 'n' : 's';
                var trip = new Trip([], options);

                var contentId = "Ingrese la cédula de identidad del " +
                "nuevo gestor.";
                var contentPsw = "Ingrese la contraseña con que el nuevo " +
                    "gestor ingresará al sistema.";
                var contentName = "Ingrese el nombre del gestor.";
                var contentLastName = "Ingrese el apellido del gestor.";
                var contentPhone = "Ingrese el número telefónico (opcional).";
                var contentEmail = "Ingrese el correo electrónico (opcional).";
                if (typeof $scope.userId === "undefined") {
                    Helps.addFieldHelp(trip, "#user-id",
                        contentId, responsivePos);
                }
                if (typeof $scope.model.psw === "undefined") {
                    Helps.addFieldHelp(trip, "#user-psw",
                        contentPsw, responsivePos);
                }
                if (typeof $scope.model.name === "undefined") {
                    Helps.addFieldHelp(trip, "#user-name",
                        contentName, responsivePos);
                }
                if (typeof $scope.model.lastname === "undefined") {
                    Helps.addFieldHelp(trip, "#user-lastname",
                        contentLastName, responsivePos);
                }
                if (typeof $scope.model.phone === "undefined") {
                    Helps.addFieldHelp(trip, "#user-phone",
                                       contentPhone, responsivePos);
                }
                if (typeof $scope.model.phone === "undefined") {
                    Helps.addFieldHelp(trip, "#user-email",
                                       contentEmail, responsivePos);
                }
                if (!$scope.missingField()) {
                    var content = "Haga click en REGISTRAR para crear " +
                        "el nuevo gestor.";
                    Helps.addFieldHelp(trip, "#register-btn",
                        content, 'n');
                }
                trip.start();
            }

            function showUserSelectionHelp(options) {
                var responsivePos = $mdMedia('xs') ? 'n' : 's';
                var trip = new Trip([], options);
                var content = '';

                if (!$scope.selectedUser) {
                    content = "Haga click para desplegar una lista " +
                        "con los usuarios gestores registrados en el " +
                        "sistema y escoja el usuario a eliminar.";
                    Helps.addFieldHelp(trip, "#select-agent",
                        content, responsivePos);
                }
                if ($scope.selectedUser) {
                    content = "Haga click en ELIMINAR para proceder " +
                        "con la eliminación del usuario seleccionado.";
                    Helps.addFieldHelp(trip, "#remove-btn",
                        content, 'n');
                }
                trip.start();
            }
        }
    };

    $scope.openConfigDialog = function($event) {
        var parentEl = angular.element(document.body);
        $mdDialog.show({
            parent: parentEl,
            targetEvent: $event,
            fullscreen: $mdMedia('xs'),
            templateUrl: 'ConfigController',
            clickOutsideToClose: false,
            escapeToClose: false,
            controller: DialogController
        });

        function DialogController($mdDialog, $scope, Config) {
            $scope.uploading = false;
            /**
             * =================================================
             *          Requests status configuration
             * =================================================
             */

            $scope.statuses = {};
            $scope.statuses.systemStatuses = Requests.getAllStatuses();
            $scope.statuses.newStatuses = $scope.statuses.existing = [];
            $scope.statuses.errorMsg = '';
            $scope.statuses.loading = true;

            Config.getStatusesForConfig()
                .then(
                function (statuses) {
                    $scope.statuses.loading = false;
                    $scope.statuses.newStatuses = statuses.existing;
                    // Create a DEEP copy of the existing statuses array.
                    $scope.statuses.existing = JSON.parse(JSON.stringify(statuses.existing));
                    $scope.statuses.inUse = statuses.inUse;
                },
                function (err) {
                    $scope.statuses.errorMsg = err;
                    $scope.statuses.loading = false;
                }
            );

            /**
             * Checks whether statuses have been updated at all.
             *
             * @returns {boolean} {@code true} if statuses have changed.
             */
            $scope.updatedStatuses = function() {
                return !Utils.isArrayEqualsTo($scope.statuses.newStatuses, $scope.statuses.existing);
            };

            $scope.updateStatuses = function() {
                $scope.uploading = true;
                var toSave = $scope.statuses.newStatuses.concat($scope.statuses.inUse);
                Config.saveStatuses (toSave)
                    .then (
                    function () {
                        $scope.uploading = false;
                        Utils.showAlertDialog('Actualización exitosa',
                                              'Los estatus de sus solicitudes han sido exitosamente actualizados.');
                        Manager.clearData();
                        $state.go($state.current, {}, {reload: true});
                    },
                    function (err) {
                        $scope.uploading = false;
                        Utils.showAlertDialog('Oops!', err);
                        Manager.clearData();
                        $state.go($state.current, {}, {reload: true});
                    }
                );
            };

            /**
             * =================================================
             *      Max & Min amount request configuration
             * =================================================
             */
            $scope.amount = {max: {}, min: {}, errorMsg: ''};
            $scope.amount.max.loading = true;
            Config.getMaxReqAmount()
                .then (
                function (maxAmount) {
                    $scope.amount.max.loading = false;
                    $scope.amount.max.existing = maxAmount;
                    $scope.amount.max.new = maxAmount;
                },
                function (err) {
                    $scope.amount.max.loading = false;
                    $scope.amount.errorMsg = err;
                }
            );

            $scope.amount.min.loading = true;
            Config.getMinReqAmount()
                .then (
                function (minAmount) {
                    $scope.amount.min.loading = false;
                    $scope.amount.min.existing = minAmount;
                    $scope.amount.min.new = minAmount;
                },
                function (err) {
                    $scope.amount.min.loading = false;
                    $scope.amount.errorMsg = err;
                }
            );

            $scope.updateReqAmount = function() {
                $scope.uploading = true;
                Config.updateReqAmount($scope.amount.min.new, $scope.amount.max.new)
                    .then (
                    function () {
                        $scope.uploading = false;
                        Utils.showAlertDialog('Actualización exitosa',
                                              'La cantidad posible de dinero a solicitar ha sido exitosamente ' +
                                              'actualizada.');
                    },
                    function (err) {
                        $scope.uploading = false;
                        Utils.showAlertDialog('Oops!', err);
                    }
                );
            };

            $scope.missingField = function() {
                return (typeof $scope.amount.min.new === "undefined" ||
                        typeof $scope.amount.max.new === "undefined") ||
                       ($scope.amount.min.existing === $scope.amount.min.new &&
                       $scope.amount.max.existing === $scope.amount.max.new);
            };

            /**
             * =================================================
             *         Requests span time configuration
             * =================================================
             */

            $scope.missingSpan = function() {
                return typeof $scope.span.newValue === "undefined" ||
                       $scope.span.newValue === $scope.span.existing;
            };

            $scope.span = {errorMsg: '', loading: true};
            Config.getRequestsSpan()
                .then(
                function (span) {
                    $scope.span.loading = false;
                    $scope.span.newValue = $scope.span.existing = span;
                },
                function (err) {
                    $scope.span.errorMsg = err;
                    $scope.span.loading = false;
                }
            );

            $scope.updateRequestsSpan = function () {
                $scope.uploading = true;
                Config.updateRequestsSpan($scope.span.newValue)
                    .then(
                    function () {
                        $scope.uploading = false;
                        Utils.showAlertDialog('Actualización exitosa',
                                              'El lapso a esperar para realizar diferentes solicitudes ' +
                                              'del mismo tipo ha sido actualizado.');
                    },
                    function (err) {
                        $scope.span.errorMsg = err;
                        $scope.uploading = false;
                    }
                );
            };

            /**
             * =================================================
             *                  SHARED CODE
             * =================================================
             */

            $scope.closeDialog = function() {
                $mdDialog.hide();
            };

            $scope.showHelp = function() {
                if ($scope.selectedTab == 1) {
                    showStatusHelp(Helps.getDialogsHelpOpt());
                } else if ($scope.selectedTab == 2) {
                    showReqAmountHelp(Helps.getDialogsHelpOpt());
                } else if ($scope.selectedTab == 3) {
                    showSpanHelp(Helps.getDialogsHelpOpt());
                }
            };

            /**
             * Shows tour-based help of all input fields.
             * @param options: Obj containing tour.js options
             */
            function showStatusHelp(options) {
                var trip = new Trip([], options);

                var contentChip = "Para agregar otros estatus, escríbalo y presione ENTER. Para eliminar " +
                                  "estatus existentes, bórrelos con el teclado o haga clic en la 'X'.";
                Helps.addFieldHelp(trip, "#additional-statuses", contentChip, 'n');
                if ($scope.updatedStatuses()) {
                    var content = "Haga clic en GUARDAR para hacer efectivo " +
                                  "los cambios.";
                    Helps.addFieldHelp(trip, "#save-statuses", content, 'n');
                }
                trip.start();
            }

            function showReqAmountHelp(options) {
                var trip = new Trip([], options);
                var content;

                content = "Actualice el monto mínimo a solicitar permitido.";
                Helps.addFieldHelp(trip, "#min-amount", content, 'n');
                content = "Actualice el monto máximo a solicitar permitido.";
                Helps.addFieldHelp(trip, "#max-amount", content, 'n');
                if (!$scope.missingField()) {
                    content = "Haga click en GUARDAR para hacer efectivo " +
                              "los cambios.";
                    Helps.addFieldHelp(trip, "#save-amounts", content, 'n');
                }
                trip.start();
            }

            function showSpanHelp(options) {
                var trip = new Trip([], options);
                var content;

                content = "Actualice el tiempo a esperar (en meses) para realizar diferentes " +
                          "solicitudes del mismo tipo.";
                Helps.addFieldHelp(trip, "#min-span", content, 'n');
                if (!$scope.missingSpan()) {
                    content = "Haga click en GUARDAR para hacer efectivo " +
                              "los cambios.";
                    Helps.addFieldHelp(trip, "#save-span", content, 'n');
                }
                trip.start();
            }
        }
    };

    /**
     *  Will show result pane if any of the following queries were executed
     */
    $scope.fetchedRequests = function() {
        return $scope.showResult == 1 ||
            $scope.showResult == 2 ||
            $scope.showResult == 3 ||
            $scope.showResult == 8 ||
            $scope.showResult == 9;
    };

    /**
     * onOpen & onClose select helpers which
     * 'fixes' selection usability problem (selection menu translation)
     */
    // TODO: Implement directive.
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

    $scope.onTypeOpen = function() {
        $scope.backup = $scope.model.perform[8].loanType;
        $scope.model.perform[8].loanType = null;
    };

    $scope.onTypeClose = function() {
        if ($scope.model.perform[8].loanType === null) {
            $scope.model.perform[8].loanType = $scope.backup;
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
        resetContent();
        $scope.showResult = -1;
        $scope.showOptions = true;
    };

    $scope.openMenu = function() {
       $mdSidenav('left').toggle();
    };

    $scope.getBulbColor = function(status, typeIndex, loanIndex) {
        // Selected loans won't have coloured bulbs ... looks ugly.
        if ($scope.selectedReq === typeIndex
            && $scope.selectedLoan === loanIndex) {
            return;
        }
        return {'color': $scope.pie.bulbColors[status]};
    };

    $scope.isObjEmpty = function(obj) {
        return Utils.isObjEmpty(obj);
    };

    $scope.showPie = function() {
        $scope.pieloaded = false;
        if (dataChanged) {
            updatePie();
            setDataChanged(false);
        } else {
            $scope.req = {};
            if ($scope.pieError == '') { $scope.pieloaded = true; }
        }
        // Un-select requests
        $scope.selectedReq = '';
        $scope.selectedLoan = -1;
    };

    function updatePie() {
        $scope.req = {};
        $scope.pieLoading = true;
        if ($scope.showResult == 0) {
            // update user's pie
            Manager.getUserRequests($scope.fetchId)
                .then(
                function (data) {
                    $scope.report = data.report;
                    $scope.pie = data.pie;
                    drawPie(data.pie);
                    $scope.pieLoading = false;
                    $scope.pieloaded = true;
                },
                function (error) {
                    $scope.pieLoading = false;
                    $scope.pieError = error;
                }
            );
        } else if ($scope.showResult == 1 &&
                $scope.model.perform[1].status == $scope.RECEIVED_STRING) {
            // update status pie
            Manager.fetchRequestsByStatus($scope.RECEIVED_STRING)
                .then(
                function (data) {
                    $scope.report = data.report;
                    $scope.pie = data.pie;
                    drawPie(data.pie);
                    $scope.pieLoading = false;
                    $scope.pieloaded = true;
                },
                function (error) {
                    $scope.pieLoading = false;
                    $scope.pieError = error;
                }
            );
        } else if ($scope.showResult == 2 || $scope.showResult == 3) {
            // update date interval pie
            var from, to;
            if ($scope.showResult == 2) {
                from = $scope.model.perform[2].from;
                to = $scope.model.perform[2].to;
            } else {
                from = $scope.model.perform[3].date;
                to = from;
            }
            Manager.fetchRequestsByDateInterval(from, to)
                .then(
                function (data) {
                    $scope.report = data.report;
                    $scope.pie = data.pie;
                    drawPie(data.pie);
                    $scope.pieLoading = false;
                    $scope.pieloaded = true;
                },
                function (error) {
                    $scope.pieLoading = false;
                    $scope.pieError = error;
                }
            );
        } else if ($scope.showResult == 8) {
            Manager.fetchRequestsByLoanType($scope.model.perform[8].loanType)
                .then(
                function (data) {
                    $scope.report = data.report;
                    $scope.pie = data.pie;
                    drawPie(data.pie);
                    $scope.pieLoading = false;
                    $scope.pieloaded = true;
                },
                function (error) {
                    $scope.pieLoading = false;
                    $scope.pieError = error;
                }
            );

        } else if ($scope.showResult == 9) {
            Manager.fetchPendingRequests()
                .then(
                function (data) {
                    $scope.report = data.report;
                    $scope.pie = data.pie;
                    drawPie(data.pie);
                    $scope.pieLoading = false;
                    $scope.pieloaded = true;
                },
                function (error) {
                    $scope.pieLoading = false;
                    $scope.pieError = error;
                }
            );
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
                responsive: false
            };
            $scope.chart = new Chart(ctx, {
                type: 'pie',
                data: data,
                options: options
            });
        }, 200);
    }

    $scope.showHelp = function() {
        if ($scope.pieloaded && Utils.isObjEmpty($scope.req)) {
            if ($scope.showResult == 0) {
                showSingleUserResultHelp(Helps.getDialogsHelpOpt());
            } else if ($scope.fetchedRequests()) {
                showMultipleUsersResultHelp(Helps.getDialogsHelpOpt());
            }
        } else if (Utils.isObjEmpty($scope.req)) {
            // User has not selected any request yet, tell him to do it.
            showSidenavHelp(Helps.getDialogsHelpOpt());
        } else {
            // Guide user through request selection's possible actions.
            showRequestHelp(Helps.getDialogsHelpOpt());
        }
    };

    /**
     * Shows tour-based help of single user result query
     * @param options: Obj containing tour.js options
     */
    function showSingleUserResultHelp(options) {
        options.showHeader = true;
        var trip = new Trip([], options);
        var content = "Esta tarjeta muestra las estadísticas de " +
                      "las solicitudes del afiliado. Los datos aparecen al " +
                      "mover el ratón hacia alguna de las divisiones de la gráfica.";
        Helps.addFieldHelpWithHeader(trip, '#piechart-tour', content, 'n', 'Estadísticas', true);
        content = "Puede generar un reporte detallado haciendo click aquí.";
        Helps.addFieldHelpWithHeader(trip, '#report-btn', content, 's', 'Generación de reporte', true, 'fadeInDown');
        if ($mdSidenav('left').isLockedOpen()) {
            // Nav. panel information
            content = "Consulte datos del afiliado";
            Helps.addFieldHelpWithHeader(trip, '#user-data', content, 'e', 'Datos del afiliado', false, 'fadeInLeft');
            content = "Ésta es la lista de solicitudes del afiliado. Haga clic en el tipo de solicitud de " +
                      "su elección para ver sus solicitudes de préstamo. <br/>Para facilitar " +
                      "la elección, el estatus de cada una está identificada por un bombillo amarillo, verde " +
                      "y rojo para Recibida, Aprobada y Rechazada, respectivamente.";
            Helps.addFieldHelpWithHeader(trip, '#result-data', content, 'e', 'Préstamos', false, 'fadeInRight');
            content = "Para hacer otro tipo de consulta, haga click aquí.";
            Helps.addFieldHelpWithHeader(trip, '#back-to-query', content, 'e', 'Atrás', false, 'fadeInRight');
        }
        trip.start();
    }

    /**
     * Shows tour-based help of multiple users result query
     * @param options: Obj containing tour.js options
     */
    function showMultipleUsersResultHelp(options) {
        options.showHeader = true;
        var trip = new Trip([], options);
        var content = "Esta tarjeta muestra las estadísticas de las " +
                      "solicitudes en cuestión. Los datos aparecen al mover" +
                      " el ratón hacia alguna de las divisiones de la gráfica.";
        Helps.addFieldHelpWithHeader(trip, '#piechart-tour', content, 'n', 'Estadísticas', true);
        content = "Puede generar un reporte detallado haciendo click aquí.";
        Helps.addFieldHelpWithHeader(trip, '#report-btn', content, 's', 'Generación de reporte', true, 'fadeInDown');
        if ($mdSidenav('left').isLockedOpen()) {
            // Nav. panel information
            if ($scope.showResult !== 1) {
                content = "Éstas son las solicitudes resultantes de la búsqueda. Haga click en el tipo de " +
                          "solicitud de su elección para ver las solicitudes de préstamo.<br/>Para facilitar " +
                          "la elección, el estatus de cada una está identificada por un bombillo " +
                          "amarillo, verde y rojo para Recibida, Aprobada y Rechazada, respectivamente.";
            } else {
                content = "Éstas son las solicitudes resultantes de la búsqueda. Haga click en el tipo de solicitud " +
                          "de su elección para ver las solicitudes de préstamo.";
            }
            Helps.addFieldHelpWithHeader(trip, '#result-data', content, 'e', 'Solicitudes', false, 'fadeInRight');
            content = "Para hacer otro tipo de consulta, haga click aquí.";
            Helps.addFieldHelpWithHeader(trip, '#back-to-query', content, 'e', 'Atrás', false, 'fadeInRight');
        }
        trip.start();
    }

    /**
     * Shows tour-based help of side navigation panel
     * @param options: Obj containing tour.js options
     */
    function showSidenavHelp(options) {
        var trip;
        var content;
        if ($mdSidenav('left').isLockedOpen()) {
            options.showHeader = true;
            trip = new Trip([], options);
            content = "Éstas son las listas de solicitudes por administrar. Haga click en el tipo de solicitud " +
                          "de su elección para ver las solicitudes de préstamo. Al seleccionar alguna, puede " +
                          "ver los detalles de la solicitud para administrarla.";
            Helps.addFieldHelpWithHeader(trip, '#pending-req', content, 'e', 'Solicitudes pendientes');
            content = "Puede realizar búsquedas más específicas de las solicitudes. Sólo seleccione" +
                      " el tipo de consulta e ingrese los datos solicitados.";
            Helps.addFieldHelpWithHeader(trip, '#adv-search', content, 'e', 'Búsqueda avanzada');
            content = "También puede generar reportes de solicitudes cerradas durante la " +
                      "semana vigente o en un rango de fechas específico.";
            Helps.addFieldHelpWithHeader(trip, '#approval-report', content, 'e', 'Reporte de solicitudes cerradas');
            content = "También puede realizar gestiones del sistema a través de las opciones correspondientes.";
            Helps.addFieldHelpWithHeader(trip, '#manager-options', content, 's', 'Administración');
            trip.start();
        } else {
            trip = new Trip([], options);
            content = "Haga click en el ícono para abrir el panel de navegación, donde podrá elegir las" +
                      " solicitudes a administrar o realizar búsquedas avanzadas.";
            Helps.addFieldHelp(trip, '#nav-panel', content, 's');
            content = "También hacer clic aquí para desplegar un menú, donde podrá " +
                      "realizar gestiones del sistema a través de las opciones correspondientes.";
            Helps.addFieldHelp(trip, '#manager-options-menu', content, 's');
            trip.start();
        }
    }

     /**
      * Shows tour-based help of selected request details section.
      * @param options: Obj containing tour.js options
      */
    function showRequestHelp(options) {
        options.showHeader = true;
         var trip = new Trip([], options);
         // Request summary information
         var content = "Aquí se muestra información acerca de la fecha de creación, monto " +
                       "solicitado, y un comentario de haberlo realizado.";
         Helps.addFieldHelpWithHeader(trip, '#request-summary', content, 's', 'Resumen de la solicitud', true);
         // Request status information
         content = "Esta sección provee información acerca del estatus de la solicitud.";
         Helps.addFieldHelpWithHeader(trip, '#request-status-summary', content, 's', 'Resumen de estatus', true,
                                      'fadeInDown');
         // Request payment due information
         content = "Acá puede apreciar las cuotas a pagar, indicando el monto por mes y el plazo del pago en meses.";
         Helps.addFieldHelpWithHeader(trip, '#request-payment-due', content, 's',
                                      'Cuotas a pagar', true);
         // Request contact number
         content = "Aquí se muestra el número de teléfono del solicitante.";
         Helps.addFieldHelpWithHeader(trip, '#request-contact-number', content, 'n',
                                      'Número de contacto', true);
         // Request contact email
         content = "Éste es el correo electrónico a través del cual el sistema enviará información y " +
                   "actualizaciones referente a la solicitud.";
         Helps.addFieldHelpWithHeader(trip, '#request-email', content, 'n',
                                      'Correo electrónico', true);
         // Request documents information
         content = "Éste y los siguientes " +
                   "items contienen el nombre y, de existir, una descripción " +
                   "de cada documento en la solicitud. Puede " +
                   "verlos/descargarlos haciendo click encima de ellos.";
         Helps.addFieldHelpWithHeader(trip, '#request-docs', content, 'n', 'Documentos', true, 'fadeInDown');

        if ($mdSidenav('left').isLockedOpen()) {
            content = "Puede ver los datos del creador de la solicitud, ver el historial de la solicitud, editarla " +
                      "(si la solicitud no se ha cerrado), o descargar todos sus documentos presionando " +
                      "el botón correspondiente.";
            Helps.addFieldHelpWithHeader(trip, '#request-summary-actions', content, 'w', 'Acciones', true,
                                         'fadeInLeft');
        } else {
            content = "Haga click en el botón de opciones para ver los datos del creador de la solicitud, " +
                      "ver el historial de la solicitud, editarla (si la solicitud no se ha cerrado), " +
                      "o descargar todos sus documentos.";
            Helps.addFieldHelpWithHeader(trip, '#request-summary-actions-menu', content, 's', 'Acciones', true,
                                         'fadeInLeft');
        }
        trip.start();
    }


    $scope.generateExcelReport = function() {
        $scope.loadingReport = true;
        Manager.generateExcelReport($scope.showResult, $scope.report)
            .then(
            function (downloadURL) {
                $scope.loadingReport = false;
                location.href = downloadURL;
            },
            function (error) {
                Utils.showAlertDialog('Oops!', error);
                $scope.loadingReport = false;
            }
        );
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
        $scope.requests = {};
        $scope.selectedReq = '';
        $scope.selectedLoan = -1;
        $scope.selectedPendingReq = '';
        $scope.selectedPendingLoan = -1;
        $scope.req = {};
        $scope.showList = Requests.initializeListType();
        $scope.showApprovedAmount = false;
        $scope.fetchError = '';
        $scope.approvalReportError = '';
        $scope.pieError = '';
        $scope.showApprovedAmount = false;
        $scope.pieloaded = false;
    }

    /**
     * Takes user to system config. state.
     */
    $scope.goToConfig = function() {
        $state.go('config');
    };
}

angular
    .module('sgdp')
    .controller('ManagerHomeController', managerHome);

managerHome.$inject = ['$scope', '$mdDialog', '$state', '$timeout', '$mdSidenav',
                       '$mdMedia', 'Utils', 'Requests', 'Constants', 'Manager', 'Config'];

function managerHome($scope, $mdDialog, $state, $timeout, $mdSidenav, $mdMedia,
                     Utils, Requests, Constants, Manager, Config) {
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

    $scope.return = function () {
        window.location.replace(Constants.IPAPEDI_URL + 'administracion/admin');
    };

    $scope.fetchRequestById = function(rid) {
        resetContent();
        $scope.loading = true;
        Manager.getRequestById(rid)
            .then(
            function (data) {
                $scope.selectRequest('', -1);
                $scope.req = data;
                $scope.loading = false;
            },
            function (error) {
                $scope.fetchError = error;
                $scope.loading = false;
            }
        );
    };

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
        return Config.getStatuses().then(
            function (statuses) {
                $scope.statuses = Requests.getAllStatuses();
                $scope.statuses = $scope.statuses.concat(statuses);
            }
        );
    };

    // Calculates the request's payment fee.
    $scope.calculatePaymentFee = function() {
        return $scope.req ? Requests.calculatePaymentFee($scope.req.reqAmount,
                                                         $scope.req.due,
                                                         Requests.getInterestRate($scope.req.loanType)) : 0;
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
        window.open(Requests.getDocDownloadUrl(doc.id));
    };

    $scope.downloadAll = function() {
        location.href = Requests.getAllDocsDownloadUrl($scope.req.docs);
    };

    $scope.downloadManual = function () {
        window.open(Constants.BASEURL + 'public/manualGerente.pdf');
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
                    return (($scope.model.status == request.status
                             || $scope.model.status == null) &&
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

            $scope.createNewAgent = function() {
                $scope.errorMsg = '';
                $scope.uploading = true;
                var postData = JSON.parse(JSON.stringify($scope.model));
                postData.id = $scope.userId;
                if ($scope.model.phone) {
                    postData.phone = Utils.pad($scope.model.phone, 11);
                }
                Manager.createNewAgent(postData)
                    .then(
                    function (created) {
                        if (created) {
                            $mdDialog.hide();
                            Utils.showAlertDialog('Operación exitosa',
                                                  'El nuevo usuario Agente ha sido registrado exitosamente');
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

            $scope.degradeUser = function() {
                $scope.errorMsg = '';
                $scope.uploading = true;
                Manager.deleteAgentUser($scope.selectedUser.value)
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

    $scope.onQueryClose = function() {
        if ($scope.model.query != $scope.selectedQuery) {
            $scope.fetchError = '';
        }
        $scope.model.query = $scope.selectedQuery;
        $scope.approvalReportError = '';
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
            $scope.req = null;
            if ($scope.pieError == '') { $scope.pieloaded = true; }
        }
        // Un-select requests
        $scope.selectedReq = '';
        $scope.selectedLoan = -1;
    };

    function updatePie() {
        $scope.req = null;
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
        $scope.requests = null;
        $scope.selectedReq = '';
        $scope.selectedLoan = -1;
        $scope.selectedPendingReq = '';
        $scope.selectedPendingLoan = -1;
        $scope.req = null;
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

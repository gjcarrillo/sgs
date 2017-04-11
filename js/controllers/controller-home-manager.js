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
    $scope.requests = Manager.data.requests;
    $scope.pendingRequests = Manager.data.pendingRequests;
    $scope.fetchError = Manager.data.fetchError;
    $scope.fetchId = Manager.data.fetchId;
    $scope.approvalReportError = Manager.data.approvalReportError ;
    $scope.pie = Manager.data.pie;
    $scope.pieError = Manager.data.pieError;
    $scope.showList = Manager.data.showList;
    $scope.showPendingList = Manager.data.showPendingList;
    $scope.showPendingReq = Manager.data.showPendingReq;
    $scope.showAdvSearch = Manager.data.showAdvSearch;
    $scope.selectedList = Manager.data.selectedList;
    $scope.selectedAction = Manager.data.selectedAction;
    $scope.loanTypes = Manager.data.loanTypes;
    $scope.contentAvailable = Manager.data.contentAvailable;
    $scope.contentLoaded = Manager.data.contentLoaded;
    $scope.idPrefix = Manager.data.idPrefix;

    $scope.selected = [];

    $scope.query = {
        order: 'name',
        limit: 5,
        page: 1
    };

    $scope.statuses = Requests.getAllStatuses();
    $scope.APPROVED_STRING = Constants.Statuses.APPROVED;
    $scope.REJECTED_STRING = Constants.Statuses.REJECTED;
    $scope.RECEIVED_STRING = Constants.Statuses.RECEIVED;
    $scope.PRE_APPROVED_STRING = Constants.Statuses.PRE_APPROVED;
    //$scope.listTitle = Requests.getRequestsListTitle();
    $scope.mappedStatuses = Requests.getAllStatuses();

    $scope.loadingContent = false;
    $scope.loading = false;
    $scope.loadingReport = false;

    $scope.selectAction = function (id) {
        $scope.requests = {};
        $scope.singleType = {};
        $scope.showApprovedAmount = false;
        $scope.selectedAction = id;
        performAction(id);
    };

    function performAction (action) {
        switch (action) {
            case 'pending': {
                // Editable requests
                loadPendingRequests();
                break;
            }
        }
    }
    /**
     * Helper function that loads pending requests.
     */
    function loadPendingRequests() {
        $scope.loadingContent = true;
        Manager.loadPendingRequests().then(
            function (requests) {
                $scope.requests = requests;
                $scope.loadingContent = false;
            }, function (error) {
                Utils.showAlertDialog('Oops!', error);
                $scope.loadingContent = false;
            });
    }

    function reloadAdvQuery(action, input) {
        switch (action) {
            case 0:
                $scope.fetchUserRequests(action);
                break;
            case 1:
                $scope.fetchRequestsByStatus(input.status, action);
                break;
            case 2:
                $scope.fetchRequestsByDateInterval(input.from, input.to, action);
                break;
            case 8:
                $scope.fetchRequestsByLoanType(input.loanType, input);
                break;
            case 9:
                $scope.fetchPendingRequests(action);
                break;
            case 10:
                break;
        }
    }

    $scope.togglePanelList = function (index) {
        $scope.selectedList = $scope.selectedList == index ? null : index;
    };

    $scope.return = function () {
        window.location.replace(Constants.IPAPEDI_URL + 'administracion/admin');
    };

    $scope.fetchRequestById = function() {
        resetContent();
        $scope.loading = true;
        var rid = $scope.model.perform[$scope.selectedAction].id;
        Manager.getRequestById(rid)
            .then(
            function (req) {
                $scope.goToDetails(req);
                $scope.loading = false;
            },
            function (error) {
                Utils.showAlertDialog('Mensaje', error);
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
                if ($scope.showResult == null) {
                    // It means it fired query from PANEL.
                    // Otherwise query was fired from coming back from details or actions view...
                    // So show requests list instead.
                    $scope.pieloaded = true;
                }
                $scope.showResult = index;
                $scope.report = data.report;
                $scope.pie = data.pie;
                drawPie(data.pie);
                $scope.loading = false;
            },
            function (error) {
                Utils.showAlertDialog('Mensaje', error);
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
                if ($scope.showResult == null) {
                    // It means it fired query from PANEL.
                    // Otherwise query was fired from coming back from details or actions view...
                    // So show requests list instead.
                    $scope.pieloaded = true;
                }
                $scope.showResult = index;
                $scope.pie = data.pie;
                drawPie(data.pie);
                $scope.report = data.report;
                $scope.report.status = status;
                $scope.loading = false;
            },
            function (error) {
                Utils.showAlertDialog('Mensaje', error);
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
                $scope.singleType = data.requests;
                $scope.showOptions = false;
                if ($scope.showResult == null) {
                    // It means it fired query from PANEL.
                    // Otherwise query was fired from coming back from details or actions view...
                    // So show requests list instead.
                    $scope.pieloaded = true;
                }
                $scope.showResult = index;
                $scope.pie = data.pie;
                drawPie(data.pie);
                $scope.report = data.report;
                $scope.loading = false;
            },
            function (error) {
                Utils.showAlertDialog('Mensaje', error);
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
                if ($scope.showResult == null) {
                    // It means it fired query from PANEL.
                    // Otherwise query was fired from coming back from details or actions view...
                    // So show requests list instead.
                    $scope.pieloaded = true;
                }
                $scope.showResult = index;
                $scope.pie = data.pie;
                drawPie(data.pie);
                $scope.report = data.report;
                $scope.loading = false;
            },
            function (error) {
                Utils.showAlertDialog('Mensaje', error);
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
                if ($scope.showResult == null) {
                    // It means it fired query from PANEL.
                    // Otherwise query was fired from coming back from details or actions view...
                    // So show requests list instead.
                    $scope.pieloaded = true;
                }
                $scope.showResult = index;
                $scope.pie = data.pie;
                drawPie(data.pie);
                $scope.report = data.report;
                $scope.loading = false;
            },
            function (error) {
                Utils.showAlertDialog('Mensaje', error);
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
                Utils.showAlertDialog('Mensaje', error);
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
                Utils.showAlertDialog('Mensaje', error);
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
                Utils.showAlertDialog('Mensaje', error);
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
                Utils.showAlertDialog('Mensaje', error);
                $scope.loadingReport = false;
            }
        );
    };

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

    $scope.loadUserData = function(userId) {
        sessionStorage.setItem("fetchId", userId);
        window.open(Utils.getUserDataUrl(), '_blank');
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
                                                         Requests.getInterestRate($scope.req.type)) : 0;
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
        data.selectedList = $scope.selectedList;
        data.selectedAction = $scope.selectedAction;
        data.loanTypes = $scope.loanTypes;
        data.contentAvailable = $scope.contentAvailable;
        data.contentLoaded = $scope.contentLoaded;
        data.fetchId = $scope.fetchId;
        data.idPrefix = $scope.idPrefix;

        Manager.updateData(data);
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
            $scope.selectedQuery = null;
            /**
             * =================================================
             *          Requests status configuration
             * =================================================
             */
            $scope.statuses = {};
            $scope.statuses.systemStatuses = Requests.getAllStatuses();
            $scope.statuses.newStatuses = $scope.statuses.existing = [];

            function loadStatusesForConfig () {
                $scope.statuses.errorMsg = '';
                $scope.statuses.loading = true;

                Config.getStatusesForConfig()
                    .then(
                    function (statuses) {
                        $scope.statuses.loading = false;
                        $scope.statuses.newStatuses = statuses.existing;
                        // Create a DEEP copy of the existing statuses array.
                        $scope.statuses.existing = _.cloneDeep(statuses.existing);
                        $scope.statuses.inUse = statuses.inUse;
                    },
                    function (err) {
                        $scope.statuses.errorMsg = err;
                        $scope.statuses.loading = false;
                    }
                );
            }

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

            function loadMaxAndMinAmount() {
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
            }

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
             *         Requests frequency configuration
             * =================================================
             */

            $scope.missingSpan = function() {
                if ($scope.selectedQuery) {
                    // Look for those span that are null or edited.
                    var edited = _.pickBy($scope.loanTypes, function(loanType, concept){
                        return loanType.span != $scope.existing[concept].span;
                    });
                    var nulled = _.pickBy($scope.loanTypes, function(loanType){
                        return loanType.span == null;
                    });
                    // If edit obj is empty, no span value has been edited.
                    // if there is any null span, its also a missing field.
                    return _.isEmpty(edited) || !_.isEmpty(nulled);
                } else {
                    return true;
                }
            };

            function loadReqFrequencies () {
                $scope.selectedQuery = null;
                $scope.loanTypes = $scope.existing = {};
                $scope.span = {errorMsg: '', loading: true};
                Config.getRequestsSpan()
                    .then(
                    function (spans) {
                        $scope.loanTypes = spans;
                        $scope.span.loading = false;
                        $scope.existing = _.cloneDeep(spans);
                    },
                    function (err) {
                        $scope.span.errorMsg = err;
                        $scope.span.loading = false;
                    }
                );
            }

            $scope.updateRequestsSpan = function () {
                $scope.uploading = true;
                Config.updateRequestsSpan($scope.loanTypes)
                    .then(
                    function () {
                        $scope.uploading = false;
                        Utils.showAlertDialog('Actualización exitosa',
                                              'El tiempo a esperar para realizar diferentes solicitudes ' +
                                              'del mismo tipo ha sido actualizado.');
                    },
                    function (err) {
                        console.log(err);
                        $scope.span.errorMsg = err;
                        $scope.uploading = false;
                    }
                );
            };

            /**
             * =================================================
             *         Requests terms configuration
             * =================================================
             */

            $scope.missingTerms = function() {
                if ($scope.selectedQuery) {
                    // Look for those terms that are edited.
                    var edited = _.pickBy($scope.loanTypes, function(loanType, concept){
                        return !Utils.isArrayEqualsTo(loanType.terms, $scope.existing[concept].terms);
                    });
                    // If edit obj is empty, no terms have been edited.
                    return _.isEmpty(edited);
                } else {
                    return true;
                }
            };

            $scope.checkTerm = function (concept) {
                if ($scope.loanTypes[concept].terms[$scope.loanTypes[concept].terms.length - 1] >=
                    $scope.loanTypes[concept].PlazoEnMeses ||
                    $scope.loanTypes[concept].terms[$scope.loanTypes[concept].terms.length - 1] < 1) {
                    // Remove just-added-term if not qualified.
                    $scope.loanTypes[concept].terms.splice($scope.loanTypes[concept].terms.length - 1, 1);
                }
            };

            function loadReqTerms () {
                $scope.terms = {errorMsg: '', loading: true};
                $scope.selectedQuery = null;
                $scope.loanTypes = $scope.existing = {};
                Config.getRequestsTerms()
                    .then(
                    function (terms) {
                        $scope.loanTypes = terms;
                        $scope.terms.loading = false;
                        $scope.existing = _.cloneDeep(terms);
                    },
                    function (err) {
                        $scope.terms.errorMsg = err;
                        $scope.terms.loading = false;
                    }
                );
            }

            $scope.updateRequestsTerms = function () {
                $scope.uploading = true;
                Config.updateRequestsTerms($scope.loanTypes)
                    .then(
                    function () {
                        $scope.uploading = false;
                        Utils.showAlertDialog(
                            'Actualización exitosa',
                            'Los plazos de pagos disponibles han sido actualizados exitosamente.'
                        );
                    },
                    function (err) {
                        console.log(err);
                        $scope.terms.errorMsg = err;
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

            $scope.selectTab = function(tab) {
                $scope.selectedTab = tab;
                switch (tab) {
                    case 1:
                        loadStatusesForConfig();
                        break;
                    case 2:
                        loadMaxAndMinAmount();
                        break;
                    case 3:
                        loadReqFrequencies();
                        break;
                    case 4:
                        loadReqTerms();
                }
            };
        }
    };

    /**
     * Goes back to query selection options
     */
    $scope.goBack = function() {
        resetContent();
        $scope.showResult = null;
        $scope.showOptions = true;
    };

    $scope.openMenu = function() {
       $mdSidenav('left').toggle();
    };

    $scope.isObjEmpty = function(obj) {
        return Utils.isObjEmpty(obj);
    };

    $scope.showWatermark = function() {
        return $scope.isObjEmpty(requests) &&
               !$scope.loadingContent &&
               !$scope.showApprovedAmount &&
               !$scope.pieLoading &&
               !$scope.pieloaded &&
               $scope.pieError == ''
    };

    $scope.showPie = function() {
        if ($scope.pieError == '') { $scope.pieloaded = true; }
    };

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
     * Helper function that resets UI for all query results
     */
    function resetContent() {
        $scope.requests = {};
        $scope.selectedReq = '';
        $scope.selectedLoan = -1;
        $scope.selectedPendingReq = '';
        $scope.selectedPendingLoan = -1;
        $scope.showApprovedAmount = false;
        $scope.fetchError = '';
        $scope.approvalReportError = '';
        $scope.pieError = '';
        $scope.pie = null;
        $scope.pieloaded = false;
    }

    $scope.showRequestList = function () {
        $scope.pieloaded = false;
        $scope.pieError = '';
        // Close sidenav
        $mdSidenav('left').toggle();
    };

    // Get configured loan types.
    if (!$scope.loanTypes) {
        $scope.loadingContent = true;
        Requests.initializeListType().then(
            function (list) {
                $scope.loanTypes = list;
                $scope.contentAvailable = true;
                $timeout(function () {
                    $scope.contentLoaded = true;
                    // Fetch pending requests and automatically show first one to user (if any)
                    $scope.selectAction('pending');
                }, 600);
            },
            function (error) {
                Utils.showAlertDialog('Oops!', 'Ha ocurrido un error en el sistema.<br/>' +
                                               'Por favor intente más tarde.');
                console.log(error);
            }
        );
    } else {
        $scope.selectAction($scope.selectedAction);
        if ($scope.showResult != null) {
            reloadAdvQuery($scope.selectedAction, $scope.model.perform[$scope.selectedAction]);
        }
    }
}

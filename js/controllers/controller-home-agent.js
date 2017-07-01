angular
    .module('sgdp')
    .controller('AgentHomeController', agentHome);

agentHome.$inject = ['$scope', '$mdDialog', 'Constants', 'Agent', 'Config', 'Applicant', '$window',
                     '$state', '$timeout', '$mdSidenav', '$mdMedia', 'Requests', 'Utils'];

function agentHome($scope, $mdDialog, Constants, Agent, Config, Applicant, $window,
                   $state, $timeout, $mdSidenav, $mdMedia, Requests, Utils) {
    'use strict';
    $scope.requests = Applicant.data.requests;
    $scope.singleType = Applicant.data.singleType;
    $scope.loanTypes = Applicant.data.loanTypes;
    $scope.newRequestList = Applicant.data.newRequestList;
    $scope.selectedList = Applicant.data.selectedList;
    $scope.fetchError = Applicant.data.fetchError;
    // contentAvailable will indicate whether sidenav can be visible
    $scope.contentAvailable = Applicant.data.contentAvailable;
    // contentLoaded will indicate whether sidenav can be locked open
    $scope.contentLoaded = Applicant.data.contentLoaded;
    $scope.selectedAction = Applicant.data.selectedAction;
    $scope.queryList = Applicant.data.queryList;
    $scope.queries = Applicant.data.queries;
    $scope.showMsg = true;
    // This will enable / disable search bar in mobile screens
    $scope.searchEnabled = Agent.data.searchEnabled;
    $scope.idPrefix = Agent.data.idPrefix;
    $scope.fetchId = Agent.data.fetchId;
    $scope.searchInput = Agent.data.searchInput;
    $scope.IPAPEDI_URL = Constants.IPAPEDI_URL;

    $scope.selectAction = function (id) {
        $mdSidenav('left').close();
        $scope.requests = {};
        $scope.singleType = [];
        $scope.editableReq = [];
        $scope.activeRequests = [];
        $scope.selectedAction = id;
        performAction(id);
    };

    $scope.selected = [];

    $scope.query = {
        order: 'name',
        limit: 5,
        page: 1
    };

    $scope.goBack = function () {
        $window.history.go(-1);
    };

    // Get configured loan types.
    if (!$scope.loanTypes) {
        $scope.loading = true;
        Requests.initializeListType().then(
            function (list) {
                $scope.loanTypes = list;
                $scope.loading = false;
            },
            function (error) {
                $scope.loading = false;
                Utils.handleError(error);

            }
        );
    }
    // Resume view state.
    if ($scope.fetchId) {
        $scope.selectAction($scope.selectedAction);
    }

    $scope.fetchUser = function (searchInput) {
        $scope.selectedAction = null;
        $scope.requests = {};
        $scope.searchInput = searchInput;
        $scope.fetchId = $scope.idPrefix + searchInput;
        $scope.loading = true;
        Agent.validateUser($scope.fetchId).then(
            function () {
                $scope.contentAvailable = true;
                $scope.loading = false;
                $timeout(function () {
                    $scope.contentLoaded = true;
                    if ($mdMedia('xs')) {
                        $mdSidenav('left').open();
                    }
                }, 600);
            },
            function (error) {
                $scope.loading = false;
                Utils.handleError(error);
            }
        );
    };

    $scope.togglePanelList = function (index) {
        $scope.selectedList = $scope.selectedList == index ? null : index;
    };

    function performAction (action) {
        switch (action) {
            case 1:
                // All requests
                getAllRequests();
                break;
            case 6:
                // Opened requests
                getOpenedRequests();
                break;
            case 10:
                // Active requests
                getActiveRequests();
                break;
            case 'edit': {
                // Editable requests
                editRequests();
                break;
            }
        }
    }

    function getActiveRequests() {
        $scope.showMsg = true;
        $scope.requests = {};
        $scope.activeRequests = [];
        $scope.fetching = true;
        // Fetch user's requests
        Requests.getActiveRequests($scope.fetchId).then(
            function (requests) {
                $scope.fetching = false;
                $scope.activeRequests = requests;
            },
            function (errorMsg) {
                $scope.fetching = false;
                Utils.handleError(errorMsg);
            }
        );
    }

    function getAllRequests () {
        $scope.requests = {};
        $scope.fetching = true;
        // Fetch user's requests
        Requests.getUserRequests($scope.fetchId).then(
            function (data) {
                $scope.fetching = false;
                $scope.requests = data;
            },
            function (error) {
                Utils.handleError(error);
                $scope.fetching = false;
            }
        );
    }

    $scope.getRequestById = function (rid) {
        $scope.requests = {};
        $scope.fetching = true;
        Requests.getRequestById(rid, $scope.fetchId).then(
            function (request) {
                goToDetails(request);
            },
            function (error) {
                $scope.fetching = false;
                Utils.handleError(error);
            }
        );
    };

    $scope.getRequestsByStatus = function (status) {
        $scope.requests = {};
        $scope.fetching = true;
        Requests.getRequestsByStatus(status, $scope.fetchId).then(
            function (requests) {
                $scope.requests = requests;
                $scope.fetching = false;
            },
            function (error) {
                $scope.fetching = false;
                Utils.handleError(error);
            }
        );
    };

    $scope.getRequestsByDate = function (from, to) {
        $scope.requests = {};
        $scope.fetching = true;
        Requests.getRequestsByDate(from, to, $scope.fetchId).then(
            function (requests) {
                $scope.requests = requests;
                $scope.fetching = false;
            },
            function (error) {
                $scope.fetching = false;
                Utils.handleError(error);
            }
        );
    };

    $scope.getRequestsByType = function (concept) {
        $scope.requests = {};
        $scope.singleType = [];
        $scope.fetching = true;
        Requests.getRequestsByType(concept, $scope.fetchId).then(
            function (requests) {
                $scope.singleType = requests;
                $scope.fetching = false;
            },
            function (error) {
                $scope.fetching = false;
                Utils.handleError(error);
            }
        );
    };

    function getOpenedRequests () {
        $scope.showMsg = true;
        $scope.requests = {};
        $scope.fetching = true;
        Requests.getOpenedRequests($scope.fetchId).then(
            function (requests) {
                $scope.requests = requests;
                $scope.fetching = false;
            },
            function (error) {
                $scope.fetching = false;
                Utils.handleError(error);
            }
        );
    }

    function editRequests () {
        $scope.requests = {};
        $scope.editableReq = [];
        $scope.fetching = true;
        Requests.getUserEditableRequests($scope.fetchId).then(
            function (data) {
                $scope.fetching = false;
                $scope.editableReq = data;
            },
            function (errorMsg) {
                Utils.handleError(errorMsg);
                $scope.fetching = false;
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
        sessionStorage.setItem("uid", $scope.fetchId);
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

    $scope.showWatermark = function () {
        return !$scope.loading && !$scope.fetching && $scope.fetchError == '' &&
               $scope.selectedAction != 1 && $scope.selectedAction != 2 && $scope.selectedAction != 3 &&
               $scope.selectedAction != 4 && $scope.selectedAction != 5 && $scope.selectedAction != 6 &&
               $scope.selectedAction != 10 && $scope.selectedAction != 'edit';
    };

    $scope.loadStatuses = function() {
        return Config.getStatuses().then(
            function (statuses) {
                $scope.statuses = Requests.getAllStatuses();
                $scope.statuses = $scope.statuses.concat(statuses);
            }
        );
    };

    /**
     * Opens the New Request dialog and performs the corresponding operations.
     *
     * @param $event - DOM event.
     * @param concept - new request's concept.
     * @param obj - optional obj containing user input data.
     */
    $scope.openNewRequestDialog = function ($event, concept, obj) {
        $mdSidenav('left').close();
        $scope.selectedAction = 'N' + concept;
        $scope.requests = {};
        var parentEl = angular.element(document.body);
        $mdDialog.show({
            parent: parentEl,
            targetEvent: $event,
            templateUrl: Requests.getNewRequestDialog(concept),
            clickOutsideToClose: false,
            escapeToClose: false,
            autoWrap: false,
            fullscreen: $mdMedia('xs'),
            locals: {
                fetchId: $scope.fetchId,
                obj: obj,
                parentScope: $scope
            },
            controller: DialogController
        });
        // Isolated dialog controller for the new request dialog
        function DialogController($scope, $mdDialog, fetchId, parentScope, obj) {
            $scope.docPicTaken = false;
            $scope.uploading = false;
            // if user data exists, it means the ID was
            // already given, so we must show it.
            $scope.uploadErr = '';
            // Hold scope reference to constants
            $scope.APPLICANT = Constants.Users.APPLICANT;
            $scope.AGENT = Constants.Users.AGENT;
            $scope.LoanTypes = Constants.LoanTypes;

            // obj could have a reference to user data, saved
            // before confirmation dialog was opened.
            $scope.model = obj || {};
            $scope.model.type = concept;
            $scope.confirmButton = 'Crear';
            $scope.title = 'Nueva solicitud de préstamo';

            // if user came back to this dialog after confirming operation..
            if ($scope.model.confirmed) {
                // Go ahead and proceed with creation
                createNewRequest();
            } else {
                checkCreationConditions();
            }

            // Checks whether conditions for creating new requests are fulfilled.
            function checkCreationConditions () {
                $scope.loading = true;
                Requests.getAvailabilityData(fetchId, concept).then(
                    function (data) {
                        $scope.model.data = data;
                        Requests.checkPreviousRequests(fetchId, concept).then(
                            function (opened) {
                                data.opened = opened;
                                Requests.getLoanTerms(concept).then(
                                    function (terms) {
                                        $scope.model.maxReqAmount = Requests.getMaxAmount();
                                        $scope.model.terms = terms;
                                        $scope.model.due = terms[0];
                                        if (!$scope.model.phone) {
                                            $scope.model.phone = data.userPhone ? Utils.pad(parseInt(data.userPhone, 10), 11) : '';
                                        }
                                        if (!$scope.model.email) {
                                            $scope.model.email = data.userEmail;
                                        }
                                        Requests.verifyAvailability(data, concept, false);
                                        $scope.loading = false;
                                    },
                                    function (error) {
                                        Utils.handleError(error);
                                    }
                                );
                            },
                            function (error) {
                                Utils.handleError(error);
                            }
                        );
                    },
                    function (error) {
                        Utils.handleError(error);
                    }
                );
            }

            $scope.missingField = function () {
                return typeof $scope.model.reqAmount === "undefined" ||
                       typeof $scope.model.type === "undefined" ||
                       !$scope.model.due ||
                       !$scope.model.phone ||
                       !$scope.model.email;
            };

            $scope.loadAdditionalDeductions = function () {
                if ($scope.model.deduct) {
                    $scope.loadingDeductions = true;
                    Requests.loadAdditionalDeductions(fetchId, null, $scope.model.type).then(
                        function (deductions) {
                            $scope.loadingDeductions = false;
                            $scope.model.deductions = deductions;
                        },
                        function (error) {
                            $scope.loadingDeductions = false;
                            Utils.handleError(error);
                        }
                    );
                } else {
                    $scope.model.deductions = null;
                }
            };

            $scope.calculateMedicalDebtContribution = function () {
                return Requests.calculateMedicalDebtContribution($scope.model.reqAmount, $scope.model.data);
            };

            $scope.calculateNewInterest = function () {
                return Requests.calculateNewInterest($scope.model.reqAmount, $scope.model.data);
            };

            $scope.calculateLoanAmount = function () {
                return Requests.calculateLoanAmount($scope.model.reqAmount, $scope.model.data);
            };

            $scope.calculateTotals = function (subTotal) {
                return Requests.calculateTotals($scope.model.type, $scope.model.reqAmount, subTotal, $scope.model.data,
                                                $scope.model.deductions);
            };

            $scope.calculateOtherDebtsContribution = function () {
                return Requests.calculateOtherDebtsContribution($scope.model.deductions);
            };

            $scope.calculatePaymentFee = function() {
                if ($scope.model.reqAmount && $scope.model.due) {
                    return Requests.calculatePaymentFee($scope.model.reqAmount,
                                                        $scope.model.due,
                                                        $scope.model.type);
                } else {
                    return 0;
                }
            };

            $scope.getInterestRate = function () {
                return Requests.getInterestRate($scope.model.type);
            };

            $scope.closeDialog = function () {
                $mdDialog.hide();
            };

            // Creates new request in database.
            function createNewRequest() {
                $scope.uploading = true;
                var docs = [];

                docs.push(Requests.createRequestDocData(fetchId));
                performCreation(docs);
            }

            // Helper function that performs the document's creation.
            function performCreation(docs) {
                var postData = {
                    userId: fetchId,
                    reqAmount: $scope.model.reqAmount,
                    tel: Utils.pad($scope.model.phone, 11),
                    due: $scope.model.due,
                    loanType: parseInt($scope.model.type, 10),
                    email: $scope.model.email,
                    docs: docs
                };
                postData.deductions = $scope.model.deduct ? $scope.model.deductions : null;
                Requests.createRequest(postData).then(
                    function(request) {
                        Utils.showAlertDialog(
                            'Solicitud creada',
                            'Por favor verifique los detalles de su solicitud.<br/>' +
                            'Una vez esté completamente seguro de proceder con esta solicitud, ' +
                            'realice la correspondiente validación.')
                        ;
                        // Go to details
                        goToDetails(request);
                    },
                    function(error) {
                        $scope.uploading = false;
                        Utils.handleError(error);
                    }
                );
            }

            // Sets the bound input to the max possibe request amount
            $scope.setMax = function() {
                $scope.model.reqAmount = $scope.model.maxReqAmount;
            };

            $scope.isObjEmpty = function (obj) {
                return Utils.isObjEmpty(obj);
            };

            // Shows a dialog asking user to confirm the request creation.
            $scope.confirmOperation = function (ev) {
                Utils.showConfirmDialog(
                    'Confirmación de creación de solicitud',
                    'El sistema generará el documento correspondiente a su solicitud. ¿Desea proceder?',
                    'Sí', 'Cancelar', ev, true
                ).then(
                    function() {
                        // Re-open parent dialog and perform request creation
                        $scope.model.confirmed = true;
                        parentScope.openNewRequestDialog(null, concept, $scope.model);
                    },
                    function() {
                        // Re-open parent dialog and do nothing
                        parentScope.openNewRequestDialog(null, concept, $scope.model);
                    }
                );
            };
        }
    };

    /**
     * Opens the edition request dialog and performs the corresponding operations.
     *
     * @param $event - DOM event.
     * @param request - request object.
     * @param obj - optional obj containing user input data.
     */
    $scope.openEditRequestDialog = function ($event, request, obj) {
        var parentEl = angular.element(document.body);
        $mdDialog.show({
            parent: parentEl,
            targetEvent: $event,
            templateUrl: Requests.getNewRequestDialog(request.type),
            clickOutsideToClose: false,
            escapeToClose: false,
            autoWrap: false,
            fullscreen: $mdMedia('xs'),
            locals: {
                fetchId: $scope.fetchId,
                request: request,
                obj: obj,
                parentScope: $scope
            },
            controller: DialogController
        });
        // Isolated dialog controller for the new request dialog
        function DialogController($scope, $mdDialog, fetchId, request, parentScope, obj) {
            $scope.docPicTaken = false;
            $scope.uploading = false;
            $scope.uploadErr = '';
            // Hold scope reference to constants
            $scope.APPLICANT = Constants.Users.APPLICANT;
            $scope.AGENT = Constants.Users.AGENT;
            $scope.LoanTypes = Constants.LoanTypes;
            var originalDeductions;

            // obj could have a reference to user data, saved
            // before confirmation dialog was opened.
            var model = {
                reqAmount: request.reqAmount,
                type: parseInt(request.type, 10),
                due: request.due,
                phone: Utils.pad(request.phone, 11),
                email: request.email,
                deductions: null
            };
            $scope.model = obj || model;
            $scope.model.loanTypes = Config.loanConcepts;
            $scope.confirmButton = 'Editar';
            $scope.title = 'Edición de solicitud';

            // if user came back to this dialog after confirming operation..
            if ($scope.model.confirmed) {
                // Go ahead and proceed with edition
                editRequest();
            } else {
                checkCreationConditions();
            }

            function checkCreationConditions () {
                $scope.loading = true;
                Requests.getAvailabilityData(fetchId, model.type).then(
                    function (data) {
                        $scope.model.data = data;
                        Requests.checkPreviousRequests(fetchId, model.type).then(
                            function (opened) {
                                data.opened = opened;
                                Requests.getLoanTerms(model.type).then(
                                    function (terms) {
                                        $scope.model.maxReqAmount = Requests.getMaxAmount();
                                        $scope.model.terms = terms;
                                        Requests.verifyAvailability(data, model.type, true);
                                        if (request.deductions) {
                                            $scope.model.deduct = true;
                                            Requests.loadAdditionalDeductions(fetchId, request.id, request.type).then(
                                                function (deductions) {
                                                    $scope.loading = false;
                                                    $scope.model.deductions = deductions;
                                                    // Create a copy to see if user edits them.
                                                    originalDeductions = _.cloneDeep(deductions);
                                                },
                                                function (error) {
                                                    $scope.loading = false;
                                                    Utils.handleError(error);
                                                }
                                            );
                                        } else {
                                            $scope.loading = false;
                                        }
                                    },
                                    function (error) {
                                        Utils.handleError(error);
                                    }
                                );
                            },
                            function (error) {
                                Utils.handleError(error);
                            }
                        );
                    },
                    function (error) {
                        Utils.handleError(error);
                    }
                );
            }

            $scope.missingField = function () {
                return (typeof $scope.model.reqAmount === "undefined"
                       || typeof $scope.model.phone === "undefined"
                       || typeof $scope.model.email === "undefined"
                       || !$scope.model.due)
                       || ($scope.model.reqAmount == request.reqAmount &&
                           Utils.pad($scope.model.phone, 11) == request.phone &&
                           $scope.model.email == request.email &&
                           $scope.model.due == request.due &&
                           _.isEqual($scope.model.deductions, originalDeductions));
            };

            $scope.closeDialog = function () {
                $mdDialog.hide();
            };

            $scope.loadAdditionalDeductions = function () {
                if ($scope.model.deduct) {
                    $scope.loadingDeductions = true;
                    Requests.loadAdditionalDeductions(fetchId, request.id, request.type).then(
                        function (deductions) {
                            $scope.loadingDeductions = false;
                            $scope.model.deductions = deductions;
                            // Create a copy to see if user edits them.
                            originalDeductions = _.cloneDeep(deductions);
                        },
                        function (error) {
                            $scope.loadingDeductions = false;
                            Utils.handleError(error);
                        }
                    );
                } else {
                    if (!request.deductions) {
                        originalDeductions = null;
                    }
                    $scope.model.deductions = null;
                }
            };

            $scope.calculateMedicalDebtContribution = function () {
                return Requests.calculateMedicalDebtContribution($scope.model.reqAmount, $scope.model.data);
            };

            $scope.calculateNewInterest = function () {
                return Requests.calculateNewInterest($scope.model.reqAmount, $scope.model.data);
            };

            $scope.calculateLoanAmount = function () {
                return Requests.calculateLoanAmount($scope.model.reqAmount, $scope.model.data);
            };

            $scope.calculateTotals = function (subTotal) {
                return Requests.calculateTotals($scope.model.type, $scope.model.reqAmount, subTotal, $scope.model.data,
                                                $scope.model.deductions);
            };

            $scope.calculateOtherDebtsContribution = function () {
                return Requests.calculateOtherDebtsContribution($scope.model.deductions);
            };

            $scope.calculatePaymentFee = function() {
                if ($scope.model.reqAmount && $scope.model.due) {
                    return Requests.calculatePaymentFee($scope.model.reqAmount,
                                                        $scope.model.due,
                                                        $scope.model.type);
                } else {
                    return 0;
                }
            };

            $scope.getInterestRate = function () {
                return Requests.getInterestRate($scope.model.type);
            };

            // Edits request in database.
            function editRequest() {
                $scope.uploading = true;
                performEdition();
            }

            // Helper function that performs request edition
            function performEdition() {
                var postData = {
                    rid: request.id,
                    userId: fetchId,
                    reqAmount: $scope.model.reqAmount,
                    tel: Utils.pad($scope.model.phone, 11),
                    due: $scope.model.due,
                    loanType: parseInt($scope.model.type, 10),
                    email: $scope.model.email
                };
                postData.deductions = $scope.model.deduct ? $scope.model.deductions : null;
                Requests.editRequest(postData).then(
                    function(request) {
                        Utils.showAlertDialog(
                            'Solicitud editada',
                            'La información de su solicitud ha sido editada exitosamente'
                        );
                        // Save controller state before navigating away.
                        goToDetails(request);
                    },
                    function(error) {
                        $scope.uploading = false;
                        Utils.handleError(error);
                    }
                );
            }

            // Sets the bound input to the max possibe request amount
            $scope.setMax = function() {
                $scope.model.reqAmount = $scope.model.maxReqAmount;
            };

            $scope.isObjEmpty = function (obj) {
                return Utils.isObjEmpty(obj);
            };

            // Shows a dialog asking user to confirm the request creation.
            $scope.confirmOperation = function (ev) {
                Utils.showConfirmDialog(
                    'Confirmación de edición de solicitud',
                    'Se guardarán los cambios que hayan realizado a su solicitud. ¿Desea proceder?',
                    'Sí', 'Cancelar', ev, true
                ).then(
                    function() {
                        // Re-open parent dialog and perform request creation
                        $scope.model.confirmed = true;
                        parentScope.openEditRequestDialog(null, request, $scope.model);
                    },
                    function() {
                        // Re-open parent dialog and do nothing
                        parentScope.openEditRequestDialog(null, request, $scope.model);
                    }
                );
            };
        }
    };

    $scope.deleteRequest = function (ev, req) {
        Utils.showConfirmDialog(
            'Confirmación de eliminación',
            'Al eliminar la solicitud, también se eliminarán ' +
            'todos los datos asociados a ella.',
            'Continuar',
            'Cancelar',
            ev, true).then(
            function() {
                $scope.overlay = true;
                Requests.deleteRequestUI(req).then(
                    function () {
                        $scope.overlay = false;
                        // Re-load edit requests section.
                        $scope.selectAction('edit');
                    },
                    function (errorMsg) {
                        $scope.overlay = false;
                        Utils.handleError(error);
                    }
                );
            }
        );
    };

    // Helper function for formatting numbers with leading zeros
    $scope.pad = function (n, width, z) {
        return Utils.pad(n, width, z);
    };

    function preserveState() {
        var data = {};
        data.fetchError = $scope.fetchError;
        data.selectedReq = $scope.selectedReq;
        data.selectedLoan = $scope.selectedLoan;
        data.requests = $scope.requests;
        data.singleType = $scope.singleType;
        data.queryList = $scope.queryList;
        data.loanTypes = $scope.loanTypes;
        data.queries = $scope.queries;
        data.newRequestList = $scope.newRequestList;
        data.selectedList = $scope.selectedList;
        data.selectedAction = $scope.selectedAction;
        data.fetchError = $scope.fetchError;
        // contentAvailable will indicate whether sidenav can be visible
        data.contentAvailable = $scope.contentAvailable;
        // contentLoaded will indicate whether sidenav can be locked open
        data.contentLoaded = $scope.contentLoaded;
        Applicant.updateData(data);

        data = {};
        data.fetchId = $scope.fetchId;
        data.idPrefix = $scope.idPrefix;
        data.searchInput = $scope.searchInput;
        // This will enable / disable search bar in mobile screens
        data.searchEnabled = $scope.searchEnabled;
        Agent.updateData(data);
    }

    $scope.downloadManual = function () {
        window.open(Constants.BASEURL + 'public/manualAgente.pdf');
    };

    $scope.openMenu = function () {
        $mdSidenav('left').toggle();
    };

    // Enables / disables search bar (for mobile screens)
    $scope.toggleSearch = function () {
        $scope.searchEnabled = !$scope.searchEnabled;
        $timeout(function () {
            $("#search-input").focus();
        }, 300);
    };

    $scope.clearSearch = function () {
        $('#search-input').val('');
        $scope.searchInput = '';
    };

    $scope.loadUserData = function() {
        sessionStorage.setItem("fetchId", $scope.fetchId);
        window.open(Utils.getUserDataUrl(), '_blank');
    };
}

angular
    .module('sgdp')
    .controller('ApplicantHomeController', userHome);

userHome.$inject = ['$scope', '$cookies', '$timeout', 'Config', 'Applicant',
                    '$mdSidenav', '$mdDialog', '$mdMedia', 'Constants', 'Requests', 'Utils', '$state'];

function userHome($scope, $cookies, $timeout, Config, Applicant,
                  $mdSidenav, $mdDialog, $mdMedia, Constants, Requests, Utils, $state) {
    'use strict';
    $scope.selectedReq = Applicant.data.selectedReq;
    $scope.selectedLoan = Applicant.data.selectedLoan;
    $scope.requests = Applicant.data.requests;
    $scope.singleType = Applicant.data.singleType;
    $scope.req = Applicant.data.req;
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

    var fetchId = $cookies.getObject('session').id;

    $scope.selectAction = function (id) {
        $scope.requests = {};
        $scope.selectedAction = id;
        performAction(id);
    };

    if (!$scope.loanTypes) {
        $scope.loading = true;
        Requests.initializeListType().then(
            function (list) {
                $scope.loanTypes = list;
                $scope.contentAvailable = true;
                $scope.loading = false;
                $timeout(function () {
                    $scope.contentLoaded = true;
                    $mdSidenav('left').open();
                }, 600);
            },
            function (error) {
                Utils.showAlertDialog('Oops!', 'Ha ocurrido un error en el sistema.<br/>' +
                                               'Por favor intente más tarde.');
                console.log(error);
            }
        );
    } else if ($scope.selectedAction) {
        $scope.selectAction($scope.selectedAction);
    }

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
            case 'edit': {
                // Editable requests
                editRequests();
                break;
            }
        }
    }

    function getAllRequests () {
        $scope.requests = {};
        $scope.fetching = true;
        // Fetch user's requests
        Requests.getUserRequests(fetchId).then(
            function (data) {
                $scope.fetching = false;
                $scope.requests = data;
            },
            function (errorMsg) {
                $scope.fetchError = errorMsg;
                $scope.loading = false;
            }
        );
    }

    $scope.getRequestById = function (rid) {
        $scope.requests = {};
        $scope.fetching = true;
        Requests.getRequestById(rid, fetchId).then(
            function (request) {
                goToDetails(request);
            },
            function (error) {
                $scope.fetching = false;
                Utils.showAlertDialog('Error', error);
            }
        );
    };

    $scope.getRequestsByStatus = function (status) {
        $scope.requests = {};
        $scope.fetching = true;
        Requests.getRequestsByStatus(status, fetchId).then(
            function (requests) {
                $scope.requests = requests;
                $scope.fetching = false;
            },
            function (error) {
                $scope.fetching = false;
                Utils.showAlertDialog('Error', error);
            }
        );
    };

    $scope.getRequestsByDate = function (from, to) {
        $scope.requests = {};
        $scope.fetching = true;
        Requests.getRequestsByDate(from, to, fetchId).then(
            function (requests) {
                $scope.requests = requests;
                $scope.fetching = false;
            },
            function (error) {
                $scope.fetching = false;
                Utils.showAlertDialog('Error', error);
            }
        );
    };

    $scope.getRequestsByType = function (concept) {
        $scope.requests = {};
        $scope.singleType = [];
        $scope.fetching = true;
        Requests.getRequestsByType(concept, fetchId).then(
            function (requests) {
                $scope.singleType = requests;
                $scope.fetching = false;
            },
            function (error) {
                $scope.fetching = false;
                Utils.showAlertDialog('Error', error);
            }
        );
    };

    function getOpenedRequests () {
        $scope.requests = {};
        $scope.fetching = true;
        Requests.getOpenedRequests(fetchId).then(
            function (requests) {
                $scope.requests = requests;
                $scope.fetching = false;
            },
            function (error) {
                $scope.fetching = false;
                Utils.showAlertDialog('Error', error);
            }
        );
    }

    function editRequests () {
        $scope.requests = {};
        $scope.editableReq = [];
        $scope.fetching = true;
        Requests.getUserEditableRequests(fetchId).then(
            function (data) {
                $scope.fetching = false;
                $scope.editableReq = data;
            },
            function (errorMsg) {
                $scope.fetchError = errorMsg;
                $scope.loading = false;
            }
        );
    }

    $scope.goBack = function () {
        window.location.replace(Constants.IPAPEDI_URL + 'asociados');
    };

    /**
     * Toggles the selected request type list.
     *
     * @param index - selected request type index.
     */
    $scope.toggleList = function (index) {
        $scope.loanTypes[index].selected = !$scope.loanTypes[index].selected;
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
        sessionStorage.setItem("uid", fetchId);
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
               $scope.selectedAction != 'edit';
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
        $scope.selectedAction = 'N' + concept;
        var parentEl = angular.element(document.body);
        $mdDialog.show({
            parent: parentEl,
            targetEvent: $event,
            templateUrl: 'NewRequestController',
            clickOutsideToClose: false,
            escapeToClose: false,
            autoWrap: false,
            fullscreen: $mdMedia('xs'),
            locals: {
                fetchId: fetchId,
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
                        Requests.checkPreviousRequests(fetchId, concept).then(
                            function (opened) {
                                data.opened = opened;
                                Requests.getLoanTerms(concept).then(
                                    function (terms) {
                                        $scope.maxReqAmount = Requests.getMaxAmount();
                                        $scope.minReqAmount = Requests.getMinAmount();
                                        $scope.model.terms = terms;
                                        $scope.model.phone = data.userPhone ? Utils.pad(parseInt(data.userPhone, 10), 11) : '';
                                        $scope.model.email = data.userEmail;
                                        Requests.verifyAvailability(data, concept, false);
                                        $scope.loading = false;
                                    },
                                    function (error) {
                                        Utils.showAlertDialog('Oops!', error);
                                    }
                                );
                            },
                            function (error) {
                                Utils.showAlertDialog('Oops!', error);
                            }
                        );
                    },
                    function (error) {
                        Utils.showAlertDialog('Oops!', error);
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

            $scope.calculatePaymentFee = function() {
                if ($scope.model.reqAmount && $scope.model.due) {
                    return Requests.calculatePaymentFee($scope.model.reqAmount,
                                                        $scope.model.due,
                                                        Requests.getInterestRate($scope.model.type));
                } else {
                    return 0;
                }
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
                        Utils.showAlertDialog('Oops!', error);
                    }
                );
            }

            // Sets the bound input to the max possibe request amount
            $scope.setMax = function() {
                $scope.model.reqAmount = $scope.maxReqAmount;
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

    $scope.selected = [];

    $scope.query = {
        order: 'name',
        limit: 5,
        page: 1
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
            templateUrl: 'NewRequestController',
            clickOutsideToClose: false,
            escapeToClose: false,
            autoWrap: false,
            fullscreen: $mdMedia('xs'),
            locals: {
                fetchId: fetchId,
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

            // obj could have a reference to user data, saved
            // before confirmation dialog was opened.
            var model = {
                reqAmount: request.reqAmount,
                type: parseInt(request.type, 10),
                due: request.due,
                phone: Utils.pad(request.phone, 11),
                email: request.email
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
                        Requests.checkPreviousRequests(fetchId, model.type).then(
                            function (opened) {
                                data.opened = opened;
                                Requests.getLoanTerms(model.type).then(
                                    function (terms) {
                                        $scope.maxReqAmount = Requests.getMaxAmount();
                                        $scope.minReqAmount = Requests.getMinAmount();
                                        $scope.model.terms = terms;
                                        Requests.verifyAvailability(data, model.type, true);
                                        $scope.loading = false;
                                    },
                                    function (error) {
                                        Utils.showAlertDialog('Oops!', error);
                                    }
                                );
                            },
                            function (error) {
                                Utils.showAlertDialog('Oops!', error);
                            }
                        );
                    },
                    function (error) {
                        Utils.showAlertDialog('Oops!', error);
                    }
                );
            }

            $scope.missingField = function () {
                return (typeof $scope.model.reqAmount === "undefined"
                        || typeof $scope.model.phone === "undefined"
                        || typeof $scope.model.email === "undefined"
                        || !$scope.model.due)
                       || ($scope.model.reqAmount === request.reqAmount &&
                        Utils.pad($scope.model.phone, 11) === request.phone &&
                       $scope.model.email === request.email &&
                       parseInt($scope.model.due, 10) === request.due);
                };

            $scope.closeDialog = function () {
                $mdDialog.hide();
            };

            $scope.calculatePaymentFee = function() {
                if ($scope.model.reqAmount && $scope.model.due) {
                    return Requests.calculatePaymentFee($scope.model.reqAmount,
                                                        $scope.model.due,
                                                        Requests.getInterestRate($scope.model.type));
                } else {
                    return 0;
                }
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
                        Utils.showAlertDialog('Oops!', error);
                    }
                );
            }

            // Sets the bound input to the max possibe request amount
            $scope.setMax = function() {
                $scope.model.reqAmount = $scope.maxReqAmount;
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
                        Utils.showAlertDialog('Oops!', errorMsg);
                    }
                );
            }
        );
    };

    // Helper function for formatting numbers with leading zeros
    $scope.pad = function (n, width, z) {
        return Utils.pad(n, width, z);
    };

    $scope.downloadManual = function () {
        window.open(Constants.BASEURL + 'public/manualUsuario.pdf');
    };

    $scope.openMenu = function () {
        $mdSidenav('left').toggle();
    };

    function preserveState() {
        var data = {};
        data.req = $scope.req; // Will contain the selected request object.
        data.fetchError = $scope.fetchError;
        data.selectedReq = $scope.selectedReq;
        data.selectedLoan = $scope.selectedLoan;
        data.requests = $scope.requests;
        data.singleType = $scope.singleType;
        data.req = $scope.req;
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
    }
}

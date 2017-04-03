angular
    .module('sgdp')
    .controller('ApplicantHomeController', userHome);

userHome.$inject = ['$scope', '$cookies', '$timeout', 'Config',
                    '$mdSidenav', '$mdDialog', '$mdMedia', 'Constants', 'Requests', 'Utils'];

function userHome($scope, $cookies, $timeout, Config,
                  $mdSidenav, $mdDialog, $mdMedia, Constants, Requests, Utils) {
    'use strict';
    $scope.loading = true;
    $scope.selectedReq = '';
    $scope.selectedLoan = -1;
    $scope.requests = {};
    $scope.req = null;
    Requests.initializeListType().then(
        function (list) {
            $scope.loanTypes = list;
            console.log(list);
            $scope.loading = true;
            // Fetch user's requests
            Requests.getUserRequests(fetchId).then(
                function (data) {
                    $scope.requests = data;
                    console.log($scope.requests);
                    $scope.loading = false;
                    $scope.contentAvailable = true;
                    $timeout(function () {
                        $scope.contentLoaded = true;
                        $mdSidenav('left').open();
                    }, 600);
                },
                function (errorMsg) {
                    $scope.fetchError = errorMsg;
                    $scope.loading = false;
                }
            );
        },
        function (error) {
            Utils.showAlertDialog('Oops!', 'Ha ocurrido un error en el sistema.<br/>' +
                                           'Por favor intente más tarde.');
            console.log(error);
        }
    );
    $scope.queryList = [
            {id: 1, text: 'Todas mis solicitudes'},
            {id: 2, text: 'Solicitud por ID'},
            {id: 3, text: 'Solicitudes por fecha'},
            {id: 4, text: 'Solicitudes por estatus'},
            {id: 5, text: 'Solicitudes abiertas'}
        ];

    $scope.newRequestList = false;
    $scope.selectedList = 0;
    $scope.fetchError = '';
    // contentAvailable will indicate whether sidenav can be visible
    $scope.contentAvailable = false;
    // contentLoaded will indicate whether sidenav can be locked open
    $scope.contentLoaded = false;

    var fetchId = $cookies.getObject('session').id;

    $scope.toggleQueryList = function () {
        $scope.queryList.selected = !$scope.queryList.selected;
    };

    $scope.toggleNewRequestList = function () {
        $scope.newRequestList = !$scope.newRequestList;
    };

    $scope.toggleRefinancingList = function () {
        $scope.refinancingList = !$scope.refinancingList;
    };

    $scope.togglePanelList = function (index) {
        $scope.selectedList = $scope.selectedList == index ? null : index;
    };

    $scope.selectAction = function (id) {
        $scope.selectedAction = id;
    };

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

    /**
     * Selects the specified request.
     *
     * @param i - row index of the selected request in $scope.requests
     * @param j - column index of the selected request in $scope.requests
     */
    $scope.selectRequest = function (i, j) {
        $scope.selectedReq = i;
        $scope.selectedLoan = j;
        if (i != '' && j != -1) {
            $scope.req = $scope.requests[i][j];
        }
        $mdSidenav('left').toggle();
    };

    // Calculates the request's payment fee.
    $scope.calculatePaymentFee = function() {
        return $scope.req ? Requests.calculatePaymentFee($scope.req.reqAmount,
                                                         $scope.req.due,
                                                         Requests.getInterestRate($scope.req.type)) : 0;
    };

    /**
     * Determines whether the specified object is empty (i.e. has no attributes).
     *
     * @param obj - object to test.
     * @returns {boolean}
     */
    $scope.isObjEmpty = function(obj) {
        return Utils.isObjEmpty(obj);
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
        $scope.refinancingList = false;
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
                requests: $scope.requests,
                obj: obj,
                parentScope: $scope
            },
            controller: DialogController
        });
        // Isolated dialog controller for the new request dialog
        function DialogController($scope, $mdDialog, fetchId,
                                  requests, parentScope, obj) {
            $scope.docPicTaken = false;
            $scope.uploading = false;
            $scope.maxReqAmount = Requests.getMaxAmount();
            $scope.minReqAmount = Requests.getMinAmount();
            // if user data exists, it means the ID was
            // already given, so we must show it.
            $scope.uploadErr = '';
            // Hold scope reference to constants
            $scope.APPLICANT = Constants.Users.APPLICANT;
            $scope.AGENT = Constants.Users.AGENT;

            // obj could have a reference to user data, saved
            // before confirmation dialog was opened.
            $scope.model = obj || {};
            $scope.model.loanTypes = Config.loanConcepts;
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
                Requests.getAvailabilityData(fetchId).then(
                    function (data) {
                        data.opened = Requests.checkPreviousRequests(requests);
                        Requests.getLoanTerms().then(
                            function (terms) {
                                $scope.model.terms = terms;
                                $scope.model.phone = Utils.pad(parseInt(data.userPhone, 10), 11);
                                $scope.model.email = data.userEmail;
                                $scope.model.allow = data.granting.allow;
                                $scope.model.span = data.granting.span;
                                $scope.model.opened = data.opened;
                                $scope.model.type = Requests.verifyAvailability(data);
                                if($scope.model.type) {
                                    $scope.loading = false;
                                }
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
                    function() {
                        updateRequestListUI(fetchId, 0, 'Solicitud creada',
                                            'Por favor valide su solicitud para proceder.',
                                            true, true,
                                            parseInt(postData.loanType, 10));
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
                        parentScope.openNewRequestDialog(null, $scope.model);
                    },
                    function() {
                        // Re-open parent dialog and do nothing
                        parentScope.openNewRequestDialog(null, $scope.model);
                    }
                );
            };
        }
    };

    $scope.openRefinancingRequestDialog = function ($event, concept, obj) {
        $scope.selectedAction = 'R' + concept;
        $scope.newRequestList = false;
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
                requests: $scope.requests,
                obj: obj,
                parentScope: $scope
            },
            controller: DialogController
        });
        // Isolated dialog controller for the new request dialog
        function DialogController($scope, $mdDialog, fetchId,
                                  requests, parentScope, obj) {
            $scope.docPicTaken = false;
            $scope.uploading = false;
            $scope.maxReqAmount = Requests.getMaxAmount();
            $scope.minReqAmount = Requests.getMinAmount();
            // if user data exists, it means the ID was
            // already given, so we must show it.
            $scope.uploadErr = '';
            // Hold scope reference to constants
            $scope.APPLICANT = Constants.Users.APPLICANT;
            $scope.AGENT = Constants.Users.AGENT;

            // obj could have a reference to user data, saved
            // before confirmation dialog was opened.
            $scope.model = obj || {};
            $scope.model.loanTypes = Config.loanConcepts;
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
                Requests.getAvailabilityData(fetchId).then(
                    function (data) {
                        data.opened = Requests.checkPreviousRequests(requests);
                        Requests.getLoanTerms().then(
                            function (terms) {
                                $scope.model.terms = terms;
                                $scope.model.phone = Utils.pad(parseInt(data.userPhone, 10), 11);
                                $scope.model.email = data.userEmail;
                                $scope.model.allow = data.granting.allow;
                                $scope.model.span = data.granting.span;
                                $scope.model.opened = data.opened;
                                $scope.model.type = Requests.verifyAvailability(data);
                                if($scope.model.type) {
                                    $scope.loading = false;
                                }
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
                    function() {
                        updateRequestListUI(fetchId, 0, 'Solicitud creada',
                                            'Por favor valide su solicitud para proceder.',
                                            true, true,
                                            parseInt(postData.loanType, 10));
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
                        parentScope.openNewRequestDialog(null, $scope.model);
                    },
                    function() {
                        // Re-open parent dialog and do nothing
                        parentScope.openNewRequestDialog(null, $scope.model);
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

    function success(desserts) {
        $scope.desserts = desserts;
    }

    $scope.getDesserts = function () {
        $scope.promise = $nutrition.desserts.get($scope.query, success).$promise;
    };

    /**
     * Opens the edition request dialog and performs the corresponding operations.
     *
     * @param $event - DOM event.
     * @param obj - optional obj containing user input data.
     */
    $scope.openEditRequestDialog = function ($event, obj) {
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
                request: $scope.requests[$scope.selectedReq][$scope.selectedLoan],
                selectedLoan: $scope.selectedLoan,
                obj: obj,
                parentScope: $scope,
                requests: $scope.requests
            },
            controller: DialogController
        });
        // Isolated dialog controller for the new request dialog
        function DialogController($scope, $mdDialog, fetchId, request,
                                  selectedLoan, parentScope, obj, requests) {
            $scope.docPicTaken = false;
            $scope.uploading = false;
            $scope.maxReqAmount = Requests.getMaxAmount();
            $scope.minReqAmount = Requests.getMinAmount();
            $scope.uploadErr = '';
            // Hold scope reference to constants
            $scope.APPLICANT = Constants.Users.APPLICANT;
            $scope.AGENT = Constants.Users.AGENT;

            // obj could have a reference to user data, saved
            // before confirmation dialog was opened.
            var model = {
                reqAmount: request.reqAmount,
                type: request.type,
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

            // Checks whether conditions for creating new requests are fulfilled.
            function checkCreationConditions () {
                $scope.loading = true;
                Requests.getLastRequestsGranting(fetchId)
                    .then (
                    function (granting) {
                        verifyGranting(granting);
                        Requests.getLoanTerms().then(
                            function (terms) {
                                $scope.model.terms = terms;
                                $scope.model.opened = Requests.checkPreviousRequests(requests);
                                // On-edition request should not be disabled (as we know it's still open)
                                $scope.model.opened.hasOpen[request.type] = false;
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
            }

            /**
             * Helper function that verifies the if request span has been
             * fulfilled for each type of request.
             *
             * @param granting - response from getLastRequestsGranting.
             */
            function verifyGranting (granting) {
                $scope.model.allow = granting.allow;
                $scope.model.span = granting.span;
                var allDenied = true;
                angular.forEach(granting.allow, function(allow) {
                    if (allow) {
                        allDenied = false;
                    }
                });
                if (allDenied) {
                    Utils.showAlertDialog('No permitido',
                                          'Estimado usuario, aún no ha' + (granting.span == 1 ? '' : 'n') +
                                          ' transcurrido ' + granting.span + (granting.span == 1 ? ' mes' : ' meses') +
                                          ' desde el último préstamo otorgado, para cada tipo de ' +
                                          'solicitud disponible a través del sistema.');
                }
            }

            $scope.missingField = function () {
                return (typeof $scope.model.reqAmount === "undefined"
                        || typeof $scope.model.phone === "undefined"
                        || typeof $scope.model.email === "undefined")
                       || ($scope.model.reqAmount === request.reqAmount &&
                        Utils.pad($scope.model.phone, 11) === request.phone &&
                       $scope.model.email === request.email &&
                       parseInt($scope.model.due, 10) === request.due &&
                       $scope.model.type === request.type);
                };

            $scope.closeDialog = function () {
                $mdDialog.hide();
            };

            $scope.calculatePaymentFee = function() {
                if ($scope.model.reqAmount) {
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
                    function() {
                        updateRequestListUI(fetchId, selectedLoan, 'Solicitud editada',
                                            'La información de su solicitud ha sido editada exitosamente.',
                                            true, true,
                                            parseInt(postData.loanType, 10));
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
                        parentScope.openEditRequestDialog(null, $scope.model);
                    },
                    function() {
                        // Re-open parent dialog and do nothing
                        parentScope.openEditRequestDialog(null, $scope.model);
                    }
                );
            };
        }
    };

    $scope.editEmail = function(ev) {
        var parentEl = angular.element(document.body);
        $mdDialog.show({
            parent: parentEl,
            targetEvent: ev,
            clickOutsideToClose: true,
            escapeToClose: true,
            templateUrl: 'EditRequestController/emailEditionDialog',
            locals: {
                req: $scope.req,
                userId: fetchId,
                selectedReq: $scope.selectedReq,
                selectedLoan: $scope.selectedLoan
            },
            controller: DialogController
        });

        function DialogController($scope, req, userId, selectedReq, selectedLoan) {
            $scope.email = req.email;
            $scope.loading = false;

            $scope.saveEdition = function () {
                if (!$scope.canSend()) return;
                $scope.loading = true;
                Requests.editEmail(req.id, $scope.email).then(
                    function () {
                        updateRequestListUI(userId, selectedLoan,
                                            'Actualización exitosa', 'La dirección de correo ha sido actualizada ' +
                                                                     'exitosamente',
                                            true, false, selectedReq);
                    },
                    function (errorMsg) {
                        Utils.showAlertDialog('Oops!', errorMsg);
                    }
                );
            };

            $scope.canSend = function() {
                return typeof $scope.email !== "undefined" &&
                       $scope.email !== req.email;
            }
        }
    };

    $scope.validateRequest = function (ev) {
        Utils.showConfirmDialog(
            'Advertencia',
            'Al validar su solicitud no ya no podrá editarla ni eliminarla. ¿Desea continuar?' ,
            'Continuar',
            'Cancelar',
            ev, true).then(
            function() {
                $scope.validating = true;
                Requests.validateRequest($scope.req.id).then(
                    function (date) {
                        $scope.validating = false;
                        Utils.showAlertDialog('Solicitud validada',
                                              'Su solicitud será atendida en menos de 48 horas hábiles.');
                        $scope.req.validationDate = date;
                    },
                    function (error) {
                        $scope.validating = false;
                        Utils.showAlertDialog('Oops!', error);
                    }
                );
            });
    };

    $scope.deleteRequest = function (ev) {
        Utils.showConfirmDialog(
            'Confirmación de eliminación',
            'Al eliminar la solicitud, también se eliminarán ' +
            'todos los datos asociados a ella.',
            'Continuar',
            'Cancelar',
            ev, true).then(
            function() {
                $scope.overlay = true;
                Requests.deleteRequestUI($scope.req).then(
                    function () {
                        // Update interface
                        updateRequestListUI(fetchId, -1, 'Solicitud eliminada',
                                            'La solicitud fue eliminada exitosamente.',
                                            true, true, $scope.req.type);
                        $scope.req = null;
                    },
                    function (errorMsg) {
                        $scope.overlay = false;
                        Utils.showAlertDialog('Oops!', errorMsg);
                    }
                );
            }
        );
    };

    // Helper method that updates UI's request list.
    function updateRequestListUI(userId, autoSelectIndex,
                                 dialogTitle, dialogContent,
                                 updateUI, toggleList, type) {
        // Update interface
        Requests.getUserRequests(userId).then(
            function (data) {
                // Update UI only if needed
                var loanType = type;
                if (updateUI) {
                    updateContent(data, loanType, autoSelectIndex, toggleList);
                }
                // Toggle request list only if requested.
                if (toggleList) {
                    toggleReqList(loanType);
                }
                // Close dialog and alert user that operation was
                // successful
                $scope.overlay = false;
                $mdDialog.hide();
                Utils.showAlertDialog(dialogTitle, dialogContent);
            },
            function (errorMsg) {
                $scope.overlay = false;
            }
        );
    }

    /**
     * Helper function that updates content with new request.
     *
     * @param newRequests - the updated requests obj.
     * @param req - New request's type.
     * @param selection - Specific request's index.
     * @param toggleList - Whether should to toggle request list or not.
     */
    function updateContent(newRequests, req, selection, toggleList) {
        $scope.contentLoaded = true;
        $scope.contentAvailable = true;
        $scope.fetchError = '';
        $scope.requests = newRequests;
        // Close the list
        if (toggleList) closeAllReqList();
        // Automatically select created request
        $scope.selectRequest(req, selection);
    }

    /**
     * Automatically toggles the requests list.
     *
     * @param index - Request list's index
     */
    function toggleReqList(index) {
        $timeout(function () {
            // Open the list
            $scope.loanTypes[index].selected = true;
        }, 1000);

    }

    function closeAllReqList() {
        $scope.selectedReq = '';
        $scope.selectedLoan = -1;
        angular.forEach($scope.loanTypes, function(show, index) {
            $scope.loanTypes[index].selected = false;
        });
    }

    // Helper function for formatting numbers with leading zeros
    $scope.pad = function (n, width, z) {
        return Utils.pad(n, width, z);
    };

    $scope.downloadDoc = function (doc) {
        window.open(Requests.getDocDownloadUrl(doc.id));
    };

    $scope.downloadAll = function () {
        location.href = Requests.getAllDocsDownloadUrl($scope.req.docs);
    };

    $scope.downloadManual = function () {
        window.open(Constants.BASEURL + 'public/manualUsuario.pdf');
    };

    $scope.openMenu = function () {
        $mdSidenav('left').toggle();
    };
}

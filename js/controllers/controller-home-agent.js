angular
    .module('sgdp')
    .controller('AgentHomeController', agentHome);

agentHome.$inject = ['$scope', '$mdDialog', 'FileUpload', 'Constants', 'Agent', 'Config',
                     '$state', '$timeout', '$mdSidenav', '$mdMedia', 'Requests', 'Utils', 'Auth'];

function agentHome($scope, $mdDialog, FileUpload, Constants, Agent, Config,
                   $state, $timeout, $mdSidenav, $mdMedia, Requests, Utils, Auth) {
    'use strict';
    $scope.selectedReq = Agent.data.selectedReq;
    $scope.selectedLoan = Agent.data.selectedLoan;
    $scope.requests = Agent.data.requests;
    $scope.req = Agent.data.req;
    $scope.fetchError = Agent.data.fetchError;
    $scope.showList = Agent.data.showList;
    $scope.fetchId = Agent.data.fetchId;
    $scope.searchInput = Agent.data.searchInput;
    $scope.APPROVED_STRING = Constants.Statuses.APPROVED;
    $scope.REJECTED_STRING = Constants.Statuses.REJECTED;
    $scope.RECEIVED_STRING = Constants.Statuses.RECEIVED;
    // contentAvailable will indicate whether sidenav can be visible
    $scope.contentAvailable = Agent.data.contentAvailable;
    // contentLoaded will indicate whether sidenav can be locked open
    $scope.contentLoaded = Agent.data.contentLoaded;
    // This will enable / disable search bar in mobile screens
    $scope.searchEnabled = Agent.data.searchEnabled;
    Requests.initializeListType().then(
        function (list) {
            $scope.loanTypes = list;
        },
        function (error) {
            Utils.showAlertDialog('Oops!', 'Ha ocurrido un error en el sistema.<br/>' +
                                           'Por favor intente más tarde.');
            console.log(error);
        }
    );
    $scope.idPrefix = 'V';
    $scope.loading = false;

    /**
     * Toggles the selected request type list.
     *
     * @param index - selected request type index.
     */
    $scope.toggleList = function (index) {
        $scope.loanTypes[index].selected = !$scope.loanTypes[index].selected;
    };

    $scope.goBack = function () {
        Auth.logout();
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

    /**
     * Determines whether the specified object is empty (i.e. has no attributes).
     *
     * @param obj - object to test.
     * @returns {boolean}
     */
    $scope.isObjEmpty = function (obj) {
        return Utils.isObjEmpty(obj);
    };

    $scope.fetchRequests = function (searchInput) {
        $scope.contentAvailable = false;
        $scope.fetchId = $scope.idPrefix + searchInput;
        $scope.requests = [];
        closeAllReqList();
        $scope.loading = true;
        $scope.req = null;
        $scope.fetchError = "";
        Requests.getUserRequests($scope.fetchId).then(
            function (data) {
                $scope.requests = data;
                $scope.contentAvailable = true;
                $scope.loading = false;
                $timeout(function () {
                    $scope.contentLoaded = true;
                    $mdSidenav('left').open();
                }, 300);
            },
            function (errorMsg) {
                $scope.fetchError = errorMsg;
                $scope.loading = false;
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
    $scope.pad = function (n, width, z) {
        return Utils.pad(n, width, z);
    };


    $scope.openNewRequestDialog = function ($event, obj) {
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
                fetchId: $scope.fetchId,
                requests: $scope.requests,
                parentScope: $scope,
                obj: obj
            },
            controller: DialogController
        });
        // Isolated dialog controller for the new request dialog
        function DialogController($scope, $mdDialog, fetchId,
                                  requests, parentScope, obj) {
            $scope.idPicTaken = false;
            $scope.docPicTaken = false;
            $scope.uploading = false;
            $scope.maxReqAmount = Requests.getMaxAmount();
            $scope.minReqAmount = Requests.getMinAmount();
            $scope.APPLICANT = Constants.Users.APPLICANT;
            $scope.AGENT = Constants.Users.AGENT;
            // obj could have a reference to user data, saved
            // before confirmation dialog was opened.
            $scope.model = obj || {};
            $scope.model.loanTypes = Config.loanConcepts;
            $scope.confirmButton = 'Crear';
            $scope.title = 'Nueva solicitud de préstamo';

            $scope.uploadErr = '';

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

            $scope.mapLoanType = function (code) {
                return Requests.mapLoanType(code);
            };

            $scope.closeDialog = function () {
                $mdDialog.hide();
            };

            $scope.missingField = function () {
                return typeof $scope.model.reqAmount === "undefined" ||
                       typeof $scope.model.type === "undefined" ||
                       !$scope.model.due ||
                       !$scope.model.phone ||
                       !$scope.model.email;
            };

            // Creates new request in database.
            function createNewRequest() {
                $scope.uploading = true;
                var docs = [];

                docs.push(Requests.createRequestDocData(fetchId));
                performCreation(docs);
            }

            // Shows a dialog asking user to confirm the request creation.
            $scope.confirmOperation = function (ev) {
                Utils.showConfirmDialog(
                    'Confirmación de creación de solicitud',
                    'El sistema generará el documento correspondiente a esta solicitud. ¿Desea proceder?',
                    'Sí', 'Cancelar', ev, true
                ).then(
                    function () {
                        // Re-open parent dialog and perform request creation
                        $scope.model.confirmed = true;
                        parentScope.openNewRequestDialog(null, $scope.model);
                    },
                    function () {
                        // Re-open parent dialog and do nothing
                        parentScope.openNewRequestDialog(null, $scope.model);
                    }
                );
            };

            // Sets the bound input to the max possibe request amount
            $scope.setMax = function () {
                $scope.model.reqAmount = $scope.maxReqAmount;
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
                    function () {
                        updateRequestListUI(fetchId, 0, 'Solicitud creada',
                                            'La solicitud ha sido creada exitosamente. El asociado ' +
                                            ' debe ingresar al sistema y realizar la correspondiente validación.',
                                            true, true,
                                            parseInt(postData.loanType, 10));
                    },
                    function (error) {
                        Utils.showAlertDialog('Oops!', error);
                    }
                );
            }
        }
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
                $mdDialog.hide();
                $scope.overlay = false;
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
     * @param toggleList - Whether list should be toggled or not.
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

    /**
     * Custom dialog for updating an existing request
     */
    $scope.openUpdateRequestDialog = function ($event) {
        var parentEl = angular.element(document.body);
        $mdDialog.show({
            parent: parentEl,
            targetEvent: $event,
            templateUrl: 'EditRequestController',
            clickOutsideToClose: false,
            escapeToClose: false,
            fullscreen: $mdMedia('xs'),
            locals: {
                fetchId: $scope.fetchId,
                request: $scope.req,
                selectedLoan: $scope.selectedLoan
            },
            controller: DialogController
        });
        // Isolated dialog controller
        function DialogController($scope, $mdDialog, fetchId, request, selectedLoan) {
            $scope.files = [];
            $scope.fetchId = fetchId;
            $scope.uploading = false;
            $scope.request = request;
            $scope.enabledDescription = -1;
            $scope.comment = $scope.request.comment;

            $scope.closeDialog = function () {
                $mdDialog.hide();
            };

            $scope.removeDoc = function (index) {
                $scope.files.splice(index, 1);
                $scope.selectedFiles = $scope.files.length > 0 ?
                $scope.files.length + ' archivo(s)' : null;
            };

            $scope.isDescriptionEnabled = function (dKey) {
                return $scope.enabledDescription == dKey;
            };

            $scope.enableDescription = function (dKey) {
                $scope.enabledDescription = dKey;
                $timeout(function () {
                    $("#" + dKey).focus();
                }, 300);
            };

            $scope.allFieldsMissing = function () {
                return $scope.files.length == 0 &&
                       (typeof $scope.comment === "undefined"
                        || $scope.comment == ""
                        || $scope.comment == $scope.request.comment);
            };

            $scope.showError = function (error, param) {
                return FileUpload.showDocUploadError(error, param);
            };

            // Gathers the files whenever the file input's content is updated
            $scope.gatherFiles = function (files, errFiles) {
                $scope.files = files;
                $scope.selectedFiles = $scope.files.length > 0 ?
                $scope.files.length + ' archivo(s)' : null;
                $scope.errFiles = errFiles;
            };

            // Deletes all selected files
            $scope.deleteFiles = function (ev) {
                $scope.files = [];
                $scope.selectedFiles = null;
                // Stop click event propagation, otherwise file chooser will
                // also be opened.
                ev.stopPropagation();
            };

            // Creates new request in database and uploads documents
            $scope.updateRequest = function () {
                $scope.uploading = true;
                $scope.request.comment = $scope.comment;
                if ($scope.files.length === 0) {
                    performEdition($scope.request);
                } else {
                    // Add additional files to this request.
                    uploadFiles($scope.files, fetchId);
                }
            };

            // Performs the request edition update in DB
            function performEdition(postData) {
                Requests.updateRequest(postData).then(
                    function () {
                        var updateContent = $scope.files.length > 0;
                        updateRequestListUI($scope.fetchId, selectedLoan,
                                            'Solicitud actualizada',
                                            'La solicitud fue actualizada exitosamente.',
                                            updateContent, false, parseInt(postData.type, 10));
                    },
                    function (errorMsg) {
                        $scope.overlay = false;
                        Utils.showAlertDialog('Oops!', errorMsg);
                    }
                );
            }

            // Uploads each of selected documents to the server
            function uploadFiles(files, userId) {
                FileUpload.uploadFiles(files, userId).then(
                    function (docs) {
                        $scope.request.newDocs = docs;
                        performEdition($scope.request)
                    },
                    function (errorMsg) {
                        // Show file error message
                        $scope.errorMsg = errorMsg;
                    }
                );
            }
        }
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
            fullscreen: $mdMedia('xs'),
            autoWrap: false,
            locals: {
                fetchId: $scope.fetchId,
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
                                          'Aún no ha' + (granting.span == 1 ? '' : 'n') +
                                          ' transcurrido '
                                          + granting.span + (granting.span == 1 ? ' mes' : ' meses') +
                                          ' desde el último préstamo otorgado para cualquier tipo de ' +
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

            $scope.calculatePaymentFee = function() {
                if ($scope.model.reqAmount) {
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
                    loanType: $scope.model.type,
                    email: $scope.model.email
                };
                Requests.editRequest(postData).then(
                    function() {
                        updateRequestListUI(fetchId, selectedLoan, 'Solicitud editada',
                                            'La solicitud ha sido editada exitosamente.',
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
                    'Se guardarán los cambios que hayan realizado a la solicitud. ¿Desea proceder?',
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

    $scope.deleteDoc = function (ev, dKey) {
        Utils.showConfirmDialog(
            'Confirmación de eliminación',
            "El documento " + $scope.req.docs[dKey].name + " será eliminado.",
            'Continuar',
            'Cancelar',
            ev, true).then(
            function() {
                $scope.overlay = true;
                Requests.deleteDocument(
                    $scope.req.docs[dKey]
                ).then(
                    function () {
                        // Update interface
                        updateRequestListUI($scope.fetchId, $scope.selectedLoan,
                                            'Documento eliminado',
                                            'El documento fue eliminado exitosamente.',
                                            true, false, $scope.selectedReq);
                    },
                    function (errorMsg) {
                        Utils.showAlertDialog('Oops!', errorMsg);
                    }
                )
            }
        );
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
                        updateRequestListUI($scope.fetchId, -1, 'Solicitud eliminada',
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

    /*
     * Mini custom dialog to edit a document's description
     */
    $scope.editDescription = function ($event, doc) {
        var parentEl = angular.element(document.body);
        $mdDialog.show({
            parent: parentEl,
            targetEvent: $event,
            clickOutsideToClose: true,
            escapeToClose: true,
            templateUrl: 'EditRequestController/editionDialog',
            locals: {
                doc: doc
            },
            controller: DialogController
        });

        function DialogController($scope, $mdDialog, doc) {
            $scope.description = doc.description;

            $scope.missingField = function () {
              return typeof $scope.description === "undefined" ||
                     $scope.description == doc.description;
            };
            $scope.saveEdition = function () {
                if ($scope.missingField()) {return;}
                doc.description = $scope.description;
                Requests.updateDocDescription(doc).then(
                    function () {},
                    function (errorMsg) {
                        Utils.showAlertDialog('Oops!', errorMsg);
                    }
                );
                $mdDialog.hide();
            }
        }
    };

    $scope.loadHistory = function () {
        // Save necessary data before changing views.
        preserveState();
        // Send required data to history
        sessionStorage.setItem("req", JSON.stringify($scope.req));
        $state.go('history');
    };

    function preserveState() {
        var data = {};
        data.selectedReq = $scope.selectedReq;
        data.selectedLoan = $scope.selectedLoan;
        data.requests = $scope.requests;
        data.req = $scope.req;
        data.fetchError = $scope.fetchError;
        data.fetchId = $scope.fetchId;
        data.searchInput = $scope.searchInput;
        // contentAvailable will indicate whether sidenav can be visible
        data.contentAvailable = $scope.contentAvailable;
        // contentLoaded will indicate whether sidenav can be locked open
        data.contentLoaded = $scope.contentLoaded;
        // This will enable / disable search bar in mobile screens
        data.searchEnabled = $scope.searchEnabled;

        Agent.updateData(data);
    }

    $scope.downloadDoc = function (doc) {
        window.open(Requests.getDocDownloadUrl(doc.id));
    };

    $scope.downloadManual = function () {
        window.open(Constants.BASEURL + 'public/manualAgente.pdf');
    };

    $scope.downloadAll = function () {
        location.href = Requests.getAllDocsDownloadUrl($scope.req.docs);
    };

    $scope.loadUserData = function () {
        sessionStorage.setItem("fetchId", $scope.fetchId);
        window.open(Utils.getUserDataUrl(), '_blank');
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
}

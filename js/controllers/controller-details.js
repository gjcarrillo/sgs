angular
    .module('sgdp')
    .controller('DetailsController', details);

details.$inject = ['$scope', 'Utils', 'Requests', 'Auth', 'Config', 'Constants', '$mdDialog', '$mdMedia',
                   '$state', 'FileUpload', '$timeout', 'Manager'];

function details($scope, Utils, Requests, Auth, Config, Constants, $mdDialog, $mdMedia,
                 $state, FileUpload, $timeout, Manager) {
    'use strict';

    // If no data has been sent, show nothing.
    if (sessionStorage.getItem("req") === null) { return; }
    var fetchId = sessionStorage.getItem("uid");
    $scope.req = JSON.parse(sessionStorage.getItem("req"));

    // This would happen in case user reloads (F5) being in the details view.
    if (!Config.loanConcepts) {
        Config.loanConcepts = JSON.parse(sessionStorage.getItem("loanConcepts"));
    }

    $scope.showMsg = true;
    $scope.APPROVED = Constants.Statuses.APPROVED;
    $scope.loanTypes = Config.loanConcepts;
    $scope.APPLICANT = Constants.Users.APPLICANT;
    $scope.AGENT = Constants.Users.AGENT;
    $scope.MANAGER = Constants.Users.MANAGER;
    $scope.RECEIVED = Constants.Statuses.RECEIVED;
    $scope.APPROVED = Constants.Statuses.APPROVED;
    $scope.PRE_APPROVED = Constants.Statuses.PRE_APPROVED;
    $scope.REJECTED = Constants.Statuses.REJECTED;

    if ($scope.req.status == $scope.APPROVED) {
        $scope.loading = true;
        Requests.getAvailabilityData(fetchId, $scope.req.type).then(
            function (data) {
                $scope.dateAvailable = data.granting.dateAvailable;
                $scope.loading = false;
            },
            function (error) {
                $scope.loading = false;
                Utils.handleError(error);
            }
        )
    }
    $scope.pad = function (n, width, z) {
        return Utils.pad(n, width, z);
    };

    // Calculates the request's payment fee.
    $scope.calculatePaymentFee = function() {
        return $scope.req ? Requests.calculatePaymentFee($scope.req.reqAmount,
                                                         $scope.req.due,
                                                         $scope.req.type) : 0;
    };

    $scope.downloadDoc = function (doc) {
        window.open(Requests.getDocDownloadUrl(doc.id));
    };

    $scope.downloadAll = function () {
        location.href = Requests.getAllDocsDownloadUrl($scope.req.docs);
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
                        $scope.overlay = false;
                        Utils.showAlertDialog('Solicitud eliminada', "La solicitud fue eliminada exitosamente.");
                        $scope.goHome();
                    },
                    function (errorMsg) {
                        $scope.overlay = false;
                        Utils.handleError(errorMsg);
                    }
                );
            }
        );
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
                request: $scope.req,
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
                        $scope.model.data = data;
                        Requests.checkPreviousRequests(fetchId, model.type).then(
                            function (opened) {
                                data.opened = opened;
                                Requests.getLoanTerms(model.type).then(
                                    function (terms) {
                                        $scope.model.maxReqAmount = Requests.getMaxAmount();
                                        $scope.model.terms = terms;
                                        Requests.verifyAvailability(data, model.type, true);
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
                Requests.editRequest(postData).then(
                    function(updatedReq) {
                        Utils.showAlertDialog(
                            'Solicitud editada',
                            'La información de su solicitud ha sido editada exitosamente'
                        );
                        // Update saved request and reload view.
                        sessionStorage.setItem("req", JSON.stringify(updatedReq));
                        $state.go($state.current, {}, {reload: true})
                    },
                    function(error) {
                        $scope.uploading = false;
                        Utils.handleError(error);
                    }
                );
            }

            $scope.calculateMedicalDebtContribution = function () {
                var contribution = 0.2 * $scope.model.reqAmount;
                return $scope.model.data.medicalDebt > contribution ? contribution : $scope.model.data.medicalDebt;
            };

            $scope.calculateNewInterest = function () {
                return ($scope.model.reqAmount - ($scope.calculateMedicalDebtContribution() || 0) + $scope.model.data.lastLoanFee) *
                       0.01 / $scope.model.data.daysOfMonth * $scope.model.data.newLoanInterestDays;
            };

            $scope.calculateLoanAmount = function () {
                var subtotal = $scope.model.reqAmount - ($scope.calculateMedicalDebtContribution() || 0);
                return subtotal + (($scope.model.data.lastLoanFee - $scope.calculateNewInterest() - $scope.model.data.lastLoanBalance) || 0);
            };

            $scope.getInterestRate = function () {
                return Requests.getInterestRate($scope.model.type);
            };

            // Sets the bound input to the max possibe request amount
            $scope.setMax = function() {
                $scope.model.reqAmount = $scope.model.maxReqAmount;
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

    $scope.validateRequest = function (ev) {
        Utils.showConfirmDialog(
            'Advertencia',
            'Luego de validar su solicitud no podrá editarla ni eliminarla. ¿Desea continuar?' ,
            'Continuar',
            'Cancelar',
            ev, true).then(
            function() {
                $scope.overlay = true;
                $scope.validating = true;
                Requests.validateRequest($scope.req.id).then(
                    function (date) {
                        $scope.overlay = false;
                        $scope.validating = false;
                        Utils.showAlertDialog('Solicitud validada',
                                              'Su solicitud será atendida en menos de 48 horas hábiles.');
                        $scope.req.validationDate = date;
                    },
                    function (error) {
                        $scope.overlay = false;
                        $scope.validating = false;
                        Utils.handleError(error);
                    }
                );
            });
    };

    /**
     * Custom dialog for updating an existing request (as Agent)
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
                fetchId: fetchId,
                request: $scope.req
            },
            controller: DialogController
        });
        // Isolated dialog controller
        function DialogController($scope, $mdDialog, fetchId, request) {
            $scope.files = [];
            $scope.fetchId = fetchId;
            $scope.uploading = false;
            $scope.request = _.cloneDeep(request);
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
                    function (updatedReq) {
                        Utils.showAlertDialog(
                            'Solicitud actualizada',
                            'La solicitud ha sido actualizada exitosamente.'
                        );
                        // Update saved request and reload view.
                        sessionStorage.setItem("req", JSON.stringify(updatedReq));
                        $state.go($state.current, {}, {reload: true})
                    },
                    function (errorMsg) {
                        $scope.uploading = false;
                        Utils.handleError(errorMsg);
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




    $scope.deleteDoc = function (ev, dKey) {
        Utils.showConfirmDialog(
            'Confirmación de eliminación',
            "El documento " + $scope.req.docs[dKey].name + " será eliminado.",
            'Continuar',
            'Cancelar',
            ev, true).then(
            function() {
                $scope.overlay = true;
                Requests.deleteDocument($scope.req.docs[dKey]).then(
                    function (updatedReq) {
                        $scope.overlay = false;
                        Utils.showAlertDialog(
                            'Documento eliminado',
                            'El documento ' + $scope.req.docs[dKey].name + ' ha sido eliminado exitosamente.'
                        );
                        sessionStorage.setItem("req", JSON.stringify(updatedReq));
                        $state.go($state.current, {}, {reload: true})
                    },
                    function (errorMsg) {
                        $scope.overlay = false;
                        Utils.handleError(errorMsg);
                    }
                )
            }
        );
    };

    $scope.userType = function (type) {
        return Auth.userType(type);
    };

    $scope.loadHistory = function () {
        // Send required data to history
        $state.go('actions');
    };

    $scope.showAgentEditBtn = function () {
        return $scope.req.validationDate && $scope.userType($scope.AGENT) &&
               $scope.req.status != $scope.APPROVED && $scope.req.status != $scope.REJECTED &&
               $scope.req.status != $scope.PRE_APPROVED;
    };

    $scope.showManagerEditBtn = function () {
        return $scope.userType($scope.MANAGER) && $scope.req.status != $scope.APPROVED &&
               $scope.req.status != $scope.REJECTED && $scope.req.status != $scope.PRE_APPROVED;
    };

    $scope.isDocEditable = function (type) {
        return $scope.req.validationDate && !$scope.userType($scope.APPLICANT) &&
               $scope.req.status != $scope.APPROVED && $scope.req.status != $scope.REJECTED &&
               $scope.req.status != $scope.PRE_APPROVED && type != Constants.DocTypes.MANDATORY;
    };

    $scope.loadUserData = function(userId) {
        sessionStorage.setItem("fetchId", userId);
        window.open(Utils.getUserDataUrl(), '_blank');
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
                    function (updatedReq) {
                        sessionStorage.setItem("req", JSON.stringify(updatedReq));
                        $state.go($state.current, {}, {reload: true})
                    },
                    function (errorMsg) {
                        Utils.handleError(errorMsg);
                    }
                );
                $mdDialog.hide();
            }
        }
    };

    /**
     * Custom dialog for updating an existing request (as Manager)
     */
    $scope.openManageRequestDialog = function($event, obj) {
        var parentEl = angular.element(document.body);
        $mdDialog.show({
            parent: parentEl,
            targetEvent: $event,
            templateUrl: 'ManageRequestController',
            clickOutsideToClose: false,
            escapeToClose: false,
            fullscreen: $mdMedia('xs'),
            locals: {
                fetchId: fetchId,
                request: $scope.req,
                parentScope: $scope,
                obj: obj
            },
            controller: DialogController
        });

        // Isolated dialog controller
        function DialogController($scope, $mdDialog, fetchId, request, parentScope, obj) {
            $scope.fetchId = fetchId;
            $scope.uploading = false;
            $scope.request = request;
            $scope.mappedStatuses = Requests.getAllStatuses();
            $scope.APPROVED_STRING = Constants.Statuses.APPROVED;
            $scope.PRE_APPROVED_STRING = Constants.Statuses.PRE_APPROVED;
            $scope.REJECTED_STRING = Constants.Statuses.REJECTED;
            $scope.RECEIVED_STRING = Constants.Statuses.RECEIVED;

            if (obj) {
                $scope.model = obj;
                if (obj.confirmed) performUpdate();
            } else {
                $scope.model = {};
                if ($scope.mappedStatuses.indexOf(request.status) == -1) {
                    $scope.mappedStatuses.push(request.status);
                }
                $scope.model.enabledDescription = -1;
                $scope.model.selectedFiles = null;
                $scope.model.files = [];
                $scope.model.status = request.status;
                $scope.model.comment = $scope.request.comment;
                $scope.model.approvedAmount = $scope.request.reqAmount;
            }

            $scope.closeDialog = function() {
                $mdDialog.hide();
            };

            $scope.showError = function (error, param) {
                return FileUpload.showDocUploadError(error, param);
            };
            $scope.removeDoc = function (index) {
                $scope.model.files.splice(index, 1);
                $scope.model.selectedFiles = $scope.model.files.length > 0 ?
                $scope.model.files.length + ' archivo(s)' : null;
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
            // Gathers the files whenever the file input's content is updated
            $scope.gatherFiles = function (files, errFiles) {
                $scope.model.files = files;
                $scope.model.selectedFiles = $scope.model.files.length > 0 ?
                $scope.model.files.length + ' archivo(s)' : null;
                $scope.errFiles = errFiles;
            };

            // Deletes all selected files
            $scope.deleteFiles = function (ev) {
                $scope.model.files = [];
                $scope.model.selectedFiles = null;
                // Stop click event propagation, otherwise file chooser will
                // also be opened.
                ev.stopPropagation();
            };

            $scope.missingField = function() {
                if ($scope.model.status == $scope.PRE_APPROVED_STRING) {
                    return typeof $scope.model.approvedAmount === "undefined";
                } else {
                    return (($scope.model.status == request.status
                             || $scope.model.status == null) &&
                            (typeof $scope.model.comment === "undefined"
                             || $scope.model.comment == ""
                             || $scope.model.comment == $scope.request.comment)
                            && $scope.model.files.length == 0);
                }
            };

            $scope.loadStatuses = function() {
                return Config.getStatuses().then(
                    function (statuses) {
                        $scope.mappedStatuses = Requests.getAllStatuses();
                        // Delete approved from available statuses.
                        // Pre-approved -> Approved will be done automatically through cron jobs.
                        $scope.mappedStatuses.splice($scope.mappedStatuses.indexOf($scope.APPROVED_STRING), 1);
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
                if ($scope.model.status === $scope.PRE_APPROVED_STRING ||
                    $scope.model.status === $scope.REJECTED_STRING) {
                    confirmClosure(ev);
                } else {
                    performUpdate();
                }
            };
            function performUpdate() {
                $scope.uploading = true;
                if ($scope.model.files.length == 0) {
                    updateRequest();
                } else {
                    // Add additional files to this request.
                    uploadFiles($scope.model.files, fetchId);
                }
            }

            // Uploads each of selected documents to the server
            function uploadFiles(files, userId) {
                FileUpload.uploadFiles(files, userId).then(
                    function (docs) {
                        console.log(docs);
                        $scope.request.newDocs = docs;
                        updateRequest();
                    },
                    function (errorMsg) {
                        // Show file error message
                        Utils.handleError(errorMsg);
                    }
                );
            }

            // Shows a dialog asking user to confirm the request closure.
            function confirmClosure(ev) {
                Utils.showConfirmDialog(
                    'Advertencia',
                    'Al cambiar el estatus de la solicitud a <b>' + $scope.model.status +
                    '</b> no se podrán realizar más cambios. ¿Desea proceder?',
                    'Sí', 'Cancelar', ev, true
                ).then(
                    function () {
                        // Re-open parent dialog and perform request creation
                        $scope.model.confirmed = true;
                        parentScope.openManageRequestDialog(null, $scope.model);
                    },
                    function () {
                        // Re-open parent dialog and do nothing
                        parentScope.openManageRequestDialog(null, $scope.model);
                    }
                );
            }

            // Updates the request.
            function updateRequest() {
                $scope.uploading = true;
                $scope.request.status = $scope.model.status;
                $scope.request.comment = $scope.model.comment;
                $scope.request.reunion = $scope.model.reunion;
                if ($scope.model.status == $scope.PRE_APPROVED_STRING) {
                    $scope.request.approvedAmount = $scope.model.approvedAmount;
                }
                console.log($scope.request);
                Manager.updateRequest($scope.request)
                    .then(
                    function (updatedReq) {
                        Utils.showAlertDialog(
                            'Solicitud actualizada',
                            'La solicitud ha sido actualizada exitosamente.'
                        );
                        // Update saved request and reload view.
                        sessionStorage.setItem("req", JSON.stringify(updatedReq));
                        $state.go($state.current, {}, {reload: true})
                    },
                    function (error) {
                        $scope.uploading = false;
                        Utils.handleError(error);
                    }
                );
            }
        }
    };


    $scope.goHome = function () {
        Auth.sendHome();
    };
}

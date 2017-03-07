angular
    .module('sgdp')
    .controller('ApplicantHomeController', userHome);

userHome.$inject = ['$scope', '$cookies', '$timeout', 'Helps',
                    '$mdSidenav', '$mdDialog', '$mdMedia', 'Constants', 'Requests', 'Utils'];

function userHome($scope, $cookies, $timeout, Helps,
                  $mdSidenav, $mdDialog, $mdMedia, Constants, Requests, Utils) {
    'use strict';
    $scope.loading = true;
    $scope.selectedReq = '';
    $scope.selectedLoan = -1;
    $scope.requests = {};
    $scope.req = null;
    $scope.showList = Requests.initializeListType();
    $scope.fetchError = '';
    // contentAvailable will indicate whether sidenav can be visible
    $scope.contentAvailable = false;
    // contentLoaded will indicate whether sidenav can be locked open
    $scope.contentLoaded = false;
    $scope.listTitle = Requests.getRequestsListTitle();

    var fetchId = $cookies.getObject('session').id;
    $scope.loading = true;
    // Fetch user's requests
    Requests.getUserRequests(fetchId).then(
        function (data) {
            $scope.requests = data;
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

    /**
     * Toggles the selected request type list.
     *
     * @param index - selected request type index.
     */
    $scope.toggleList = function (index) {
        $scope.showList[index] = !$scope.showList[index];
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
        return $scope.req ? Requests.calculatePaymentFee($scope.req.reqAmount, $scope.req.due, 12) : 0;
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
     * @param obj - optional obj containing user input data.
     */
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
            $scope.LOAN_TYPES = Constants.LoanTypes;
            // obj could have a reference to user data, saved
            // before confirmation dialog was opened.
            $scope.model = obj || {due: 24};
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
                        $scope.model.phone = parseInt(data.userPhone, 10);
                        $scope.model.email = data.userEmail;
                        $scope.model.allow = data.granting.allow;
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
            }

            $scope.mapLoanType = function (code) {
                return Requests.mapLoanType(code);
            };

            $scope.missingField = function () {
                return typeof $scope.model.reqAmount === "undefined" ||
                       typeof $scope.model.type === "undefined" ||
                       !$scope.model.phone ||
                       !$scope.model.email;
            };

            $scope.calculatePaymentFee = function() {
                if ($scope.model.reqAmount) {
                    return Requests.calculatePaymentFee($scope.model.reqAmount, $scope.model.due, 12);
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
                                            'Le hemos enviado un correo para realizar validación de su solicitud.<br/>' +
                                            'Si no ha recibido el correo luego de 10 minutos, haga clic en Reenviar.',
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

            $scope.showHelp = function () {
                showFormHelp(Helps.getDialogsHelpOpt());
            };

            /**
             * Shows tour-based help of all input fields.
             * @param options: Obj containing tour.js options
             */
            function showFormHelp(options) {
                var tripToShowNavigation = new Trip([], options);
                if (!$scope.missingField()) {
                    Helps.addFieldHelp(tripToShowNavigation, '#create-btn',
                                       'Haga clic en CREAR para generar la solicitud', 'n');
                    tripToShowNavigation.start();
                } else {
                    showAllFieldsHelp(tripToShowNavigation);
                }
            }

            function showAllFieldsHelp(tripToShowNavigation) {
                var content = '';
                if (!$scope.model.reqAmount) {
                    // Requested amount field
                    content = "Ingrese la cantidad de Bs. que " +
                                  "desea solicitar.";
                    Helps.addFieldHelp(tripToShowNavigation, "#req-amount",
                                  content, 's');
                }
                if (!$scope.model.phone) {
                    // Requested amount field
                    content = "Ingrese su número telefónico, a través " +
                                  "del cual nos estaremos comunicando con usted.";
                    Helps.addFieldHelp(tripToShowNavigation, "#phone-numb",
                                  content, 'n');
                }
                if (!$scope.model.email) {
                    // Email field
                    content = "Ingrese su correo electrónico, a través del cual se le " +
                              "enviará información y actualizaciones referente a su solicitud.";
                    Helps.addFieldHelp(tripToShowNavigation, "#email",
                                       content, 'n');
                }
                // Add payment due help.
                content = "Escoja el plazo (en meses) en el que desea " +
                                "pagar su deuda.";
                Helps.addFieldHelp(tripToShowNavigation, "#payment-due", content, 'n');
                // Add loan type help.
                content = "Escoja el tipo de préstamo que desea solicitar.";
                Helps.addFieldHelp(tripToShowNavigation, "#loan-type", content, 'n');
                tripToShowNavigation.start();
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
            $scope.LOAN_TYPES = Constants.LoanTypes;

            // obj could have a reference to user data, saved
            // before confirmation dialog was opened.
            var model = {
                reqAmount: request.reqAmount,
                type: request.type,
                due: request.due,
                phone: parseInt(request.phone),
                email: request.email
            };
            console.log(request.type);
            $scope.model = obj || model;
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
                        $scope.model.opened = Requests.checkPreviousRequests(requests);
                        // On-edition request should not be disabled (as we know it's still open)
                        $scope.model.opened.hasOpen[request.type] = false;
                        $scope.loading = false;
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

            $scope.mapLoanType = function (code) {
                return Requests.mapLoanType(code);
            };

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
                    return Requests.calculatePaymentFee($scope.model.reqAmount, $scope.model.due, 12);
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
                                            'Hemos reenviado el correo de validación con los datos actualizados.<br/>' +
                                            'Si no recibe el correo dentro de unos pocos minutos, ' +
                                            'por favor haga clic en Reenviar.',
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
                    'Se guardarán los cambios que haya realizado a su solicitud y se reenviará el correo de ' +
                    'validación con los datos actualizados. ¿Desea proceder?',
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

            $scope.showHelp = function () {
                showFormHelp(Helps.getDialogsHelpOpt());
            };

            /**
             * Shows tour-based help of all input fields.
             * @param options: Obj containing tour.js options
             */
            function showFormHelp(options) {
                var tripToShowNavigation = new Trip([], options);
                if (!$scope.missingField()) {
                    Helps.addFieldHelp(tripToShowNavigation, '#create-btn',
                                       'Haga clic en CREAR para generar la solicitud', 'n');
                    tripToShowNavigation.start();
                } else {
                    showAllFieldsHelp(tripToShowNavigation);
                }
            }

            function showAllFieldsHelp(tripToShowNavigation) {
                var content = '';
                if (!$scope.model.reqAmount) {
                    // Requested amount field
                    content = "Ingrese la cantidad de Bs. que " +
                              "desea solicitar.";
                    Helps.addFieldHelp(tripToShowNavigation, "#req-amount",
                                       content, 's');
                }
                if (!$scope.model.phone) {
                    // Requested amount field
                    content = "Ingrese su número telefónico, a través " +
                              "del cual nos estaremos comunicando con usted.";
                    Helps.addFieldHelp(tripToShowNavigation, "#phone-numb",
                                       content, 'n');
                }
                if (!$scope.model.email) {
                    // Email field
                    content = "Ingrese su correo electrónico, a través del cual se le " +
                              "enviará información y actualizaciones referente a su solicitud.";
                    Helps.addFieldHelp(tripToShowNavigation, "#email",
                                       content, 'n');
                }
                // Add payment due help.
                content = "Escoja el plazo (en meses) en el que desea " +
                          "pagar su deuda.";
                Helps.addFieldHelp(tripToShowNavigation, "#payment-due", content, 'n');
                // Add loan type help.
                content = "Escoja el tipo de préstamo que desea solicitar.";
                Helps.addFieldHelp(tripToShowNavigation, "#loan-type", content, 'n');
                tripToShowNavigation.start();
            }
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

    $scope.sendValidation = function() {
        $scope.sending = true;
        Requests.sendValidation($scope.req.id)
            .then(
            function () {
                $scope.sending = false;
                Utils.showAlertDialog('Validación reenviada!',
                                      'Hemos reenviado el correo de validación de su solicitud de forma exitosa.');
            },
            function (errorMsg) {
                $scope.sending = false;
                Utils.showAlertDialog('Oops!', errorMsg);
            }
        )
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
                Requests.deleteRequestUI($scope.req).then(
                    function () {
                        // Update interface
                        $scope.req = null;
                        updateRequestListUI(fetchId, -1, 'Solicitud eliminada',
                                            'La solicitud fue eliminada exitosamente.',
                                            true, true, -1);
                    },
                    function (errorMsg) {
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
                var loanType = Requests.mapLoanTypeAsCode(type);
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
                Utils.showAlertDialog(dialogTitle, dialogContent);
            },
            function (errorMsg) {
                console.log("REFRESHING ERROR!");
                console.log(errorMsg);
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
            $scope.showList[index] = true;
        }, 1000);

    }

    function closeAllReqList() {
        $scope.selectedReq = '';
        $scope.selectedLoan = -1;
        angular.forEach($scope.showList, function(show, index) {
            $scope.showList[index] = false;
        });
    }

    // Helper function for formatting numbers with leading zeros
    $scope.pad = function (n, width, z) {
        return Utils.pad(n, width, z);
    };

    $scope.downloadDoc = function (doc) {
        window.open(Requests.getDocDownloadUrl(doc.lpath));
    };

    $scope.downloadAll = function () {
        location.href = Requests.getAllDocsDownloadUrl($scope.req.docs);
    };

    $scope.openMenu = function () {
        $mdSidenav('left').toggle();
    };


    $scope.showHelp = function () {
        if (!$scope.req) {
            // User has not selected any request yet, tell him to do it.
            showSidenavHelp(Helps.getDialogsHelpOpt());
        } else {
            // Guide user through request selection's possible actions
            showRequestHelp(Helps.getDialogsHelpOpt());
        }
    };

    /**
     * Shows tour-based help of side navigation panel
     * @param options: Obj containing tour.js options
     */

    function showSidenavHelp(options) {
        var responsivePos = $mdMedia('xs') ? 'n' : 'w';
        var tripToShowNavigation = new Trip([], options);
        var content;
        if ($mdSidenav('left').isLockedOpen() && Requests.getTotalLoans($scope.requests) > 0) {
            options.showHeader = true;
            content = "Seleccione alguna de sus solicitudes en la lista para ver más detalles.";
            Helps.addFieldHelpWithHeader(tripToShowNavigation, '#requests-list', content, 'e',
                                         'Panel de navegación', true);
            content = "También puede crear una solicitud haciendo click aquí";
            Helps.addFieldHelpWithHeader(tripToShowNavigation, '#new-req-fab', content, responsivePos,
                                         'Crear solicitud', true);
            tripToShowNavigation.start();
        } else if ($scope.contentLoaded && Requests.getTotalLoans($scope.requests) > 0) {
            content = "Haga click en el ícono para abrir el panel de navegación y seleccionar alguna " +
                      "de sus solicitudes para ver más detalles";
            Helps.addFieldHelp(tripToShowNavigation, '#nav-panel', content, 's', true);
            content = "También puede crear una solicitud haciendo click aquí";
            Helps.addFieldHelp(tripToShowNavigation, '#new-req-fab', content, responsivePos, true);
            tripToShowNavigation.start();
        } else {
            options.showHeader = true;
            content = "Para crear una solicitud haga click aquí";
            Helps.addFieldHelpWithHeader(tripToShowNavigation, '#new-req-fab', content, responsivePos,
                                         'Crear solicitud');
            tripToShowNavigation.start();
        }
    }

    /**
     * Shows tour-based help of selected request details section.
     * @param options: Obj containing tour.js options
     */
    function showRequestHelp(options) {
        options.showHeader = true;
        var responsivePos = $mdMedia('xs') ? 's' : 'w';
        var tripToShowNavigation = new Trip([], options);
        var content;
        // Validation help
        if (!$scope.req.validationDate) {
            content = "Debe validar su solicitud a través del correo enviado al correo electrónico provisto. " +
                      "Si no ha recibido el correo dentro de unos minutos, por favor haga clic en Reenviar." +
                      "También puede cambiar la dirección del correo electrónico haciendo clic en \"Cambiar Correo\".";
            Helps.addFieldHelpWithHeader(tripToShowNavigation, '#validation-card', content, 's',
                                         'Validación de solicitud', true);
        }
        // Request summary information
        content = "Aquí se muestra información acerca de la fecha de creación, monto solicitado " +
                  "por usted, y un posible comentario.";
        Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-summary', content, 's',
                                     'Resumen de la solicitud', true);
        // Request status information
        content = "Esta sección provee información acerca del estatus de su solicitud.";
        Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-status-summary', content, 's',
                                     'Resumen de estatus', true);
        // Request payment due information
        content = "Acá puede apreciar las cuotas a pagar, indicando el monto por mes y el plazo del pago en meses.";
        Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-payment-due', content, 's',
                                     'Cuotas a pagar', true);
        // Request contact number
        content = "Aquí se muestra el número de teléfono que ingresó al crear la solicitud, a través del cual " +
                  "lo estaremos contactando.";
        Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-contact-number', content, 'n',
                                     'Número de contacto', true);
        // Request contact email
        content = "Éste es el correo electrónico que ingresó al crear la solicitud, a través del cual " +
                  "le enviaremos información y actualizaciones referente a su solicitud.";
        Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-email', content, 'n',
                                     'Correo electrónico', true);
        // Request documents information
        content = "Éste y los siguientes " +
                  "items contienen el nombre y una posible descripción de " +
                  "cada documento en su solicitud. Puede verlos/descargarlos " +
                  "haciendo click encima de ellos.";
        Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-docs', content, 'n',
                                     'Documentos', true);
        if ($mdSidenav('left').isLockedOpen()) {
            if (!$scope.req.validationDate) {
                content = "También puede editar la información de su solicitud descargar todos los " +
                          "documentos, o eliminarla presionando el botón correspondiente.";
            } else {
                content = "También puede descargar todos los documentos haciendo click aquí.";
            }
            Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-summary-actions', content, responsivePos,
                                         'Acciones', true, 'fadeInLeft');
        } else {
            if (!$scope.req.validationDate) {
                content = "También puede hacer clic en el botón de opciones para " +
                          "editar la información de su solicitud, o descargar todos los " +
                          "documentos, o eliminarla presionando el botón correspondiente.";
            } else {
                content = "También puede hacer clic en el botón de opciones para " +
                          "descargar todos los documentos presionando el botón correspondiente.";
            }
            Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-summary-actions-menu',
                                         content, responsivePos,
                                         'Acciones', true, 'fadeInLeft');
        }
        tripToShowNavigation.start();
    }
}

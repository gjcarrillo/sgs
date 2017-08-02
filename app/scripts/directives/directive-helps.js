var app = angular.module('sgdp.directive-helps', []);

/**
 * The following help directives are meant to be tied to a specific scope.
 */
app.directive('applicantHelp', function(Helps, Requests, $mdMedia, $mdSidenav) {
    return {
        restrict: 'A',
        link: function ($scope, elem) {
            $scope.showHelp = function () {
                if ($scope.showWatermark()) {
                    // User has not selected any action yet, tell him to do it.
                    showSidenavHelp(Helps.getDialogsHelpOpt());
                } else {
                    // Guide user through request selection's possible actions
                    showActionsHelp(Helps.getDialogsHelpOpt());
                }
            };

            /**
             * Shows tour-based help of side navigation panel
             * @param options: Obj containing tour.js options
             */

            function showSidenavHelp(options) {
                var responsivePos = $mdMedia('xs') ? 'n' : 'e';
                var tripToShowNavigation = new Trip([], options);
                var content;
                if ($mdSidenav('left').isLockedOpen()) {
                    options.showHeader = true;
                    content = "Haga clic aquí si desea ver sus datos.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#user-data', content, 'e',
                                                 'Ver datos', false);
                    content = "Consulte las solicitudes realizadas haciendo clic aquí.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#query', content, 'e',
                                                 'Consultar solicitudes', false);
                    content = "Solicite un nuevo préstamo haciendo clic aquí";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#new-request', content, responsivePos,
                                                 'Crear solicitud', false);
                    content = "Puede editar las solicitudes que aún no estén validadas haciendo clic aquí.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#edit-request', content, responsivePos,
                                                 'Editar solicitudes', false);
                    tripToShowNavigation.start();
                } else if ($scope.contentLoaded) {
                    content = "Haga clic en el ícono para abrir el panel de navegación para ver los datos de su cuenta, " +
                              "consultar, editar o crear solicitudes";
                    Helps.addFieldHelp(tripToShowNavigation, '#nav-panel', content, 's', false);
                    tripToShowNavigation.start();
                }
            }

            /**
             * Shows tour-based help of selected action.
             * @param options: Obj containing tour.js options
             */
            function showActionsHelp(options) {
                options.showHeader = true;
                var tripToShowNavigation = new Trip([], options);
                var content;

                switch ($scope.selectedAction) {
                    case 2:
                        // request by id help.
                        showRequestByIdHelp();
                        break;
                    case 3:
                        // requests by date help.
                        showRequestByDateHelp();
                        break;
                    case 4:
                        // requests by status help.
                        showRequestByStatusHelp();
                        break;
                    case 5:
                        // requests by type help.
                        showRequestByTypeHelp();
                        break;
                }
                showResultHelp();

                function showResultHelp() {
                    if (!$scope.isObjEmpty($scope.requests)) {
                        content =
                            "A continuación se muestra un panel con todas sus solciitudes resultantes, " +
                            "categorizadas por los tipos de solicitud disponibles por el sistema.<br/>" +
                            "Para ver una lista de solicitudes, haga clic encima del tipo de préstamo correspondiente.<br/>" +
                            "Para ver los detalles de una solicitud en particular, haga clic encima de la fila correspondiente.";
                        Helps.addFieldHelpWithHeader(tripToShowNavigation, '#requests-group', content, 's',
                                                     'Panel de solicitudes');
                        tripToShowNavigation.start();
                    } else if ($scope.singleType.length > 0) {
                        content = "A continuación se una tabla listando todas sus solicitudes del tipo de préstamo especificado.<br/>" +
                                  "Puede hacer ver los detalles de una solicitud haciendo clic encima de la fila correspondiente.";
                        Helps.addFieldHelpWithHeader(tripToShowNavigation, '#single-type', content, 'w',
                                                     'Lista de solicitudes');
                        tripToShowNavigation.start();
                    } else if ($scope.editableReq.length > 0) {
                        content = "A continuación se una tabla listando todas sus solicitudes editables (que aún no han sido validadas).<br/>" +
                                  "Puede editar o eliminar una solicitud haciendo clic en los botones correspondientes, o ver sus detalles " +
                                  "haciendo clic encima de la fila correspondiente.";
                        Helps.addFieldHelpWithHeader(tripToShowNavigation, '#editable-req', content, 'w',
                                                     'Solicitudes editables');
                        tripToShowNavigation.start();
                    } else if ($scope.activeRequests.length > 0) {
                        content = "A continuación se una tabla listando todas sus solicitudes activas (cuya deuda sigue " +
                                  "vigente y está registrada en su Estado de Cuenta).<br/>" +
                                  "Puede visualizar detalles correspondientes a su saldo y mensualidad, o ver sus detalles " +
                                  "haciendo clic encima de la fila correspondiente.";
                        Helps.addFieldHelpWithHeader(tripToShowNavigation, '#active-req', content, 'w',
                                                     'Solicitudes activas.');
                        tripToShowNavigation.start();
                    }
                }

                function showRequestByIdHelp() {
                    content = "Ingrese el ID de la solicitud que desea consultar.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#req-id', content, 's',
                                                 'ID de la solicitud');
                    tripToShowNavigation.start();
                }

                function showRequestByDateHelp() {
                    content = "Ingrese una fecha de creación como punto de inicio de la búsqueda.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#date-from', content, 's',
                                                 'Intervalo de fecha');
                    tripToShowNavigation.start();
                    content = "Ingrese una fecha de creación como punto final de la búsqueda.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#date-to', content, 's',
                                                 'Intervalo de fecha');
                    tripToShowNavigation.start();
                }

                function showRequestByStatusHelp() {
                    content = "Seleccione el estatus de las solicitudes que desea consultar.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#req-status', content, 's',
                                                 'Estatus de las solicitudes');
                    tripToShowNavigation.start();
                }

                function showRequestByTypeHelp() {
                    content = "Seleccione el tipo de solicitud de aquellas que desea consultar.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#req-type', content, 's',
                                                 'Tipo de solicitudes');
                    tripToShowNavigation.start();
                }
            }
        }
    };
});

app.directive('reviserHelp', function(Helps) {

});

app.directive('agentHelp', function(Helps, Requests, $mdMedia, $mdSidenav) {
    return {
        restrict: 'A',
        link: function ($scope, elem) {
            $scope.showHelp = function () {
                if (!$scope.contentAvailable) {
                    // Indicate user to input another user's ID.
                    if ($mdMedia('gt-sm')) {
                        showSearchbarHelp(Helps.getDialogsHelpOpt());
                    } else {
                        showMobileSearchbarHelp(Helps.getDialogsHelpOpt());
                    }
                } else if ($scope.showWatermark()) {
                    // User has not selected any request yet, tell him to do it.
                    showSidenavHelp(Helps.getDialogsHelpOpt());
                } else {
                    // Guide user through request selection's possible actions.
                    showActionsHelp(Helps.getDialogsHelpOpt());
                }
            };

            /**
             * Shows tour-based help of searchbar
             * @param options: Obj containing tour.js options
             */
            function showSearchbarHelp(options) {
                var tripToShowNavigation = new Trip([], options);
                Helps.addFieldHelp(tripToShowNavigation, '#search',
                                   'Ingrese la cédula de identidad de algún asociado para gestionar sus solicitudes.', 's');
                tripToShowNavigation.start();
            }

            /**
             * Shows tour-based help of mobile searchbar
             * @param options: Obj containing tour.js options
             */
            function showMobileSearchbarHelp(options) {
                var pos = $mdMedia('gt-sm') ? 'w' : 's';
                var tripToShowNavigation = new Trip([], options);
                Helps.addFieldHelp(tripToShowNavigation, '#toggle-search',
                                   'Haga clic en la lupa e ingrese la cédula de identidad ' +
                                   'de algún asociado para gestionar sus solicitudes.', pos);
                tripToShowNavigation.start();
            }

            /**
             * Shows tour-based help of side navigation panel
             * @param options: Obj containing tour.js options
             */

            function showSidenavHelp(options) {
                var responsivePos = $mdMedia('xs') ? 'n' : 'e';
                var tripToShowNavigation = new Trip([], options);
                var content;
                if ($mdSidenav('left').isLockedOpen()) {
                    options.showHeader = true;
                    content = "Haga clic aquí si desea ver los datos del asociado.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#user-data', content, 'e',
                                                 'Ver datos', false);
                    content = "Consulte las solicitudes realizadas haciendo clic aquí.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#query', content, 'e',
                                                 'Consultar solicitudes', false);
                    content = "Solicite un nuevo préstamo por el asociado haciendo clic aquí";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#new-request', content, responsivePos,
                                                 'Crear solicitud', false);
                    content = "Puede editar las solicitudes que aún no estén validadas haciendo clic aquí.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#edit-request', content, responsivePos,
                                                 'Editar solicitudes', false);
                    tripToShowNavigation.start();
                } else if ($scope.contentLoaded) {
                    content = "Haga clic en el ícono para abrir el panel de navegación para ver los datos del asociado, " +
                              "consultar, editar o crear solicitudes";
                    Helps.addFieldHelp(tripToShowNavigation, '#nav-panel', content, 's', false);
                    tripToShowNavigation.start();
                }
            }

            /**
             * Shows tour-based help of selected action.
             * @param options: Obj containing tour.js options
             */
            function showActionsHelp(options) {
                options.showHeader = true;
                var tripToShowNavigation = new Trip([], options);
                var content;

                switch ($scope.selectedAction) {
                    case 2:
                        // request by id help.
                        showRequestByIdHelp();
                        break;
                    case 3:
                        // requests by date help.
                        showRequestByDateHelp();
                        break;
                    case 4:
                        // requests by status help.
                        showRequestByStatusHelp();
                        break;
                    case 5:
                        // requests by type help.
                        showRequestByTypeHelp();
                        break;
                }
                showResultHelp();

                function showResultHelp() {
                    if (!$scope.isObjEmpty($scope.requests)) {
                        content =
                            "A continuación se muestra un panel con todas las solciitudes resultantes, " +
                            "categorizadas por los tipos de solicitud disponibles por el sistema.<br/>" +
                            "Para ver una lista de solicitudes, haga clic encima del tipo de préstamo correspondiente.<br/>" +
                            "Para ver los detalles de una solicitud en particular, haga clic encima de la fila correspondiente.";
                        Helps.addFieldHelpWithHeader(tripToShowNavigation, '#requests-group', content, 's',
                                                     'Panel de solicitudes');
                        tripToShowNavigation.start();
                    } else if ($scope.singleType.length > 0) {
                        content = "A continuación se una tabla listando todas las solicitudes del tipo de préstamo especificado.<br/>" +
                                  "Puede hacer ver los detalles de una solicitud haciendo clic encima de la fila correspondiente.";
                        Helps.addFieldHelpWithHeader(tripToShowNavigation, '#single-type', content, 'w',
                                                     'Lista de solicitudes');
                        tripToShowNavigation.start();
                    } else if ($scope.editableReq.length > 0) {
                        content = "A continuación se una tabla listando todas las solicitudes editables (que aún no han sido validadas).<br/>" +
                                  "Puede editar o eliminar una solicitud haciendo clic en los botones correspondientes, o ver sus detalles " +
                                  "haciendo clic encima de la fila correspondiente.";
                        Helps.addFieldHelpWithHeader(tripToShowNavigation, '#editable-req', content, 'w',
                                                     'Solicitudes editables');
                        tripToShowNavigation.start();
                    } else if ($scope.activeRequests.length > 0) {
                        content = "A continuación se una tabla listando todas las solicitudes activas (cuya deuda sigue " +
                                  "vigente y está registrada en el Estado de Cuenta del asociado).<br/>" +
                                  "Puede visualizar detalles correspondientes a su saldo y mensualidad, o ver sus detalles " +
                                  "haciendo clic encima de la fila correspondiente.";
                        Helps.addFieldHelpWithHeader(tripToShowNavigation, '#active-req', content, 'w',
                                                     'Solicitudes activas.');
                        tripToShowNavigation.start();
                    }
                }

                function showRequestByIdHelp() {
                    content = "Ingrese el ID de la solicitud que desea consultar.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#req-id', content, 's',
                                                 'ID de la solicitud');
                    tripToShowNavigation.start();
                }

                function showRequestByDateHelp() {
                    content = "Ingrese una fecha de creación como punto de inicio de la búsqueda.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#date-from', content, 's',
                                                 'Intervalo de fecha');
                    tripToShowNavigation.start();
                    content = "Ingrese una fecha de creación como punto final de la búsqueda.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#date-to', content, 's',
                                                 'Intervalo de fecha');
                    tripToShowNavigation.start();
                }

                function showRequestByStatusHelp() {
                    content = "Seleccione el estatus de las solicitudes que desea consultar.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#req-status', content, 's',
                                                 'Estatus de las solicitudes');
                    tripToShowNavigation.start();
                }

                function showRequestByTypeHelp() {
                    content = "Seleccione el tipo de solicitud de aquellas que desea consultar.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#req-type', content, 's',
                                                 'Tipo de solicitudes');
                    tripToShowNavigation.start();
                }
            }
        }
    };
});

app.directive('detailsHelp', function(Helps, $mdMedia, Auth, Constants) {
    return {
        restrict: 'A',
        link: function ($scope, elem) {
            $scope.showHelp = function () {
                showRequestDetailsHelp(Helps.getDialogsHelpOpt());
            };

            /**
             * Shows tour-based help of selected request details section.
             * @param options: Obj containing tour.js options
             */
            function showRequestDetailsHelp(options) {
                options.showHeader = true;
                var responsivePos = $mdMedia('xs') ? 's' : 'w';
                var responsiveNorthPos = $mdMedia('xs') ? 'n' : 'w';
                var responsiveNSPos = $mdMedia('xs') ? 'n' : 's';
                var tripToShowNavigation = new Trip([], options);
                var content;
                // Validation help
                if (!$scope.req.validationDate) {
                    if (Auth.userType(Constants.Users.AGENT)) {
                        content = "Esta solicitud no ha sido validada.<br/> " +
                                  "El asociado debe ingresar con sus credenciales y realizar la correspondiente validación";
                    } else if (Auth.userType(Constants.Users.APPLICANT)) {
                        content = "Esta solicitud no ha sido validada.<br/> " +
                                  "Una vez esté completamente seguro de proceder con esta solicitud, por favor haga clic en " +
                                  "el botón \"VALIDAR\".";
                    }
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#validation-card', content, 's',
                                                 'Validación de solicitud', true);
                }
                // Request summary information
                content = "Aquí se muestra información acerca de la fecha de creación, monto solicitado," +
                          " y un posible comentario.";
                Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-summary', content, 's',
                                             'Resumen de la solicitud', true);
                // Request status information
                content = "Esta sección provee información acerca del estatus de la solicitud.";
                Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-status-summary', content, responsiveNSPos,
                                             'Resumen de estatus', true);
                // Request validation date
                if ($scope.req.validationDate) {
                    content = "A continuación se muestra la fecha en la se realizó la validación de la solicitud.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-validation-date', content, responsiveNSPos,
                                                 'Fecha de validación', true);
                }
                // Request payment due information
                content = "Acá puede apreciar las cuotas a pagar mensualmente, indicando el monto por mes y el plazo " +
                          "del pago en meses.";
                Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-payment-due', content, 'n',
                                             'Cuotas a pagar', true);
                // Request contact number
                content = "Aquí se muestra el número de teléfono que se ingresó al crear la solicitud, a través del cual " +
                          "lo estaremos contactando.";
                Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-contact-number', content, 'n',
                                             'Número de contacto', true);
                // Request contact email
                content = "Éste es el correo electrónico que se ingresó al crear la solicitud, a través del cual " +
                          "le enviaremos información y actualizaciones referente a su solicitud.";
                Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-email', content, 'n',
                                             'Correo electrónico', true);
                // Request documents information
                content = "Éste y los siguientes " +
                          "items contienen el nombre y una posible descripción de " +
                          "cada documento en su solicitud. Puede verlos/descargarlos " +
                          "haciendo clic encima de ellos.";
                Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-docs', content, 'n',
                                             'Documentos', true);
                if (!Auth.userType(Constants.Users.APPLICANT) && existsAdditionalDoc($scope.req.docs)) {
                    content = "Siendo un documento adicional, " +
                              "puede hacer clic en el botón de opciones para proveer una descripción, " +
                              "descargarlos o eliminarlos.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-docs-actions', content, responsiveNorthPos,
                                                 'Documentos', true, 'fadeInLeft');
                }
                content = getDetailsOptionHelpContent();
                Helps.addFieldHelpWithHeader(tripToShowNavigation,
                                             $mdMedia('xs') ? '#request-summary-actions-menu' : '#request-summary-actions',
                                             content, responsivePos,
                                             'Acciones', true, 'fadeInLeft');
                if ($scope.req.deductions) {
                    content = 'En la siguiente tabla se muestran las deducciones adicionales solicitadas durante la ' +
                              'creación de la solicitud para pagar deudas de otros préstamos.';
                    Helps.addFieldHelpWithHeader(tripToShowNavigation,
                                                 '#deductions', content, responsivePos, 'Deducciones adicionales', true, 'fadeInLeft');
                }
                tripToShowNavigation.start();
            }

            function getDetailsOptionHelpContent() {
                var content = 'También puede ';
                if ($mdMedia('xs')) {
                    content += 'hacer clic en el botón de opciones para ';
                }
                if (Auth.userType($scope.APPLICANT)) {
                    if (!$scope.req.validationDate) {
                        content += "editar la información de su solicitud, descargar todos los " +
                                  "documentos, o eliminarla presionando el botón correspondiente.";
                    } else {
                        content += "descargar todos los documentos presionando el botón correspondiente.";
                    }
                } else if (Auth.userType($scope.AGENT)) {
                    if (!$scope.req.validationDate) {
                        content += "ver los datos del " +
                                  "asociado, editar la información de la solicitud, ver el historial de acciones, " +
                                  "descargar todos los documentos, o eliminarla presionando el botón correspondiente.";
                    } else {
                        content += "ver los datos del asociado, gestionar la información de la solicitud, ver " +
                                  "el historial de acciones, o descargar todos los documentos.";
                    }
                } else if (Auth.userType($scope.MANAGER)) {
                    content += "ver los datos del asociado, gestionar la información de la solicitud, ver el " +
                              "historial de acciones, o descargar todos los documentos.";
                }
                return content;
            }

            function existsAdditionalDoc(docs) {
                var exists = false;
                for (var key in docs) {
                    if (docs.hasOwnProperty(key)) {
                        if (docs[key].type == Constants.DocTypes.ADDITIONAL) {
                            exists = true;
                            break;
                        }
                    }
                }
                return exists;
            }

        }
    };
});

app.directive('agentUpdateHelp', function(Helps) {
    return {
        restrict: 'A',
        link: function ($scope) {
            $scope.showHelp = function () {
                showFormHelp(Helps.getDialogsHelpOpt());
            };

            /**
             * Shows tour-based help of all input fields.
             * @param options: Obj containing tour.js options
             */
            function showFormHelp(options) {
                var content;
                var tripToShowNavigation = new Trip([], options);
                if (typeof $scope.comment === "undefined" || $scope.comment == ""
                    || $scope.comment == $scope.request.comment) {
                    content = "Puede (opcionalmente) realizar algún comentario " +
                              "hacia la solicitud.";
                    Helps.addFieldHelp(tripToShowNavigation, "#comment", content, 's');
                }
                if ($scope.files.length == 0) {
                    content = "Haga clic para para (opcionalmente) agregar documentos " +
                              "adicionales a la solicitud.";
                    Helps.addFieldHelp(tripToShowNavigation, "#more-files", content, 's');
                } else {
                    content = "Estas tarjetas contienen el nombre y posible descripción " +
                              "de los documentos seleccionados. Puede eliminarla o proporcionar una descripción" +
                              " a través de los íconos en la parte inferior de la tarjeta.";
                    Helps.addFieldHelp(tripToShowNavigation, "#file-card", content, 'n');
                }
                if (!$scope.allFieldsMissing()) {
                    content = "Haga clic en ACTUALIZAR para guardar los cambios.";
                    Helps.addFieldHelp(tripToShowNavigation, "#edit-btn", content, 'n');
                }
                tripToShowNavigation.start();
            }
        }
    };
});

/**
 * Creation & edition help for both AGENTS & APPLICANTS
 */
app.directive('createHelp', function(Helps, Auth, Constants) {
    return {
        restrict: 'A',
        link: function ($scope) {
            $scope.showHelp = function () {
                if ($scope.confirmButton == 'Crear') {
                    if (Auth.userType(Constants.Users.APPLICANT)) {
                        showApplicantCreateFormHelp(Helps.getDialogsHelpOpt())
                    } else {
                        showAgentCreateFormHelp(Helps.getDialogsHelpOpt())
                    }
                } else {
                    if (Auth.userType(Constants.Users.APPLICANT)) {
                        showApplicantEditFormHelp(Helps.getDialogsHelpOpt())
                    } else {
                        showAgentEditFormHelp(Helps.getDialogsHelpOpt())
                    }
                }
            };
            /**
             * Shows tour-based help of all input fields.
             * @param options: Obj containing tour.js options
             */
            function showAgentCreateFormHelp(options) {
                var trip = new Trip([], options);
                if (!$scope.missingField()) {
                    // Tell user to hit the create button
                    Helps.addFieldHelp(trip, '#create-btn',
                                       'Haga clic en CREAR para generar la solicitud.', 'n');
                    trip.start();
                } else {
                    showAllCreateAgentFieldsHelp(trip);
                }
            }

            function showAllCreateAgentFieldsHelp(tripToShowNavigation) {
                var content;
                if (!$scope.model.reqAmount) {
                    // Requested amount field
                    content = "Ingrese la cantidad de Bs. solicitado por el asociado.";
                    Helps.addFieldHelp(tripToShowNavigation, "#req-amount", content, 's');
                }
                if (!$scope.model.phone) {
                    // Phone number field
                    content = "Ingrese el número telefónico del asociado, a través " +
                              "del cual se le estará contactando.";
                    Helps.addFieldHelp(tripToShowNavigation, "#phone-numb",
                                       content, 'n');
                }
                if (!$scope.model.email) {
                    // Email field
                    content = "Ingrese su correo electrónico, a través del cual se le " +
                              "enviará información y actualizaciones referente a la solicitud.";
                    Helps.addFieldHelp(tripToShowNavigation, "#email",
                                       content, 'n');
                }
                // Add payment due help.
                content = "Escoja el plazo (en meses) en el que el asociado desea " +
                          "pagar su deuda.";
                Helps.addFieldHelp(tripToShowNavigation, "#payment-due", content, 'n');
                // info card help
                content = "Estas tarjetas muestran información de interés con respecto al monto máximo que el asociado " +
                          "puede solicitar y el monto máximo que se le será otorgado.";
                Helps.addFieldHelp(tripToShowNavigation, "#info", content, 'n');
                tripToShowNavigation.start();
            }

            /**
             * Shows tour-based help of all input fields.
             * @param options: Obj containing tour.js options
             */
            function showApplicantCreateFormHelp(options) {
                var tripToShowNavigation = new Trip([], options);
                if (!$scope.missingField()) {
                    Helps.addFieldHelp(tripToShowNavigation, '#create-btn',
                                       'Haga clic en CREAR para generar la solicitud', 'n');
                    tripToShowNavigation.start();
                } else {
                    showAllApplicantCreateFieldsHelp(tripToShowNavigation);
                }
            }

            function showAllApplicantCreateFieldsHelp(tripToShowNavigation) {
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
                // Info help
                content = "Estas tarjetas muestras información de interés con respecto al monto máximo que usted puede " +
                          "solicitar y el monto máximo que se le será otorgado.";
                Helps.addFieldHelp(tripToShowNavigation, "#info", content, 'n');
                // Add loan type help.
                tripToShowNavigation.start();
            }

            /**
             * Shows tour-based help of all input fields.
             * @param options: Obj containing tour.js options
             */
            function showAgentEditFormHelp(options) {
                var tripToShowNavigation = new Trip([], options);
                if (!$scope.missingField()) {
                    Helps.addFieldHelp(tripToShowNavigation, '#create-btn',
                                       'Haga clic en EDITAR para editar la solicitud', 'n');
                    tripToShowNavigation.start();
                } else {
                    showAllAgentEditFieldsHelp(tripToShowNavigation);
                }
            }

            function showAllAgentEditFieldsHelp(tripToShowNavigation) {
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
                    content = "Ingrese el número telefónico, a través " +
                              "del cual nos comunicaremos con el asociado.";
                    Helps.addFieldHelp(tripToShowNavigation, "#phone-numb",
                                       content, 'n');
                }
                if (!$scope.model.email) {
                    // Email field
                    content = "Ingrese el correo electrónico, a través del cual se le " +
                              "enviará al asociado información y actualizaciones referente a su solicitud.";
                    Helps.addFieldHelp(tripToShowNavigation, "#email",
                                       content, 'n');
                }
                // Add payment due help.
                content = "Escoja el plazo (en meses) en el que el asociado desea " +
                          "pagar su deuda.";
                Helps.addFieldHelp(tripToShowNavigation, "#payment-due", content, 'n');
                // info card help
                content = "Estas tarjetas muestran información de interés con respecto al monto máximo que el asociado " +
                          "puede solicitar y el monto máximo que se le será otorgado.";
                Helps.addFieldHelp(tripToShowNavigation, "#info", content, 'n');
                tripToShowNavigation.start();
            }

            /**
             * Shows tour-based help of all input fields.
             * @param options: Obj containing tour.js options
             */
            function showApplicantEditFormHelp(options) {
                var tripToShowNavigation = new Trip([], options);
                if (!$scope.missingField()) {
                    Helps.addFieldHelp(tripToShowNavigation, '#create-btn',
                                       'Haga clic en EDITAR para editar la solicitud', 'n');
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
                // info card help
                content = "Estas tarjetas muestran información de interés con respecto al monto máximo que usted " +
                          "puede solicitar y el monto máximo que se le será otorgado.";
                Helps.addFieldHelp(tripToShowNavigation, "#info", content, 'n');
                tripToShowNavigation.start();
            }
        }
    }
});


app.directive('managerHelp', function(Helps, $mdSidenav) {
    return {
        restrict: 'A',
        link: function ($scope) {
            $scope.showHelp = function() {
                if ($scope.showWatermark()) {
                    // User has not selected any request yet, tell him to do it.
                    showSidenavHelp(Helps.getDialogsHelpOpt());
                } else {
                    // Guide user through possible actions.
                    if ($scope.showResult != null && $scope.pieloaded) {
                        // Show result help
                        if ($scope.showResult == 0) {
                            // Single user query result help
                            showSingleUserResultHelp(Helps.getDialogsHelpOpt());
                        } else {
                            showMultipleUsersResultHelp(Helps.getDialogsHelpOpt());
                        }
                    } else if ($scope.showResult == null && $scope.selectedAction != 'pending') {
                        showActionsHelp(Helps.getDialogsHelpOpt());
                    } else {
                        showResultHelp(Helps.getDialogsHelpOpt());
                    }
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
                              "las solicitudes del asociado en base a los estatus. Los datos aparecen al " +
                              "mover el ratón hacia alguna de las divisiones de la gráfica.";
                Helps.addFieldHelpWithHeader(trip, '#piechart-tour', content, 'n', 'Estadísticas', true);
                content = "Puede generar un reporte detallado haciendo clic aquí.";
                Helps.addFieldHelpWithHeader(trip, '#report-btn', content, 's', 'Generación de reporte', true, 'fadeInDown');
                if ($mdSidenav('left').isLockedOpen()) {
                    // Nav. panel information
                    content = "Consulte datos del asociado";
                    Helps.addFieldHelpWithHeader(trip, '#user-data', content, 'e', 'Datos del asociado', false, 'fadeInLeft');
                    content = "Haga clic aquí si desea ver la lista de solicitudes del asociado.";
                    Helps.addFieldHelpWithHeader(trip, '#result-data', content, 'e', 'Lista de solicitudes', false, 'fadeInRight');
                    content = "Para hacer otro tipo de consulta, haga clic aquí.";
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
                content = "Puede generar un reporte detallado haciendo clic aquí.";
                Helps.addFieldHelpWithHeader(trip, '#report-btn', content, 's', 'Generación de reporte', true, 'fadeInDown');
                if ($mdSidenav('left').isLockedOpen()) {
                    // Nav. panel information
                    content = "Éstas son las solicitudes resultantes de la búsqueda. Haga clic para poder " +
                              "ver la lista";
                    Helps.addFieldHelpWithHeader(trip, '#result-data', content, 'e', 'Solicitudes', false, 'fadeInRight');
                    content = "Para hacer otro tipo de consulta, haga clic aquí.";
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
                    content = "Éstas son las listas de solicitudes por administrar. Haga clic en el tipo de solicitud " +
                              "de su elección para ver las solicitudes de préstamo. Al seleccionar alguna, puede " +
                              "ver los detalles de la solicitud para administrarla.";
                    Helps.addFieldHelpWithHeader(trip, '#pending-req', content, 'e', 'Solicitudes pendientes');
                    content = "Puede realizar búsquedas más específicas de las solicitudes. Sólo seleccione" +
                              " el tipo de consulta e ingrese los datos solicitados.";
                    Helps.addFieldHelpWithHeader(trip, '#adv-search', content, 'e', 'Búsqueda avanzada');
                    content = "También puede generar reportes de solicitudes cerradas durante la " +
                              "semana vigente o en un rango de fechas específico.";
                    Helps.addFieldHelpWithHeader(trip, '#approval-report', content, 'e', 'Reporte de solicitudes cerradas');
                    content = "Para realizar gestiones de agentes y el sistema, haga clic en las opciones correspondientes.";
                    Helps.addFieldHelpWithHeader(trip, '#manager-options', content, 's', 'Administración');
                    trip.start();
                } else {
                    trip = new Trip([], options);
                    content = "Haga clic en el ícono para abrir el panel de navegación, donde podrá ver las" +
                              " solicitudes a administrar o realizar búsquedas avanzadas.";
                    Helps.addFieldHelp(trip, '#nav-panel', content, 's');
                    content = "También puede hacer clic aquí para desplegar un menú, donde podrá " +
                              "realizar gestiones del sistema a través de las opciones correspondientes.";
                    Helps.addFieldHelp(trip, '#manager-options-menu', content, 's');
                    trip.start();
                }
            }

            function showResultHelp(options) {
                options.showHeader = true;
                var tripToShowNavigation = new Trip([], options);
                var content;
                if (!$scope.isObjEmpty($scope.requests)) {
                    content =
                        "A continuación se muestra un panel con todas las solciitudes resultantes, " +
                        "categorizadas por los tipos de solicitud disponibles por el sistema.<br/>" +
                        "Para ver una lista de solicitudes, haga clic encima del tipo de préstamo correspondiente.<br/>" +
                        "Para ver los detalles de una solicitud en particular y gestionarla, haga clic encima de la " +
                        "fila correspondiente.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#requests-group', content, 's',
                                                 'Panel de solicitudes');
                    tripToShowNavigation.start();
                } else if ($scope.singleType.length > 0) {
                    content = "A continuación se una tabla listando todas las solicitudes del tipo de préstamo especificado.<br/>" +
                              "Puede hacer ver los detalles de una solicitud haciendo clic encima de la fila correspondiente.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#single-type', content, 'w',
                                                 'Lista de solicitudes');
                    tripToShowNavigation.start();
                }
            }

            /**
             * Shows tour-based help of selected action.
             * @param options: Obj containing tour.js options
             */
            function showActionsHelp(options) {
                options.showHeader = true;
                var tripToShowNavigation = new Trip([], options);
                var content;

                switch ($scope.selectedAction) {
                    case 0:
                        // request by id help.
                        showUserIdHelp();
                        break;
                    case 1:
                        // requests by status help.
                        showStatusHelp();
                        break;
                    case 2:
                        // requests by date help.
                        showCreationDateHelp();
                        break;
                    case 4:
                        // approved amount by date
                        showClosedDateHelp();
                        break;
                    case 5:
                        // approved amount by user id.
                        showUserIdHelp();
                        break;
                    case 6:
                        // closed requests report by date
                        showClosedDateHelp();
                        break;
                    case 7:
                        // closed quests report by current week.
                        showGenerateThisWeekReportHelp();
                        break;
                    case 8:
                        // requests by type help.
                        showTypeHelp();
                        break;
                    case 9:
                        // requests by type help.
                        showPendingRequestHelp();
                        break;
                    case 10:
                        // request by id help.
                        showRequestIdHelp();
                        break;
                }

                function showRequestIdHelp() {
                    content = "Ingrese el ID de la solicitud que desea consultar.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#req-id', content, 's',
                                                 'ID de la solicitud');
                    tripToShowNavigation.start();
                }

                function showCreationDateHelp() {
                    content = "Ingrese una fecha de creación como punto de inicio de la búsqueda.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#date-from', content, 's',
                                                 'Intervalo de fecha');
                    tripToShowNavigation.start();
                    content = "Ingrese una fecha de creación como punto final de la búsqueda.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#date-to', content, 's',
                                                 'Intervalo de fecha');
                    tripToShowNavigation.start();
                }

                function showClosedDateHelp() {
                    content = "Ingrese una fecha de cierre como punto de inicio de la búsqueda.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#date-from', content, 's',
                                                 'Intervalo de fecha');
                    tripToShowNavigation.start();
                    content = "Ingrese una fecha de cierre como punto final de la búsqueda.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#date-to', content, 's',
                                                 'Intervalo de fecha');
                    tripToShowNavigation.start();
                }

                function showStatusHelp() {
                    content = "Seleccione el estatus de las solicitudes que desea consultar.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#req-status', content, 's',
                                                 'Estatus de las solicitudes');
                    tripToShowNavigation.start();
                }

                function showTypeHelp() {
                    content = "Seleccione el tipo de solicitud de aquellas que desea consultar.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#req-type', content, 's',
                                                 'Tipo de solicitudes');
                    tripToShowNavigation.start();
                }

                function showPendingRequestHelp() {
                    content = "Haga clic en CONSULTAR para obtener las estadísticas y listado de solicitudes pendientes.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#pending-btn', content, 's',
                                                 'Solicitudes pendientes');
                    tripToShowNavigation.start();
                }

                function showUserIdHelp() {
                    content = "Ingrese el ID del asociado cuyas solicitudes desea consultar.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#uid', content, 's',
                                                 'ID del asociado');
                    tripToShowNavigation.start();
                }

                function showGenerateThisWeekReportHelp() {
                    content = "Haga clic en GENERAR para generar un reporte detallado de las solicitudes cerradas esta semana.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#generate-btn', content, 's',
                                                 'Reporte');
                    tripToShowNavigation.start();
                }
            }
        }
    };
});

app.directive('manageRequestHelp', function(Helps) {
    return {
        restrict: 'A',
        link: function ($scope) {
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
                if (($scope.model.status == $scope.PRE_APPROVED_STRING ||
                     $scope.model.status == $scope.REJECTED_STRING)
                    && typeof $scope.model.reunion === "undefined") {
                    content = "Agrege el número de reunión (opcional).";
                    Helps.addFieldHelp(trip, "#reunion",
                                       content, 'n');
                }
                if ($scope.model.status == $scope.PRE_APPROVED_STRING
                    && typeof $scope.model.approvedAmount === "undefined") {
                    content = "Agrege el monto aprobado en Bs.";
                    Helps.addFieldHelp(trip, "#approved-amount",
                                       content, 'n');
                }
                if ($scope.model.files.length == 0) {
                    content = "Haga clic para para (opcionalmente) agregar documentos " +
                              "adicionales a la solicitud.";
                    Helps.addFieldHelp(trip, "#more-files", content, 's');
                } else {
                    content = "Estas tarjetas contienen el nombre y posible descripción " +
                              "de los documentos seleccionados. Puede eliminarla o proporcionar una descripción" +
                              " a través de los íconos en la parte inferior de la tarjeta.";
                    Helps.addFieldHelp(trip, "#file-card", content, 'n');
                }
                if ($scope.model.status == $scope.PRE_APPROVED_STRING) {
                    content = "Estas tarjetas muestran información de interés con respecto al monto que el asociado solicitó " +
                              "y el monto máximo que se le será otorgado.";
                    Helps.addFieldHelp(trip, "#info", content, 'n');
                }
                if (!$scope.missingField()) {
                    content = "Haga clic en ACTUALIZAR para guardar " +
                              "los cambios.";
                    Helps.addFieldHelp(trip, "#edit-btn",
                                       content, 'n');
                }
                trip.start();
            }
        }
    };
});


app.directive('manageAgentsHelp', function(Helps, $mdMedia) {
    return {
        restrict: 'A',
        link: function ($scope) {
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

                var contentId = "Ingrese el ID del nuevo usuario.";
                var contentPsw = "Ingrese la contraseña con que el nuevo " +
                                 "usuario ingresará al sistema.";
                var repeatPsw = "Por medidas de seguridad, repita la contraseña.";
                var userType = "Elija el tipo de usuario que desea crear.";
                var contentName = "Ingrese el nombre del usuario.";
                var contentLastName = "Ingrese el apellido del usuario.";
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
                if (typeof $scope.model.psw2 === "undefined") {
                    Helps.addFieldHelp(trip, "#psw2",
                                       repeatPsw, responsivePos);
                }
                if (typeof $scope.model.type === "undefined") {
                    Helps.addFieldHelp(trip, "#user-type",
                                       userType, responsivePos);
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
                if (typeof $scope.model.email === "undefined") {
                    Helps.addFieldHelp(trip, "#user-email",
                                       contentEmail, responsivePos);
                }
                if (!$scope.missingField()) {
                    var content = "Haga clic en REGISTRAR para crear " +
                                  "el nuevo usuario.";
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
                    content = "Haga clic para desplegar una lista " +
                              "con los usuarios gestores registrados en el " +
                              "sistema y escoja el usuario a eliminar.";
                    Helps.addFieldHelp(trip, "#select-agent",
                                       content, responsivePos);
                }
                if ($scope.selectedUser) {
                    content = "Haga clic en REVOCAR para proceder " +
                              "con la remoción de privilegios del usuario seleccionado.";
                    Helps.addFieldHelp(trip, "#remove-btn",
                                       content, 'n');
                }
                trip.start();
            }
        }
    };
});

app.directive('configHelp', function(Helps) {
    return {
        restrict: 'A',
        link: function ($scope) {
            $scope.showHelp = function() {
                if ($scope.selectedTab == 1) {
                    showStatusHelp(Helps.getDialogsHelpOpt());
                } else if ($scope.selectedTab == 2) {
                    showReqAmountHelp(Helps.getDialogsHelpOpt());
                } else if ($scope.selectedTab == 3) {
                    showSpanHelp(Helps.getDialogsHelpOpt());
                } else if ($scope.selectedTab == 4) {
                    showTermsHelp(Helps.getDialogsHelpOpt());
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

                content = "Actualice el porcentaje del sueldo que el asociado puede solicitar como máximo en los préstamos de " +
                          $scope.loanConcepts[$scope.LoanTypes.CASH_VOUCHER].DescripcionDelPrestamo;
                Helps.addFieldHelp(trip, "#max-amount", content, 'n');
                if (!$scope.missingField()) {
                    content = "Haga clic en GUARDAR para hacer efectivo " +
                              "los cambios.";
                    Helps.addFieldHelp(trip, "#save-amounts", content, 'n');
                }
                trip.start();
            }

            function showSpanHelp(options) {
                var trip = new Trip([], options);
                var content;

                if (!$scope.selectedQuery) {
                    content = "Elija el tipo de préstamo al que desea configurar";
                    Helps.addFieldHelp(trip, "#span-select", content, 'n');
                } else {
                    content = "Actualice el tiempo a esperar (en meses) que el asociado debe esperar para realizar otra " +
                              "solicitud de préstamo del tipo " + $scope.loanTypes[$scope.selectedQuery].DescripcionDelPrestamo;
                    Helps.addFieldHelp(trip, "#min-span", content, 'n');
                }
                if (!$scope.missingSpan()) {
                    content = "Haga clic en GUARDAR para hacer efectivo " +
                              "los cambios.";
                    Helps.addFieldHelp(trip, "#save-span", content, 'n');
                }
                trip.start();
            }

            function showTermsHelp(options) {
                var trip = new Trip([], options);
                var content;

                if (!$scope.selectedQuery) {
                    content = "Elija el tipo de préstamo al que desea configurar";
                    Helps.addFieldHelp(trip, "#terms-select", content, 'n');
                } else {
                    content = "Para agregar otro plazo, ingrese el número en meses  y presione ENTER. </br>" +
                              "Para eliminar plazos existentes, bórrelos con el teclado o haga clic en la 'X'.";
                    Helps.addFieldHelp(trip, "#terms", content, 'n');
                }
                if (!$scope.missingTerms()) {
                    content = "Haga clic en GUARDAR para hacer efectivo " +
                              "los cambios.";
                    Helps.addFieldHelp(trip, "#save-terms", content, 'n');
                }
                trip.start();
            }
        }
    };
});

app.directive('historyHelp', function($mdMedia) {
    return {
        restrict: 'A',
        link: function ($scope) {
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
                showHistoryHelp(options);
            };

            function showHistoryHelp(options) {
                options.showHeader = true;
                var tripToShowNavigation = new Trip([
                    { sel : $("#action-summary"),
                        content : "Por cada acción realizada, se proporciona el nombre del " +
                                  "usuario que ejecutó la acción, tipo de acción realizada y fecha-hora de ejecución." +
                                  " Para ver más detalles acerca de la acción realizada, haga clic encima del item.",
                        position : "s", header: "Resumen de acciones", animation: 'fadeInUp' }
                ], options);

                if ($mdMedia('gt-sm')) {
                    tripToShowNavigation.tripData.push(
                        { sel : $("#filter"),
                            content : "También puede filtrar la lista de acciones escribiendo contenido clave. " +
                                      "Ej: 05/08/2016",
                            position : "s", header: "Filtro de búsqueda", animation: 'fadeInUp' }
                    );
                } else {
                    tripToShowNavigation.tripData.push(
                        { sel : $("#toggle-search"),
                            content : "También puede hacer clic en la lupa y " +
                                      "filtrar la lista de acciones escribiendo contenido clave. " +
                                      "Ej: 05/08/2016",
                            position : "s", header: "Filtro de búsqueda", animation: 'fadeInUp' }
                    );
                }
                tripToShowNavigation.start();
            }
        }
    };
});

app.directive('userInfoHelp', function($mdMedia) {
    return {
        restrict: 'A',
        link: function ($scope) {
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
                showUserInfoHelp(options);
            };

            function showUserInfoHelp(options) {
                options.showHeader = true;
                var responsivePos = $mdMedia('xs') ? 's' : 'e';
                var tripToShowNavigation = new Trip([
                    { sel : $("#info-card"),
                        content : "Esta tarjeta muestra información personal de interés del asociado " +
                                  $scope.userName,
                        position : responsivePos, header: "Información del asociado", expose: true, animation: 'fadeInUp' }
                ], options);
                tripToShowNavigation.start();
            }
        }
    };
});
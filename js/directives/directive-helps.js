var app = angular.module('sgdp.directive-helps', []);

/**
 * The following help directives are meant to be tied to a specific scope.
 */
app.directive('applicantHelp', function(Helps, Requests, $mdMedia, $mdSidenav) {
    return {
        restrict: 'A',
        link: function ($scope, elem) {
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
                    content = "También puede crear una solicitud haciendo clic aquí";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#new-req-fab', content, responsivePos,
                                                 'Crear solicitud', true);
                    tripToShowNavigation.start();
                } else if ($scope.contentLoaded && Requests.getTotalLoans($scope.requests) > 0) {
                    content = "Haga clic en el ícono para abrir el panel de navegación y seleccionar alguna " +
                              "de sus solicitudes para ver más detalles";
                    Helps.addFieldHelp(tripToShowNavigation, '#nav-panel', content, 's', true);
                    content = "También puede crear una solicitud haciendo clic aquí";
                    Helps.addFieldHelp(tripToShowNavigation, '#new-req-fab', content, responsivePos, true);
                    tripToShowNavigation.start();
                } else {
                    options.showHeader = true;
                    content = "Para crear una solicitud haga clic aquí";
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
                          "haciendo clic encima de ellos.";
                Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-docs', content, 'n',
                                             'Documentos', true);
                if ($mdSidenav('left').isLockedOpen()) {
                    if (!$scope.req.validationDate) {
                        content = "También puede editar la información de su solicitud descargar todos los " +
                                  "documentos, o eliminarla presionando el botón correspondiente.";
                    } else {
                        content = "También puede descargar todos los documentos haciendo clic aquí.";
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
    };
});

app.directive('applicantCreateHelp', function(Helps) {
    return {
        restrict: 'A',
        link: function ($scope, elem) {
            $scope.showHelp = function () {
                showFormHelp(Helps.getDialogsHelpOpt());
            };


        }
    }
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
                } else if (!$scope.req) {
                    // User has not selected any request yet, tell him to do it.
                    showSidenavHelp(Helps.getDialogsHelpOpt());
                } else {
                    // Guide user through request selection's possible actions.
                    showRequestHelp(Helps.getDialogsHelpOpt());
                }
            };

            /**
             * Shows tour-based help of searchbar
             * @param options: Obj containing tour.js options
             */
            function showSearchbarHelp(options) {
                var tripToShowNavigation = new Trip([], options);
                Helps.addFieldHelp(tripToShowNavigation, '#search',
                                   'Ingrese la cédula de identidad de algún afiliado para gestionar sus solicitudes.', 's');
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
                                   'de algún afiliado para gestionar sus solicitudes.', pos);
                tripToShowNavigation.start();
            }

            /**
             * Shows tour-based help of side navigation panel
             * @param options: Obj containing tour.js options
             */
            function showSidenavHelp(options) {
                var tripToShowNavigation = new Trip([], options);
                if ($mdSidenav('left').isLockedOpen()) {
                    options.showHeader = true;
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#requests-list',
                                                 'Consulte datos de interés del afiliado, o seleccione ' +
                                                 'alguna de sus solicitudes en la lista para ver más detalles.', 'e',
                                                 'Panel de navegación', true);
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#new-req-fab',
                                                 'También puede abrir una solicitud haciendo clic aquí', 'w',
                                                 'Nueva solicitud', true);
                    tripToShowNavigation.start();
                } else {
                    Helps.addFieldHelp(tripToShowNavigation, '#nav-panel',
                                       'Haga clic en el ícono para abrir el panel de navegación,' +
                                       ' donde podrá consultar datos del afiliado o gestionar sus solicitudes.', 'e');
                    tripToShowNavigation.start();
                }
            }

            /**
             * Shows tour-based help of selected request details section.
             * @param options: Obj containing tour.js options
             */
            function showRequestHelp(options) {
                options.showHeader = true;
                var responsiveNorthPos = $mdMedia('xs') ? 'n' : 'w';
                var responsiveSouthPos = $mdMedia('xs') ? 's' : 'w';
                var tripToShowNavigation = new Trip([], options);
                var content;
                // Validation help
                if (!$scope.req.validationDate) {
                    content = "Esta solicitud no ha sido validada por su solicitante.";
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#validation-card', content, 's',
                                                 'Validación de solicitud', true);
                }
                // Request summary information
                content = "Aquí se muestra información acerca de la fecha de creación, monto solicitado " +
                          ", y un comentario de haberlo realizado.";
                Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-summary', content, 's',
                                             'Resumen de la solicitud', true);
                // Request status information
                content = "Esta sección provee información acerca del estatus de la solicitud.";
                Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-status-summary', content, 's',
                                             'Resumen de estatus', true);
                // Request payment due information
                content = "Acá puede apreciar las cuotas a pagar, indicando el monto por mes y el plazo del pago en meses.";
                Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-payment-due', content, 's',
                                             'Cuotas a pagar', true);
                // Request contact number
                content = "Aquí se muestra el número de teléfono del solicitante.";
                Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-contact-number', content, 'n',
                                             'Número de contacto', true);
                // Request contact email
                content = "Éste es el correo electrónico a través del cual el sistema enviará información y " +
                          "actualizaciones referente a la solicitud.";
                Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-email', content, 'n',
                                             'Correo electrónico', true);
                // Request documents information
                content = "Éste y los siguientes items contienen " +
                          "el nombre y, de existir, una descripción de cada documento en la solicitud. " +
                          "Puede verlos/descargarlos haciendo clic encima de ellos.";
                Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-docs', content, 'n',
                                             'Documentos', true);
                // Additional documents.
                content = "Siendo un documento adicional, " +
                          "puede hacer clic en el botón de opciones para proveer una descripción, " +
                          "descargarlos o incluso eliminarlos.";
                Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-docs-actions', content, responsiveNorthPos,
                                             'Documentos', true, 'fadeInLeft');
                if ($scope.req.docs.length < 2) {
                    // This request hasn't additional documents.
                    tripToShowNavigation.tripData.splice(tripToShowNavigation.tripData.length - 1, 1);
                }
                if ($mdSidenav('left').isLockedOpen()) {
                    if (!$scope.req.validationDate) {
                        content = "Puede ver el historial de la solicitud, editar su información, descargar todos " +
                                  "sus documentos, o eliminarla presionando el botón correspondiente.";
                    } else {
                        content = "Puede ver el historial de la solicitud, editarla con alguna actualización " +
                                  "(si la solicitud no se ha cerrado), o descargar todos sus documentos presionando " +
                                  "el botón correspondiente.";
                    }
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-summary-actions', content, responsiveSouthPos,
                                                 'Acciones', true, 'fadeInLeft');
                } else {
                    if (!$scope.req.validationDate) {
                        content = "Haga clic en el botón de opciones para ver el historial de la solicitud, editar " +
                                  "su información, descargar todos sus documentos, o eliminarla";
                    } else {
                        content = "Haga clic en el botón de opciones para " +
                                  "ver el historial de la solicitud, editarla con con alguna actualización (si la solicitud" +
                                  " no ha cerrado), o descargar todos sus documentos.";
                    }
                    Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-summary-actions-menu',
                                                 content, responsiveSouthPos,
                                                 'Acciones', true, 'fadeInLeft');
                }
                tripToShowNavigation.start();
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
                    content = "Ingrese la cantidad de Bs. solicitado por el afiliado.";
                    Helps.addFieldHelp(tripToShowNavigation, "#req-amount", content, 's');
                }
                if (!$scope.model.phone) {
                    // Phone number field
                    content = "Ingrese el número telefónico del afiliado, a través " +
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
                content = "Escoja el plazo (en meses) en el que el afiliado desea " +
                          "pagar su deuda.";
                Helps.addFieldHelp(tripToShowNavigation, "#payment-due", content, 'n');
                // Add loan type help.
                content = "Escoja el tipo de préstamo que el afiliado desea solicitar.";
                Helps.addFieldHelp(tripToShowNavigation, "#loan-type", content, 'n');
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
                // Add loan type help.
                content = "Escoja el tipo de préstamo que desea solicitar.";
                Helps.addFieldHelp(tripToShowNavigation, "#loan-type", content, 'n');
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
                              "del cual nos comunicaremos con el afiliado.";
                    Helps.addFieldHelp(tripToShowNavigation, "#phone-numb",
                                       content, 'n');
                }
                if (!$scope.model.email) {
                    // Email field
                    content = "Ingrese el correo electrónico, a través del cual se le " +
                              "enviará al afiliado información y actualizaciones referente a su solicitud.";
                    Helps.addFieldHelp(tripToShowNavigation, "#email",
                                       content, 'n');
                }
                // Add payment due help.
                content = "Escoja el plazo (en meses) en el que el afiliado desea " +
                          "pagar su deuda.";
                Helps.addFieldHelp(tripToShowNavigation, "#payment-due", content, 'n');
                // Add loan type help.
                content = "Escoja el tipo de préstamo que el afiliado desea solicitar.";
                Helps.addFieldHelp(tripToShowNavigation, "#loan-type", content, 'n');
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
                // Add loan type help.
                content = "Escoja el tipo de préstamo que desea solicitar.";
                Helps.addFieldHelp(tripToShowNavigation, "#loan-type", content, 'n');
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
                if ($scope.pieloaded && !$scope.req) {
                    if ($scope.showResult == 0) {
                        showSingleUserResultHelp(Helps.getDialogsHelpOpt());
                    } else if ($scope.fetchedRequests()) {
                        showMultipleUsersResultHelp(Helps.getDialogsHelpOpt());
                    }
                } else if (!$scope.req) {
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
                content = "Puede generar un reporte detallado haciendo clic aquí.";
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
                    if ($scope.showResult !== 1) {
                        content = "Éstas son las solicitudes resultantes de la búsqueda. Haga clic en el tipo de " +
                                  "solicitud de su elección para ver las solicitudes de préstamo.<br/>Para facilitar " +
                                  "la elección, el estatus de cada una está identificada por un bombillo " +
                                  "amarillo, verde y rojo para Recibida, Aprobada y Rechazada, respectivamente.";
                    } else {
                        content = "Éstas son las solicitudes resultantes de la búsqueda. Haga clic en el tipo de solicitud " +
                                  "de su elección para ver las solicitudes de préstamo.";
                    }
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
                    content = "También puede realizar gestiones del sistema a través de las opciones correspondientes.";
                    Helps.addFieldHelpWithHeader(trip, '#manager-options', content, 's', 'Administración');
                    trip.start();
                } else {
                    trip = new Trip([], options);
                    content = "Haga clic en el ícono para abrir el panel de navegación, donde podrá elegir las" +
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
                          "verlos/descargarlos haciendo clic encima de ellos.";
                Helps.addFieldHelpWithHeader(trip, '#request-docs', content, 'n', 'Documentos', true, 'fadeInDown');

                if ($mdSidenav('left').isLockedOpen()) {
                    content = "Puede ver los datos del creador de la solicitud, ver el historial de la solicitud, editarla " +
                              "(si la solicitud no se ha cerrado), o descargar todos sus documentos presionando " +
                              "el botón correspondiente.";
                    Helps.addFieldHelpWithHeader(trip, '#request-summary-actions', content, 'w', 'Acciones', true,
                                                 'fadeInLeft');
                } else {
                    content = "Haga clic en el botón de opciones para ver los datos del creador de la solicitud, " +
                              "ver el historial de la solicitud, editarla (si la solicitud no se ha cerrado), " +
                              "o descargar todos sus documentos.";
                    Helps.addFieldHelpWithHeader(trip, '#request-summary-actions-menu', content, 's', 'Acciones', true,
                                                 'fadeInLeft');
                }
                trip.start();
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

                var contentId = "Ingrese el ID de usuario del " +
                                "nuevo agente.";
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
                    var content = "Haga clic en REGISTRAR para crear " +
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
                    content = "Haga clic en GUARDAR para hacer efectivo " +
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
                    content = "Haga clic en GUARDAR para hacer efectivo " +
                              "los cambios.";
                    Helps.addFieldHelp(trip, "#save-span", content, 'n');
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
                                  " Para ver más detalles acerca de la acción realizada, haga click encima del item.",
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
                            content : "También puede hacer click en la lupa y " +
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
                        content : "Esta tarjeta muestra información personal de interés del afiliado " +
                                  $scope.userName,
                        position : responsivePos, header: "Información del afiliado", expose: true, animation: 'fadeInUp' }
                ], options);
                tripToShowNavigation.start();
            }
        }
    };
});
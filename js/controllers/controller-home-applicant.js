angular
    .module('sgdp')
    .controller('ApplicantHomeController', userHome);

userHome.$inject = ['$scope', '$http', '$cookies', '$timeout',
                    '$mdSidenav', '$mdDialog', 'Upload', '$mdMedia'];

function userHome($scope, $http, $cookies, $timeout,
                  $mdSidenav, $mdDialog, Upload, $mdMedia) {
    'use strict';
    $scope.loading = true;
    $scope.selectedReq = '';
    $scope.selectedLoan = -1;
    $scope.requests = {};
    $scope.docs = [];
    $scope.showList = {pp: false, vc: false};
    $scope.fetchError = '';
    // contentAvailable will indicate whether sidenav can be visible
    $scope.contentAvailable = false;
    // contentLoaded will indicate whether sidenav can be locked open
    $scope.contentLoaded = false;
    $scope.listTitle = {
        pp: 'préstamos personales',
        vc: 'vales de caja'
    };
    var totalLoans = 0;

    var fetchId = $cookies.getObject('session').id;
    $http.get('index.php/ApplicantHomeController/getUserRequests',
        {params: {fetchId: fetchId}})
        .then(function (response) {
                  $scope.maxReqAmount = response.data.maxReqAmount;
                  if (response.data.message === "success") {
                      if (typeof response.data.requests !== "undefined") {
                          filterRequests(response.data.requests);
                      }
                      $scope.contentAvailable = true;
                      $timeout(function () {
                          $scope.contentLoaded = true;
                          $mdSidenav('left').open();
                      }, 600);
                  } else {
                      $scope.fetchError = response.data.message;
                  }
                  $scope.loading = false;
              });

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
        if (i != -1 && j != -1) {
            $scope.selectedReq = i;
            $scope.selectedLoan = j;
            console.log(i);
            console.log(j);
            $scope.docs = $scope.requests[i][j].docs;
        }
        $mdSidenav('left').toggle();
    };

    /**
     * Filters all requests by type and assigns to the scope.
     * @param requests - Requests array.
     */
    function filterRequests(requests) {
        totalLoans = 0;
        $scope.requests.pp = requests.filter(function (loan) {
            totalLoans++;
            return loan.type == 40;
        });
        $scope.requests.vc = requests.filter(function(loan) {
            totalLoans++;
            return loan.type == 31;
        });
    }

    $scope.openNewRequestDialog = function ($event, obj) {
        var parentEl = angular.element(document.body);
        $mdDialog.show({
            parent: parentEl,
            targetEvent: $event,
            templateUrl: 'index.php/NewRequestController',
            clickOutsideToClose: false,
            escapeToClose: false,
            autoWrap: false,
            locals: {
                fetchId: fetchId,
                maxReqAmount: $scope.maxReqAmount,
                requestNumb: totalLoans + 1,
                obj: obj,
                parentScope: $scope
            },
            controller: DialogController
        });
        // Isolated dialog controller for the new request dialog
        function DialogController($scope, $mdDialog, fetchId, maxReqAmount,
                                  requestNumb, parentScope, obj) {
            $scope.docPicTaken = false;
            $scope.uploading = false;
            $scope.maxReqAmount = maxReqAmount;
            // obj could have a reference to user data, saved
            // before confirmation dialog was opened.
            $scope.model = obj || {due: 24, type: 40, tel: {operator: '0412'}};
            // if user data exists, it means the ID was
            // already given, so we must show it.
            $scope.idPicTaken = obj && obj.idFile ? true : false;
            $scope.uploadErr = '';
            // Will notify whether all files were uploaded.
            var uploadedFiles;
            // Will contain docs to create in DB
            var docs = [];

            // if user came back to this dialog after confirming operation..
            if ($scope.model.confirmed) {
                // Go ahead and proceed with creation
                createNewRequest();
            }

            $scope.closeDialog = function () {
                $mdDialog.hide();
            };

            $scope.missingField = function () {
                return !$scope.idPicTaken ||
                       typeof $scope.model.reqAmount === "undefined" ||
                       !$scope.model.tel.value;
            };

            $scope.deleteIdPic = function (event) {
                $scope.idPicTaken = false;
                $scope.model.idFile = {};
                // Stops click propagation (which would open)
                // the camera again.
                event.stopPropagation();
            };

            $scope.deleteDocPic = function () {
                $scope.docPicTaken = false;
            };

            $scope.gatherIDFile = function (file, errFiles) {
                if (file) {
                    $scope.model.idFile = file;
                    $scope.model.idFile.description = "Comprobación de autorización";
                    $scope.model.idFile.docName = "Identidad";
                    $scope.idPicTaken = true;
                }
                $scope.errFiles = errFiles;
            };

            $scope.gatherDocFile = function (file, errFiles) {
                if (file) {
                    $scope.docFile = file;
                    $scope.docFile.description = "Documento explicativo " +
                                                 "de la solicitud";
                    $scope.docFile.docName = "Solicitud";
                    $scope.docPicTaken = true;
                }
                $scope.errFiles = errFiles;
            };

            $scope.showIdError = function (error, param) {
                if (error === "pattern") {
                    return "Archivo no aceptado. Por favor seleccione " +
                        "imágenes o archivos PDF.";
                } else if (error === "maxSize") {
                    return "El archivo es muy grande. Tamaño máximo es: " +
                        param;
                }
                console.log(error);
            };

            $scope.showError = function (error, param) {
                if (error === "pattern") {
                    return "Archivo no aceptado. Por favor seleccione " +
                           "sólo documentos.";
                } else if (error === "maxSize") {
                    return "El archivo es muy grande. Tamaño máximo es: " +
                           param;
                }
            };

            // Creates new request in database and uploads documents
            function createNewRequest() {
                $scope.uploading = true;
                // uploadedFiles = new Array($scope.docPicTaken ? 2 : 1).
                //     fill(false);

                // Upload ID document.
                uploadFile($scope.model.idFile, 0);
                // if ($scope.docPicTaken) {
                //     // Upload the optional document.
                //     uploadFile($scope.docFile, 1);
                // }
            };

            // Determines whether all files were uploaded
            function uploadsFinished(uploadedFiles) {
                return (uploadedFiles.filter(function (bool) {
                    return !bool;
                }).length == 0);
            }

            // Uploads each of selected documents to the server
            // and updates database
            function uploadFile(file, uploadIndex) {
                file.upload = Upload.upload({
                    url: 'index.php/NewRequestController/upload',
                    data: {
                        file: file,
                        userId: fetchId,
                        requestNumb: requestNumb
                    }
                });

                file.upload.then(function (response) {
                    // Register upload success
                    // uploadedFiles[uploadIndex] = true;
                    // Add doc info
                    docs.push({
                        lpath: response.data.lpath,
                        docName: file.docName,
                        description: file.description
                    });

                    // if (uploadsFinished(uploadedFiles)) {
                    //     // If all files were uploaded, proceed to
                    //     // database entry creation.
                    //     performCreation(0);
                    // }

                    // Proceed to database entry creation.
                    performCreation(0);
                }, function (response) {
                    // Show upload error
                    if (response.status > 0)
                        $scope.errorMsg = response.status + ': ' + response.data;
                }, function (evt) {
                    // Upload file upload progress
                    file.progress = Math.min(100, parseInt(100.0 *
                                                           evt.loaded / evt.total));
                });
            }

            // Helper function that performs the document's creation.
            function performCreation(autoSelectIndex) {
                var postData = {
                    userId: fetchId,
                    reqAmount: $scope.model.reqAmount,
                    tel: parseInt($scope.model.tel.operator + $scope.model.tel.value, 10),
                    due: $scope.model.due,
                    loanType: $scope.model.type,
                    docs: docs
                };
                $http.post('index.php/NewRequestController/createRequest',
                           JSON.stringify(postData))
                    .then(function (response) {
                              if (response.status == 200) {
                                  updateRequestListUI(fetchId, autoSelectIndex,
                                                      'Solicitud creada',
                                                      'La solicitud ha sido creada exitosamente.',
                                                      true, true,
                                                      parseInt(postData.loanType, 10));
                              }
                          });
            }

            // Determines wether the specified userType matches
            // logged user's type
            $scope.userType = function (type) {
                return type === $cookies.getObject('session').type;
            };

            // Sets the bound input to the max possibe request amount
            $scope.setMax = function() {
                $scope.model.reqAmount = $scope.maxReqAmount;
            };

            // Shows a dialog asking user to confirm the request creation.
            $scope.confirmCreation = function (ev) {
                // Appending dialog to document.body to cover sidenav in docs app
                var confirm = $mdDialog.confirm()
                .title('Confirmación de creación de solicitud')
                .textContent('El sistema generará el documento ' +
                    'correspondiente a su solicitud y será' +
                    ' atendida a la mayor brevedad posible. ¿Desea proceder' +
                    ' con su solicitud?')
                .css('smaller-dialog-content')
                .ariaLabel('Confirmación de creación de solicitud')
                .targetEvent(ev)
                .ok('Sí')
                .cancel('Cancelar');
                $mdDialog.show(confirm).then(function() {
                    // Re-open parent dialog and perform request creation
                    $scope.model.confirmed = true;
                    parentScope.openNewRequestDialog(null, $scope.model);
                }, function() {
                    // Re-open parent dialog and do nothing
                    parentScope.openNewRequestDialog(null, $scope.model);

                });
            };

            $scope.showHelp = function () {
                var options = {
                    showNavigation: true,
                    showCloseBox: true,
                    delay: -1,
                    tripTheme: "dark",
                    prevLabel: "Anterior",
                    nextLabel: "Siguiente",
                    finishLabel: "Entendido"
                };
                showFormHelp(options);
            };
            /**
             * Shows tour-based help of all input fields.
             * @param options: Obj containing tour.js options
             */
            function showFormHelp(options) {
                if (!$scope.missingField()) {
                    var tripToShowNavigation = new Trip([
                        // Tell user to hit the create button
                        {
                            sel: $("#create-btn"),
                            content: "Haga click en CREAR para generar " +
                                     "la solicitud.",
                            position: "n", animation: 'fadeInLeft'
                        }

                    ], options);
                    tripToShowNavigation.start();
                } else {
                    var tripToShowNavigation = new Trip([], options);
                    showAllFieldsHelp(tripToShowNavigation);
                }
            }

            function addFieldHelp(trip, id, content, pos) {
                trip.tripData.push(
                    {
                        sel: $(id), content: content, position: pos,
                        animation: 'fadeInUp'
                    }
                );
            }

            function showAllFieldsHelp(tripToShowNavigation) {
                var content = '';
                if (!$scope.model.reqAmount) {
                    // Requested amount field
                    content = "Ingrese la cantidad de Bs. que " +
                                  "desea solicitar.";
                    addFieldHelp(tripToShowNavigation, "#req-amount",
                                  content, 's');
                }
                if (!$scope.model.phone) {
                    // Requested amount field
                    content = "Ingrese su número telefónico, a través " +
                                  "del cual nos estaremos comunicando con usted.";
                    addFieldHelp(tripToShowNavigation, "#phone-numb",
                                  content, 'n');
                }
                if (!$scope.idPicTaken) {
                    // Show id pic field help
                    content = "Haga click para subir su cédula de " +
                                  "identidad en digital.";
                    addFieldHelp(tripToShowNavigation, "#id-pic", content, 'n');
                }
                // Add payment due help.
                content = "Escoja el plazo (en meses) en el que desea " +
                                "pagar su deuda.";
                addFieldHelp(tripToShowNavigation, "#payment-due", content, 'n');
                // Add loan type help.
                content = "Escoja el tipo de préstamo que desea solicitar.";
                addFieldHelp(tripToShowNavigation, "#loan-type", content, 'n');
                tripToShowNavigation.start();
            }
        }
    };

    // Helper method that updates UI's request list.
    function updateRequestListUI(userId, autoSelectIndex,
                                 dialogTitle, dialogContent,
                                 updateUI, toggleList, type) {
        // Update interface
        $http.get('index.php/AgentHomeController/getUserRequests',
            {params: {fetchId: userId}})
            .then(function (response) {
                      if (response.status == 200) {
                          // Update UI only if needed
                          var loanType = mapReqTypes(type);
                          console.log(type);
                          console.log(loanType);
                          if (updateUI) {
                              updateContent(response.data.requests,
                                            loanType, //TODO: Utils.mapReqTypes(type)
                                            autoSelectIndex);
                          }
                          // Toggle request list only if requested.
                          if (toggleList) {
                              toggleReqList(loanType);
                          }
                          // Close dialog and alert user that operation was
                          // successful
                          $mdDialog.hide();
                          showAlertDialog(dialogTitle, dialogContent);

                      } else {
                          console.log("REFRESHING ERROR!");
                          console.log(response);
                      }
                  });
    }

    function mapReqTypes(type) {
        switch (type) {
            case 40:
                return 'pp';
                break;
            case 31:
                return 'vc';
                break;
            default:
                return 'unknown';
        }
    }

    // Helper function that shows an alert dialog message
    // to user.
    function showAlertDialog(dialogTitle, dialogContent) {
        $mdDialog.show(
            $mdDialog.alert()
                .parent(angular.element(document.body))
                .clickOutsideToClose(true)
                .title(dialogTitle)
                .textContent(dialogContent)
                .ariaLabel(dialogTitle)
                .ok('Ok')
        );
    }

    // Helper function that updates content with new request
    function updateContent(requests, req, selection) {
        $scope.contentLoaded = true;
        $scope.contentAvailable = true;
        $scope.fetchError = '';
        // Filter the result.
        filterRequests(requests);
        console.log(req);
        console.log(selection);
        // Automatically select created request
        $scope.selectRequest(req, selection);
    }

    /**
     * Automatically toggles the requests list.
     *
     * @param index - Request list's index
     */
    function toggleReqList(index) {
        // Close the list
        // $scope.showList[index] = false;
        closeAllReqList();
        $timeout(function () {
            // Open the list
            $scope.showList[index] = true;
        }, 1000);

    }

    function closeAllReqList() {
        angular.forEach($scope.showList, function(show, index) {
            $scope.showList[index] = false;
        });
    }

    // Helper function for formatting numbers with leading zeros
    $scope.pad = function (n, width, z) {
        z = z || '0';
        n = n + '';
        return n.length >= width ? n :
        new Array(width - n.length + 1).join(z) + n;
    };

    $scope.downloadDoc = function (doc) {
        window.open('index.php/ApplicantHomeController/download?lpath=' +
                    doc.lpath, '_blank');
    };

    $scope.downloadAll = function () {
        // Bits of pre-processing before passing objects to URL
        var paths = [];
        angular.forEach($scope.docs, function (doc) {
            paths.push(doc.lpath);
        });
        location.href = 'index.php/ApplicantHomeController/' +
                        'downloadAll?docs=' + JSON.stringify(paths);
    };

    $scope.openMenu = function () {
        $mdSidenav('left').toggle();
    };

    $scope.showHelp = function () {
        var options = {
            showNavigation: true,
            showCloseBox: true,
            delay: -1,
            tripTheme: "dark",
            prevLabel: "Anterior",
            nextLabel: "Siguiente",
            finishLabel: "Entendido"
        };
        if ($scope.docs.length == 0) {
            // User has not selected any request yet, tell him to do it.
            showSidenavHelp(options);
        } else {
            // Guide user through request selection's possible actions
            showRequestHelp(options);
        }
    };

    /**
     * Shows tour-based help of side navigation panel
     * @param options: Obj containing tour.js options
     */
    function showSidenavHelp(options) {
        var responsivePos = $mdMedia('xs') ? 'n' : 'w';
        if ($mdSidenav('left').isLockedOpen() && totalLoans > 0) {
            options.showHeader = true;
            var tripToShowNavigation = new Trip([
                {
                    sel: $("#requests-list"),
                    content: "Seleccione alguna de sus solicitudes en la " +
                             "lista para ver más detalles.",
                    position: "e", expose: true,
                    header: "Panel de navegación", animation: 'fadeInUp'
                },
                {
                    sel: $("#new-req-fab"),
                    content: "También puede crear una solicitud haciendo " +
                             "click aquí",
                    position: responsivePos, expose: true, header: "Crear solicitud",
                    animation: 'fadeInUp'
                }
            ], options);
            tripToShowNavigation.start();
        } else if ($scope.contentLoaded && totalLoans > 0) {
            var tripToShowNavigation = new Trip([
                {
                    sel: $("#nav-panel"),
                    content: "Haga click en el ícono para abrir el panel " +
                             "de navegación y seleccionar alguna de sus " +
                             "solicitudes para ver más detalles",
                    position: "s", animation: 'fadeInUp'
                },
                {
                    sel: $("#new-req-fab"),
                    content: "También puede crear una solicitud haciendo " +
                             "click aquí",
                    position: responsivePos, expose: true, header: "Crear solicitud",
                    animation: 'fadeInUp'
                }
            ], options);
            tripToShowNavigation.start();
        } else {
            options.showHeader = true;
            var tripToShowNavigation = new Trip([
                {
                    sel: $("#new-req-fab"),
                    content: "Para crear una solicitud haga click aquí",
                    position: responsivePos, expose: true, header: "Crear solicitud",
                    animation: 'fadeInUp'
                }
            ], options);
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
        // options.showSteps = true;
        var tripToShowNavigation = new Trip([
            // Request summary information
            {
                sel: $("#request-summary"),
                content: "Aquí se muestra " +
                         "información acerca de la fecha de creación, monto " +
                         "solicitado por usted, y un posible comentario.",
                position: "s", header: "Resumen de la solicitud",
                expose: true
            },
            // Request status information
            {
                sel: $("#request-status-summary"),
                content: "Esta sección " +
                         "provee información acerca del estatus de su solicitud.",
                position: "s", header: "Resumen de estatus",
                expose: true, animation: 'fadeInDown'
            },
            // Request documents information
            {
                sel: $("#request-docs"),
                content: "Éste y los siguientes " +
                         "items contienen el nombre y una posible descripción de " +
                         "cada documento en su solicitud. Puede verlos/descargarlos " +
                         "haciendo click encima de ellos.",
                position: "s", header: "Documentos", expose: true,
                animation: 'fadeInDown'
            },
            {
                // Download as zip information
                sel: $("#request-summary-actions"),
                content: "También puede " +
                         "descargar todos los documentos haciendo click aquí.",
                position: responsivePos, header: "Descargar todo", expose: true,
                animation: 'fadeInLeft'
            }
        ], options);
        tripToShowNavigation.start();
    }
}

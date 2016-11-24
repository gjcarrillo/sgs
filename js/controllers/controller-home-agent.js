angular
    .module('sgdp')
    .controller('AgentHomeController', agentHome);

agentHome.$inject = ['$scope', '$mdDialog', '$cookies', 'FileUpload', 'Constants',
                     '$http', '$state', '$timeout', '$mdSidenav', '$mdMedia', 'Requests', 'Utils', 'Helps'];

function agentHome($scope, $mdDialog, $cookies, FileUpload, Constants,
                   $http, $state, $timeout, $mdSidenav, $mdMedia, Requests, Utils, Helps) {
    'use strict';
    $scope.loading = false;
    $scope.selectedReq = '';
    $scope.selectedLoan = -1;
    $scope.requests = {};
    $scope.docs = [];
    $scope.fetchError = "";
    $scope.showList = {pp: false, vc: false};
    $scope.idPrefix = "V";
    $scope.listTitle = Requests.getTypeTitles();
    // contentAvailable will indicate whether sidenav can be visible
    $scope.contentAvailable = false;
    // contentLoaded will indicate whether sidenav can be locked open
    $scope.contentLoaded = false;
    // This will enable / disable search bar in mobile screens
    $scope.searchEnabled = false;

    // Check if there is stored data before we went to History
    var requests = JSON.parse(sessionStorage.getItem("requests"));
    if (requests != null) {
        $scope.requests = requests;
        $scope.fetchId = sessionStorage.getItem("fetchId");
        // fetchId is used for several database queries.
        // that is why we don't use searchInput value, which is bind to search input.
        $scope.searchInput = $scope.fetchId.replace('V', '');
        $scope.selectedReq = sessionStorage.getItem("selectedReq");
        $scope.selectedLoan = parseInt(sessionStorage.getItem("selectedLoan"));
        $scope.docs = $scope.requests[$scope.selectedReq][$scope.selectedLoan].docs;
        $scope.showList = JSON.parse(sessionStorage.getItem("showList"));
        $scope.contentAvailable = true;
        $scope.contentLoaded = true;
        // Got back what we wanted -- erase them from storage
        sessionStorage.removeItem("requests");
        sessionStorage.removeItem("fetchId");
        sessionStorage.removeItem("selectedReq");
        sessionStorage.removeItem("selectedLoan");
        sessionStorage.removeItem("showList");
    }

    $scope.generatePdfDoc = function () {
        $http.get('index.php/DocumentGenerator/generatePdf')
            .then(function (response) {
                      console.log(response);
                  });
    };

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
        console.log(i);
        console.log(j);
        if (i != '' && j != -1) {
            $scope.docs = $scope.requests[i][j].docs;
        }
        $mdSidenav('left').toggle();
    };

    $scope.fetchRequests = function (searchInput) {
        $scope.contentAvailable = false;
        $scope.fetchId = $scope.idPrefix + searchInput;
        $scope.requests = [];
        closeAllReqList();
        $scope.loading = true;
        $scope.docs = [];
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

    // Helper function for formatting numbers with leading zeros
    $scope.pad = function (n, width, z) {
        return Utils.pad(n, width, z);
    };


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
            $scope.APPLICANT = Constants.Users.APPLICANT;
            $scope.AGENT = Constants.Users.AGENT;
            $scope.PERSONAL = Constants.LoanTypes.PERSONAL;
            $scope.CASH_VOUCHER = Constants.LoanTypes.CASH_VOUCHER;
            // obj could have a reference to user data, saved
            // before confirmation dialog was opened.
            $scope.model = obj || {due: 24, type: $scope.PERSONAL, tel: {operator: '0412'}};
            // if user data exists, it means the ID was
            // already given, so we must show it.
            if (obj && obj.idFile) {
                updateIdPic(obj.idData);
            } else {
                $scope.idPicTaken = false;
            }

            $scope.uploadErr = '';

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
                       typeof $scope.model.reqAmount === "undefined" || !$scope.model.tel.value;
            };

            // TODO: Try to implement this onSelectOpen and onSelectClose
            // fix as a DIRECTIVE! Used un multiple views...
            var backup;
            $scope.onSelectOpen = function () {
                backup = $scope.model.tel.operator;
                $scope.model.tel.operator = null;
            };

            $scope.onSelectClose = function () {
                if ($scope.model.tel.operator === null) {
                    $scope.model.tel.operator = backup;
                }
            };

            function updateIdPic(dataURL) {
                $scope.model.idData = dataURL;
                $scope.idPicTaken = true;
                // Upate dd pic input
                $scope.model.idFile = "Foto del afiliado";
            }

            $scope.deleteIdPic = function (event) {
                $scope.idPicTaken = false;
                $scope.model.idFile = null;
                // Stops click propagation (which would open)
                // the camera again.
                event.stopPropagation();
            };

            $scope.showError = function (error, param) {
                FileUpload.showDocUploadError(error, param)
            };

            // Creates new request in database and uploads documents
            function createNewRequest() {
                $scope.uploading = true;
                var docs = [];

                // Upload ID photo.
                var type = Requests.mapLoanTypes($scope.model.type);
                var requestNumb = type + '.' + (requests[type].length + 1);
                FileUpload.uploadImage($scope.model.idData, fetchId, requestNumb).then(
                    function (uploadedDoc) {
                        docs.push(uploadedDoc);
                        performCreation(docs);
                    },
                    function (errorMsg) {
                        $scope.errorMsg = errorMsg;
                    }
                );
            }

            // Shows a dialog asking user to confirm the request creation.
            $scope.confirmCreation = function (ev) {
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

            // Helper function that performs the document's creation.
            function performCreation(docs) {
                var postData = {
                    userId: fetchId,
                    reqAmount: $scope.model.reqAmount,
                    tel: parseInt($scope.model.tel.operator + $scope.model.tel.value, 10),
                    due: $scope.model.due,
                    loanType: $scope.model.type,
                    docs: docs
                };
                Requests.createRequest(postData).then(
                    function () {
                        updateRequestListUI(fetchId, 0, 'Solicitud creada',
                                            'La solicitud ha sido creada exitosamente.',
                                            true, true,
                                            parseInt(postData.loanType, 10));
                    }
                );
            }

            $scope.openIdentityCamera = function (ev) {
                var parentEl = angular.element(document.body);
                $mdDialog.show({
                    parent: parentEl,
                    targetEvent: ev,
                    templateUrl: 'index.php/NewRequestController/camera',
                    clickOutsideToClose: true,
                    escapeToClose: true,
                    autoWrap: true,
                    locals: {
                        obj: $scope.model // Will retain the user data.
                    },
                    controller: CameraController
                });
            };


            //Controller for camera dialog
            function CameraController($scope, $mdDialog, obj) {
                // Setup a channel to receive a video property
                // with a reference to the video element
                $scope.channel = {
                    videoHeight: 320,
                    videoWidth: 480
                };
                var _video = null;

                $scope.webcamError = false;

                $scope.picTaken = false;

                $scope.onError = function (err) {
                    $scope.webcamError = err;
                };

                $scope.onSuccess = function () {
                    // The video element contains the captured camera data
                    _video = $scope.channel.video;
                };
                $scope.closeDialog = function () {
                    $mdDialog.hide();
                    // Re-open parent dialog
                    parentScope.openNewRequestDialog(null, obj);
                };

                $scope.deletePic = function () {
                    $scope.picTaken = false;
                };

                $scope.savePic = function () {
                    updateIdPic(document.querySelector('#snapshot').toDataURL());
                    $mdDialog.hide();
                    // Re-open parent dialog
                    parentScope.openNewRequestDialog(null, obj);
                };

                $scope.takePicture = function () {
                    if (_video) {
                        var patCanvas = document.querySelector('#snapshot');
                        if (!patCanvas) return;
                        patCanvas.width = _video.width;
                        patCanvas.height = _video.height;
                        var ctxPat = patCanvas.getContext('2d');

                        var idata = getVideoData(0, 0, _video.width, _video.height);
                        ctxPat.putImageData(idata, 0, 0);
                        $scope.picTaken = true;
                    }
                };

                function getVideoData(x, y, w, h) {
                    var hiddenCanvas = document.createElement('canvas');
                    hiddenCanvas.width = _video.width;
                    hiddenCanvas.height = _video.height;
                    var ctx = hiddenCanvas.getContext('2d');
                    ctx.drawImage(_video, 0, 0, _video.width, _video.height);
                    return ctx.getImageData(x, y, w, h);
                }
            }

            // Determines whether the specified userType matches logged user's type
            $scope.userType = function (type) {
                return type === $cookies.getObject('session').type;
            };

            $scope.showHelp = function () {
                showFormHelp(Helps.getDialogsHelpOpt());
            };
            /**
             * Shows tour-based help of all input fields.
             * @param options: Obj containing tour.js options
             */
            function showFormHelp(options) {
                var trip = new Trip([], options);
                if (!$scope.missingField()) {
                    // Tell user to hit the create button
                    Helps.addFieldHelp(trip, '#create-btn',
                                       'Haga clic en CREAR para generar la solicitud.', 'n');
                    trip.start();
                } else {
                    showAllFieldsHelp(trip);
                }
            }

            function showAllFieldsHelp(tripToShowNavigation) {
                var content;
                if (!$scope.model.reqAmount) {
                    // Requested amount field
                    content = "Ingrese la cantidad de Bs. solicitado por el afiliado.";
                    Helps.addFieldHelp(tripToShowNavigation, "#req-amount", content, 's');
                }
                if (!$scope.model.phone) {
                    // Requested amount field
                    content = "Ingrese el número telefónico del afiliado, a través " +
                              "del cual se le estará contactando.";
                    Helps.addFieldHelp(tripToShowNavigation, "#phone-numb",
                                       content, 'n');
                }
                if (!$scope.idPicTaken) {
                    // Show id pic field help
                    content = "Haga click para tomar una foto al afiliado.";
                    Helps.addFieldHelp(tripToShowNavigation, "#id-pic", content, 'n');
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

    // Helper method that updates UI's request list.
    function updateRequestListUI(userId, autoSelectIndex,
                                 dialogTitle, dialogContent,
                                 updateUI, toggleList, type) {
        // Update interface
        Requests.getUserRequests(userId).then(
            function (data) {
                // Update UI only if needed
                var loanType = Requests.mapLoanTypes(type);
                if (updateUI) {
                    updateContent(data, loanType, autoSelectIndex);
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
            $scope.showList[index] = true;
        }, 1000);

    }

    function closeAllReqList() {
        $scope.selectedReq = '';
        $scope.selectedLoan = -1;
        angular.forEach($scope.showList, function (show, index) {
            $scope.showList[index] = false;
        });
    }

    /**
     * Custom dialog for updating an existing request
     */
    $scope.openEditRequestDialog = function ($event) {
        var parentEl = angular.element(document.body);
        $mdDialog.show({
            parent: parentEl,
            targetEvent: $event,
            templateUrl: 'index.php/EditRequestController',
            clickOutsideToClose: false,
            escapeToClose: false,
            locals: {
                fetchId: $scope.fetchId,
                request: $scope.requests[$scope.selectedReq][$scope.selectedLoan],
                // Request lists are ordered from newest to oldest!
                loanNumb: $scope.requests[$scope.selectedReq].length - $scope.selectedLoan
            },
            controller: DialogController
        });
        // Isolated dialog controller
        function DialogController($scope, $mdDialog, fetchId, request, loanNumb) {
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
                    var requestNumb = Requests.mapLoanTypes($scope.request.type) + '.' + loanNumb;
                    uploadFiles($scope.files, fetchId, requestNumb);
                }
            };

            // Performs the request edition update in DB
            function performEdition(postData) {
                Requests.updateRequest(postData).then(
                    function () {
                        var updateContent = $scope.files.length > 0;
                        updateRequestListUI(fetchId, loanNumb - 1,
                                            'Solicitud actualizada',
                                            'La solicitud fue actualizada exitosamente.',
                                            updateContent, false, parseInt(postData.type, 10));
                    },
                    function (errorMsg) {
                        console.log(errorMsg);
                    }
                );
            }

            // Uploads each of selected documents to the server
            function uploadFiles(files, userId, loanNumb) {
                FileUpload.uploadFiles(files, userId, loanNumb).then(
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
                    content = "Haga click para para (opcionalmente) agregar documentos " +
                              "adicionales a la solicitud.";
                    Helps.addFieldHelp(tripToShowNavigation, "#more-files", content, 's');
                } else {
                    content = "Estas tarjetas contienen el nombre y posible descripción " +
                              "de los documentos seleccionados. Puede eliminarla o proporcionar una descripción" +
                              " a través de los íconos en la parte inferior de la tarjeta.";
                    Helps.addFieldHelp(tripToShowNavigation, "#file-card", content, 'n');
                }
                if (!$scope.allFieldsMissing()) {
                    content = "Haga click en ACTUALIZAR para guardar los cambios.";
                    Helps.addFieldHelp(tripToShowNavigation, "#edit-btn", content, 'n');
                }
                tripToShowNavigation.start();
            }
        }
    };

    $scope.deleteDoc = function (ev, dKey) {
        Utils.showConfirmDialog(
            'Confirmación de eliminación',
            "El documento " +
            $scope.requests[$scope.selectedReq][$scope.selectedLoan].docs[dKey].name +
            " será eliminado.",
            'Continuar',
            'Cancelar',
            ev, true).then(
            function() {
                Requests.deleteDocument(
                    $scope.requests[$scope.selectedReq][$scope.selectedLoan].docs[dKey]
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
            'Al eliminar la solicitud, también eliminará ' +
            'todos sus documentos.',
            'Continuar',
            'Cancelar',
            ev, true).then(
            function() {
                Requests.deleteRequest($scope.requests[$scope.selectedReq][$scope.selectedLoan]).then(
                    function () {
                        // Update interface
                        $scope.docs = [];
                        updateRequestListUI($scope.fetchId, -1, 'Solicitud eliminada',
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
            templateUrl: 'index.php/EditRequestController/editionDialog',
            locals: {
                doc: doc
            },
            controller: DialogController
        });

        function DialogController($scope, $mdDialog, doc) {
            $scope.description = '';
            $scope.saveEdition = function () {
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
        // Save data before going to history page
        sessionStorage.setItem("requests", JSON.stringify($scope.requests));
        sessionStorage.setItem("fetchId", $scope.fetchId);
        sessionStorage.setItem("selectedReq", $scope.selectedReq);
        sessionStorage.setItem("selectedLoan", $scope.selectedLoan);
        sessionStorage.setItem("showList", JSON.stringify($scope.showList));

        $state.go('history');

    };

    $scope.downloadDoc = function (doc) {
        window.open(Requests.getDocDownloadUrl(doc.lpath));
    };

    $scope.downloadAll = function () {
        location.href = Requests.getAllDocsDownloadUrl($scope.docs);
    };

    $scope.loadUserData = function () {
        sessionStorage.setItem("fetchId", $scope.fetchId);
        window.open(Utils.getUserDataUrl(), '_blank');
    };

    $scope.openMenu = function () {
        $mdSidenav('left').toggle();
    };

    $scope.showHelp = function () {
        if (!$scope.contentAvailable) {
            // Indicate user to input another user's ID.
            if ($mdMedia('gt-sm')) {
                showSearchbarHelp(Helps.getDialogsHelpOpt());
            } else {
                showMobileSearchbarHelp(Helps.getDialogsHelpOpt());
            }
        } else if ($scope.docs.length == 0) {
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
                           'Haga click en la lupa e ingrese la cédula de identidad ' +
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
                               'También puede abrir una solicitud haciendo click aquí', 'w',
                               'Nueva solicitud', true);
            tripToShowNavigation.start();
        } else {
            Helps.addFieldHelp(tripToShowNavigation, '#nav-panel',
                               'Haga click en el ícono para abrir el panel de navegación,' +
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

        // Request summary information
        content = "Aquí se muestra información acerca de la fecha de creación, monto solicitado " +
                  "por usted, y un comentario de haberlo realizado.";
        Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-summary', content, 's',
                                     'Resumen de la solicitud', true);
        // Request status information
        content = "Esta sección provee información acerca del estatus de la solicitud.";
        Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-status-summary', content, 's',
                                     'Resumen de estatus', true);
        // Request documents information
        content = "Éste y los siguientes items contienen " +
                  "el nombre y, de existir, una descripción de cada documento en la solicitud. " +
                  "Puede verlos/descargarlos haciendo click encima de ellos.";
        Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-docs', content, 'n',
                                     'Documentos', true);
        // Additional documents.
        content = "Siendo un documento adicional, " +
                  "puede hacer click en el botón de opciones para proveer una descripción, " +
                  "descargarlos o incluso eliminarlos.";
        Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-docs-actions', content, responsiveNorthPos,
                                     'Documentos', true, 'fadeInLeft');
        if ($scope.docs.length < 2) {
            // This request hasn't additional documents.
            tripToShowNavigation.tripData.splice(3, 1);
        }
        if ($mdSidenav('left').isLockedOpen()) {
            content = "Puede ver el historial de la solicitud, " +
                      "editarla (si la solicitud no se ha cerrado), o descargar todos " +
                      "sus documentos presionando el botón correspondiente.";
            Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-summary-actions', content, responsiveSouthPos,
                                         'Acciones', true, 'fadeInLeft');
        } else {
            content = "Haga click en el botón de opciones para " +
                      "ver el historial de la solicitud, editarla (si la solicitud no se ha cerrado)" +
                      ", o descargar todos sus documentos.";
            Helps.addFieldHelpWithHeader(tripToShowNavigation, '#request-summary-actions-menu',
                                         content, responsiveSouthPos,
                                         'Acciones', true, 'fadeInLeft');
        }
        tripToShowNavigation.start();
    }

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

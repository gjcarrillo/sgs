angular
    .module('sgdp')
    .controller('AgentHomeController', agentHome);

agentHome.$inject = ['$scope', '$rootScope', '$mdDialog', 'Upload', '$cookies', '$http', '$state',
    '$timeout', '$mdSidenav', '$mdMedia'];

function agentHome($scope, $rootScope, $mdDialog, Upload, $cookies, $http, $state,
    $timeout, $mdSidenav, $mdMedia) {
    'use strict';
    $scope.loading = false;
    $scope.selectedReq = -1;
    $scope.requests = [];
    $scope.docs = [];
    $scope.fetchError = "";
    $scope.showList = false;
    $scope.idPrefix = "V";
    // contentAvailable will indicate whether sidenav can be visible
    $scope.contentAvailable = false;
    // contentLoaded will indicate whether sidenav can be locked open
    $scope.contentLoaded = false;

    // Check if there is stored data before we went to History
    var requests = JSON.parse(sessionStorage.getItem("requests"));
    if (requests != null) {
        $scope.requests = requests;
        $scope.fetchId = sessionStorage.getItem("fetchId");
        // fetchId is used for several database queries.
        // that is why we don't use searchInput value, which is bind to search input.
        $scope.searchInput = $scope.fetchId.replace('V', '');
        $scope.selectedReq = parseInt(sessionStorage.getItem("selectedReq"));
        $scope.docs = $scope.requests[$scope.selectedReq].docs;
        $scope.showList = parseInt(sessionStorage.getItem("showList")) ? true : false;
        $scope.contentAvailable = true;
        $scope.contentLoaded = true;
        // Got back what we wanted -- erase them from storage
        sessionStorage.removeItem("requests");
        sessionStorage.removeItem("fetchId");
        sessionStorage.removeItem("selectedReq");
        sessionStorage.removeItem("showList");
    }

    $scope.generatePdfDoc = function() {
        $http.get('index.php/documents/DocumentGenerator/generatePdf')
            .then(function (response) {
                console.log(response);
            });
    };

    $scope.getSidenavHeight = function() {
        return {
            // 129 = header and footer height, approx
            'height':($(window).height() - 129)
        };
    };

    $scope.getDocumentContainerStyle = function() {
        return {
            'background-color': '#F5F5F5',
            'max-height':($(window).height() - 129)
        };
    };

    $scope.toggleList = function() {
        $scope.showList = !$scope.showList;
    };

    $scope.selectRequest = function(req) {
        $scope.selectedReq = req;
        if (req != -1) {
            $scope.docs = $scope.requests[req].docs;
        }
        $mdSidenav('left').toggle();
    };

    $scope.fetchRequests = function(searchInput) {
        $scope.showList = false;
        $scope.contentAvailable = false;
        $scope.fetchId = $scope.idPrefix + searchInput;
        $scope.requests = [];
        $scope.selectedReq = -1;
        $scope.loading = true;
        $scope.docs = [];
        $scope.fetchError = "";
        $http.get('index.php/home/AgentHomeController/getUserRequests', {params:{fetchId:$scope.fetchId}})
            .then(function (response) {
                if (response.data.message === "success") {
                    $scope.requests = response.data.requests;
                    $scope.contentAvailable = true;
                    $timeout(function() {
                        $scope.contentLoaded = true;
                        $mdSidenav('left').open();
                    }, 300);
                } else {
                    $scope.fetchError = response.data.error;
                }
                $scope.loading = false;
            });
    };

    // Helper function for formatting numbers with leading zeros
    $scope.pad = function(n, width, z) {
        z = z || '0';
        n = n + '';
        return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
    };


    $scope.openNewRequestDialog = function($event) {
        var parentEl = angular.element(document.body);
        $mdDialog.show({
            parent: parentEl,
            targetEvent: $event,
            templateUrl: 'index.php/documents/NewRequestController',
            clickOutsideToClose: false,
            escapeToClose: false,
            autoWrap: false,
            locals:{
                fetchId:$scope.fetchId
            },
            controller: DialogController
        });
        // Isolated dialog controller for the new request dialog
        function DialogController($scope, $mdDialog, fetchId) {
            $scope.idPicTaken = false;
            $scope.docPicTaken = false;
            $scope.uploading = false;
            $scope.uploadErr = '';
            var uploadedFiles = 0;

            $scope.closeDialog = function() {
                $mdDialog.hide();
            };

            $scope.missingField = function() {
                return !$scope.idPicTaken || !$scope.docPicTaken || typeof $scope.reqAmount === "undefined";
            };

            function updateIdPic(dataURL) {
                $("#idThumbnail").attr("src", dataURL);
                $scope.idPicTaken = true;
                $scope.idData = dataURL;
            }

            function updateDocPic(dataURL){
                $("#docThumbnail").attr("src", dataURL);
                $scope.docPicTaken = true;
                $scope.docData = dataURL;
            }

            $scope.deleteIdPic = function() {
                $scope.idPicTaken = false;
            };

            $scope.deleteDocPic = function() {
                $scope.docPicTaken = false;
            };

            $scope.gatherFile = function(file, errFiles) {
                $scope.file = file;
                $scope.file.description = "Documento con datos de solicitud";
                $scope.docPicTaken = true;
                $scope.errFiles = errFiles;
            };

            $scope.removeScannedDoc = function() {
                $scope.docPicTaken = false;
                $scope.file = null;
            };

            $scope.showError = function(error, param) {
                if (error === "pattern") {
                    return "Archivo no aceptado. Por favor seleccione sólo documentos.";
                } else if (error === "maxSize") {
                    return "El archivo es muy grande. Tamaño máximo es: " + param;
                }
            };

            // Creates new request in database and uploads documents
            $scope.createNewRequest = function() {
                $scope.uploading = true;
                $http.get('index.php/documents/NewRequestController/createRequest',
                    {params:{userId:fetchId, reqAmount:$scope.reqAmount}})
                    .then(function (response) {
                        if (response.data.message== "success") {
                            uploadData(1, response.data.requestId, response.data.historyId);
                            if (!$scope.file) {
                                uploadData(2, response.data.requestId, response.data.historyId);
                            } else {
                                uploadFile($scope.file, response.data.requestId, response.data.historyId);
                            }
                        }
                    });
            };

            function uploadData(data, requestId, historyId) {
                var imageData = "";
                var docName = "";
                var description = "";
                if (data == 1) {
                    imageData = $scope.idData;
                    docName = "Identidad";
                    description = "Prueba de presencia"
                } else {
                    imageData = $scope.docData;
                    docName = "Solicitud";
                    description = "Documento con datos de solicitud"
                }
                $.ajax({
                    type: "POST",
                    url: 'index.php/documents/NewRequestController/uploadBase64Images',
                    dataType: 'text',
                    data: {
                         imageData:imageData,
                         userId:fetchId,
                         requestId:requestId,
                         docName:docName
                    },
                    success: function (response) {
                        var file = {};
                        file.lpath = JSON.parse(response).lpath;
                        file.requestId = requestId;
                        file.historyId = historyId;
                        file.docName = docName;
                        file.description = description;
                        // Doc successfully uploaded. Now create it on database.
                        $http.get('index.php/documents/NewRequestController/createDocument', {params:file})
                            .then(function (response) {
                                if (response.data.message== "success") {
                                    uploadedFiles++;
                                    console.log(uploadedFiles);
                                    if (uploadedFiles === 2) {
                                        // Update interface
                                        $http.get('index.php/home/AgentHomeController/getUserRequests', {params:{fetchId:fetchId}})
                                            .then(function (response) {
                                                if (response.data.message === "success") {
                                                    updateContent(response.data.requests, 0);
                                                    toggleReqList();
                                                    // Close dialog and alert user that operation was successful
                                                    $mdDialog.hide();
                                                    $mdDialog.show(
                                                        $mdDialog.alert()
                                                            .parent(angular.element(document.body))
                                                            .clickOutsideToClose(true)
                                                            .title('Solicitud creada')
                                                            .textContent('La solicitud ha sido creada exitosamente.')
                                                            .ariaLabel('Successful request creation dialog')
                                                            .ok('Ok')
                                                    );
                                                } else {
                                                    console.log("REFRESHING ERROR!");
                                                }
                                            });
                                    }
                                } else {
                                    console.log("Document creation in database had an error");
                                }
                            });
                    }
                });
            };
            // Uploads each of selected documents to the server
            // and updates database
            function uploadFile(file, requestId, historyId) {
                file.upload = Upload.upload({
                    url: 'index.php/documents/NewRequestController/upload',
                    data: {file: file, userId: fetchId, requestId: requestId},
                });

                file.upload.then(function (response) {
                    file.lpath = response.data.lpath;
                    file.requestId = requestId;
                    file.historyId = historyId;
                    // file.name is not passed through GET. Gotta create new property
                    file.docName = "Solicitud";
                    // Doc successfully uploaded. Now create it on database.
                    console.log(file);
                    $http.get('index.php/documents/NewRequestController/createDocument', {params:file})
                        .then(function (response) {
                            if (response.data.message== "success") {
                                uploadedFiles++;
                                if (uploadedFiles == 2) {
                                    // Update interface
                                    $http.get('index.php/home/AgentHomeController/getUserRequests', {params:{fetchId:fetchId}})
                                        .then(function (response) {
                                            if (response.data.message === "success") {
                                                updateContent(response.data.requests, 0);
                                                // Close dialog and alert user that operation was successful
                                                $mdDialog.hide();
                                                $mdDialog.show(
                                                    $mdDialog.alert()
                                                        .parent(angular.element(document.body))
                                                        .clickOutsideToClose(true)
                                                        .title('Solicitud creada')
                                                        .textContent('La solicitud ha sido creada exitosamente.')
                                                        .ariaLabel('Successful request creation dialog')
                                                        .ok('Ok')
                                                );
                                            }
                                        });
                                }
                            }
                        });
                }, function (response) {
                    if (response.status > 0)
                        $scope.errorMsg = response.status + ': ' + response.data;
                }, function (evt) {
                    file.progress = Math.min(100, parseInt(100.0 *
                                       evt.loaded / evt.total));
                });
            }

            $scope.openIdentityCamera = function(ev) {
                var parentEl = angular.element(document.body);
                $mdDialog.show({
                    parent: parentEl,
                    targetEvent: $event,
                    templateUrl: 'index.php/documents/NewRequestController/camera',
                    clickOutsideToClose: true,
                    escapeToClose: true,
                    preserveScope: true,
                    autoWrap: true,
                    skipHide: true,
                    locals: {
                        sendTo: 1 // 1 = camera result will be sent to id's variables
                    },
                    controller: CameraController
                });
            };

            $scope.openDocCamera = function(ev) {
                var parentEl = angular.element(document.body);
                $mdDialog.show({
                    parent: parentEl,
                    targetEvent: $event,
                    templateUrl: 'index.php/documents/NewRequestController/camera',
                    clickOutsideToClose: true,
                    escapeToClose: true,
                    preserveScope: true,
                    autoWrap: true,
                    skipHide: true,
                    locals: {
                        sendTo: 2 // 2 = camera result will be sent to doc's variables
                    },
                    controller: CameraController
                });

            };

            //Controller for camera dialog
            function CameraController($scope, $mdDialog, sendTo) {
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
                $scope.closeDialog = function() {
                    $mdDialog.hide();
                };

                $scope.deletePic = function() {
                    $scope.picTaken = false;
                };

                $scope.savePic = function() {
                    if (sendTo == 1) {
                        updateIdPic(document.querySelector('#snapshot').toDataURL());
                    } else {
                        updateDocPic(document.querySelector('#snapshot').toDataURL());
                    }
                    $mdDialog.hide();
                };

                $scope.takePicture = function() {
                    if (_video) {
                        var patCanvas = document.querySelector('#snapshot');
                        if (!patCanvas) return;
                        patCanvas.width = _video.width;
                        patCanvas.height = _video.height;
                        var ctxPat = patCanvas.getContext('2d');

                        var idata = getVideoData(0, 0, _video.width, _video.height);
                        ctxPat.putImageData(idata, 0, 0);

                        // sendSnapshotToServer(patCanvas.toDataURL());

                        // window.open(patCanvas.toDataURL(), '_blank');
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
                        { sel : $("#create-btn"),
                            content : "Haga click en CREAR para generar la solicitud.",
                            position : "w", animation: 'fadeInLeft'}

                    ], options);
                    tripToShowNavigation.start();
                } else {
                    var tripToShowNavigation = new Trip([], options);
                    showAllFieldsHelp(tripToShowNavigation);
                }
            }

            function showFieldHelp(trip, id, content) {
                trip.tripData.push(
                    { sel : $(id), content: content, position: "s", animation: 'fadeInUp' }
                );
            }

            function showAllFieldsHelp(tripToShowNavigation) {
                if (!$scope.reqAmount) {
                    // Requested amount field
                    var content = "Ingrese la cantidad de Bs. solicitado por el afiliado.";
                    showFieldHelp(tripToShowNavigation, "#req-amount", content);
                }
                if (!$scope.idPicTaken) {
                    // Show id pic field help
                    var content = "Haga click para tomar una foto al afiliado.";
                    showFieldHelp(tripToShowNavigation, "#id-pic", content);
                } else {
                    // Show pic result help
                    var content = "Resultado de la foto del afiliado. Si lo desea, " +
                    "puede eliminarla y volver a tomarla.";
                    showFieldHelp(tripToShowNavigation, "#id-pic-result", content);
                }
                if (!$scope.docPicTaken) {
                    // Show doc pic field help
                    var content = "Haga click para proveer el documento de la solicitud." +
                    " Puede tomarle foto o elegir el documento desde la computadora.";
                    showFieldHelp(tripToShowNavigation, "#doc-pic", content);
                } else {
                    if (!$scope.file) {
                        // Picture was taken, show pic result help
                        var content = "Resultado de la foto del documento de solicitud." +
                        " Si lo desea, puede eliminarla y volver a tomarla.";
                        showFieldHelp(tripToShowNavigation, "#doc-pic-result", content);
                    } else {
                        // doc was uploaded instead
                        var content = "Documento de solicitud seleccionado." +
                        " Si lo desea, puede eliminarlo y volver a seleccionarlo.";
                        showFieldHelp(tripToShowNavigation, "#doc-pic-selection", content);
                    }
                }
                tripToShowNavigation.start();
            }
        }
    };

    // Helper function that updates content with new request
    function updateContent(requests, selection) {
        $scope.requests = requests;
        // Automatically select created request
        $scope.selectRequest(selection);
    }

    function toggleReqList() {
        // Toggle list
        $scope.showList = false;
        $timeout(function() {
            // Toggle list again
            $scope.showList = true;
        }, 1000);

    }

    /**
    * Custom dialog for updating an existing request
    */
    $scope.openEditRequestDialog = function($event) {
        var parentEl = angular.element(document.body);
        $mdDialog.show({
            parent: parentEl,
            targetEvent: $event,
            templateUrl: 'index.php/documents/EditRequestController',
            clickOutsideToClose: false,
            escapeToClose: false,
            locals: {
                fetchId: $scope.fetchId,
                request: $scope.requests[$scope.selectedReq],
                selectedReq: $scope.selectedReq
            },
            controller: DialogController
        });
        // Isolated dialog controller
        function DialogController($scope, $mdDialog, fetchId, request, selectedReq) {
            $scope.files = [];
            $scope.selectedReq = selectedReq;
            $scope.fetchId = fetchId;
            $scope.uploading = false;
            $scope.request = request;
            $scope.enabledDescription = -1;
            $scope.statuses = ["Recibida", "Aprobada", "Rechazada"];
            $scope.comment = $scope.request.comment;

            $scope.closeDialog = function() {
                $mdDialog.hide();
            };

            $scope.removeDoc = function(index) {
                $scope.files.splice(index, 1);
            };

            $scope.isDescriptionEnabled = function(dKey) {
                return $scope.enabledDescription == dKey;
            };

            $scope.enableDescription = function(dKey) {
                $scope.enabledDescription = dKey;
                $timeout(function(){
                    $("#"+dKey).focus();
                }, 300);
            };

            $scope.allFieldsMissing = function() {
                return $scope.files.length == 0 &&
                    (typeof $scope.comment === "undefined"
                        || $scope.comment == ""
                        || $scope.comment == $scope.request.comment);
            };

            $scope.showError = function(error, param) {
                if (error === "pattern") {
                    return "Archivo no aceptado. Por favor seleccione sólo documentos.";
                } else if (error === "maxSize") {
                    return "El archivo es muy grande. Tamaño máximo es: " + param;
                }
            };
            // Gathers the files whenever the file input's content is updated
            $scope.gatherFiles = function(files, errFiles) {
                $scope.files = files;
                console.log($scope.files);
                $scope.errFiles = errFiles;
            };

            // Creates new request in database and uploads documents
            $scope.updateRequest = function() {
                $scope.uploading = true;
                $scope.request.docsAdded = $scope.files.length > 0;
                $scope.request.comment = $scope.comment;
                $http.get('index.php/documents/EditRequestController/updateRequest', {params:$scope.request})
                    .then(function (response) {
                        console.log(response.data);
                        if (response.data.message === "success") {
                            console.log("Request update succeded...");
                            if ($scope.files.length === 0) {
                                console.log("No documents to upload....");
                                // Close dialog and alert user that operation was successful
                                $mdDialog.hide();
                                $mdDialog.show(
                                    $mdDialog.alert()
                                        .parent(angular.element(document.body))
                                        .clickOutsideToClose(true)
                                        .title('Solicitud actualizada')
                                        .textContent('La solicitud fue actualizada exitosamente.')
                                        .ariaLabel('Successful request update dialog')
                                        .ok('Ok')
                                );
                            } else {
                                console.log("Uploading documents....");
                                uploadFiles($scope.fetchId, $scope.request.id, response.data.historyId);
                            }
                        } else {
                            console.log("FAILED!");
                        }
                    });
            };

            // Uploads each of selected documents to the server
            // and updates database
            function uploadFiles(userId, requestId, historyId) {
                var uploadedFiles = 0;
                angular.forEach($scope.files, function(file) {
                    file.upload = Upload.upload({
                        url: 'index.php/documents/NewRequestController/upload',
                        data: {file: file, userId: userId, requestId: requestId},
                    });
                    file.upload.then(function (response) {
                        file.lpath = response.data.lpath;
                        file.requestId = requestId;
                        file.historyId = historyId;
                        // file.name is not passed through GET. Gotta create new property
                        file.docName = file.name;
                        // Doc successfully uploaded. Now create it on database.
                        console.log(file);
                        console.log(file.name);
                        $http.get('index.php/documents/NewRequestController/createDocument', {params:file})
                            .then(function (response) {
                                if (response.data.message== "success") {
                                    uploadedFiles++;
                                    if (uploadedFiles == $scope.files.length) {
                                        // Update interface
                                        $http.get('index.php/home/AgentHomeController/getUserRequests', {params:{fetchId:$scope.fetchId}})
                                            .then(function (response) {
                                                if (response.data.message === "success") {
                                                    updateContent(response.data.requests, $scope.selectedReq);
                                                    console.log(response.data.requests);
                                                    // Close dialog and alert user that operation was successful
                                                    $mdDialog.hide();
                                                    $mdDialog.show(
                                                        $mdDialog.alert()
                                                            .parent(angular.element(document.body))
                                                            .clickOutsideToClose(true)
                                                            .title('Solicitud actualizada')
                                                            .textContent('La solicitud fue actualizada exitosamente.')
                                                            .ariaLabel('Successful request update dialog')
                                                            .ok('Ok')
                                                    );
                                                }
                                            });
                                    }
                                }
                            });
                    }, function (response) {
                        if (response.status > 0) {
                            // Show file error message
                            $scope.errorMsg = response.status + ': ' + response.data;
                        }
                    }, function (evt) {
                        // Fetch file updating progress
                        file.progress = Math.min(100, parseInt(100.0 *
                                                 evt.loaded / evt.total));
                    });
                });
            }

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
                showFormHelp(options);
            };

            /**
             * Shows tour-based help of all input fields.
             * @param options: Obj containing tour.js options
             */
            function showFormHelp(options) {
                var tripToShowNavigation = new Trip([], options);
                if (typeof $scope.comment === "undefined" || $scope.comment == ""
                    || $scope.comment == $scope.request.comment) {
                    var content = "Puede opcionalmente realizar algún comentario " +
                    "hacia la solicitud.";
                    appendFieldHelp(tripToShowNavigation, "#comment", content);
                }
                if ($scope.files.length == 0) {
                    var content = "Haga click para para agregar documentos " +
                    "adicionales a la solicitud.";
                    appendFieldHelp(tripToShowNavigation, "#more-files", content);
                } else {
                    content = "Estas tarjetas contienen el nombre y posible descripción " +
                    "de los documentos seleccionados. Puede eliminarla o proporcionar una descripción" +
                    " a través de los íconos en la parte inferior de la tarjeta."
                    appendFieldHelp(tripToShowNavigation, "#file-card", content);
                }
                if (!$scope.allFieldsMissing()) {
                    var content = "Haga click en ACTUALIZAR para guardar los cambios."
                    appendFieldHelp(tripToShowNavigation, "#edit-btn", content);
                }
                tripToShowNavigation.start();
            }

            function appendFieldHelp(trip, id, content) {
                trip.tripData.push(
                    { sel : $(id), content: content, position: "s", animation: 'fadeInUp' }
                );
            }
        }
    };

    $scope.deleteDoc = function(ev, dKey) {
         var confirm = $mdDialog.confirm()
             .title('Confirmación de eliminación')
             .textContent("El documento " + $scope.requests[$scope.selectedReq].docs[dKey].name + " será eliminado.")
             .ariaLabel('Document removal warning')
             .targetEvent(ev)
             .ok('Continuar')
             .cancel('Cancelar');
             $mdDialog.show(confirm).then(function() {
                 $http.get('index.php/home/AgentHomeController/deleteDocument',{params:$scope.requests[$scope.selectedReq].docs[dKey]})
                     .then(function(response) {
                         console.log(response)
                         if (response.data.message == "success") {
                             // Update the view
                             $http.get('index.php/home/AgentHomeController/getUserRequests', {params:{fetchId:$scope.fetchId}})
                                 .then(function (response) {
                                     if (response.data.message === "success") {
                                         updateContent(response.data.requests, $scope.selectedReq);
                                     }
                                 });
                             $mdDialog.show(
                                 $mdDialog.alert()
                                     .parent(angular.element(document.body))
                                     .clickOutsideToClose(true)
                                     .title('Documento eliminado')
                                     .textContent('El documento fue eliminado exitosamente.')
                                     .ariaLabel('Successful document removal dialog')
                                     .ok('Ok')
                                     .targetEvent(ev)
                             );
                         } else {
                             $mdDialog.show(
                                 $mdDialog.alert()
                                     .parent(angular.element(document.body))
                                     .clickOutsideToClose(true)
                                     .title('Oops!')
                                     .textContent('Ha ocurrido un error en el sistema. Por favor intente más tarde')
                                     .ariaLabel('Failed document removal dialog')
                                     .ok('Ok')
                                     .targetEvent(ev)
                             );
                         }
                     });
             });
    };

    $scope.deleteRequest = function(ev) {
        var confirm = $mdDialog.confirm()
            .title('Confirmación de eliminación')
            .textContent('Al eliminar la solicitud, también eliminará todos sus documentos.')
            .ariaLabel('Request removal warning')
            .targetEvent(ev)
            .ok('Continuar')
            .cancel('Cancelar');
            $mdDialog.show(confirm).then(function() {
                $http.get('index.php/home/AgentHomeController/deleteRequest',{params:$scope.requests[$scope.selectedReq]})
                    .then(function(response) {
                        console.log(response)
                        if (response.data.message == "success") {
                            // Update the view.
                            $scope.docs = [];
                            $http.get('index.php/home/AgentHomeController/getUserRequests', {params:{fetchId:$scope.fetchId}})
                                .then(function (response) {
                                    if (response.data.message === "success") {
                                        // Update content
                                        updateContent(response.data.requests, -1);
                                        toggleReqList();
                                    }
                                });
                                $mdDialog.show(
                                    $mdDialog.alert()
                                        .parent(angular.element(document.body))
                                        .clickOutsideToClose(true)
                                        .title('Solicitud eliminada')
                                        .textContent('La solicitud fue eliminada exitosamente.')
                                        .ariaLabel('Successful request removal dialog')
                                        .ok('Ok')
                                        .targetEvent(ev)
                                );
                        } else {
                            $mdDialog.show(
                                $mdDialog.alert()
                                    .parent(angular.element(document.body))
                                    .clickOutsideToClose(true)
                                    .title('Oops!')
                                    .textContent('Ha ocurrido un error en el sistema. Por favor intente más tarde.')
                                    .ariaLabel('Failed request removal dialog')
                                    .ok('Ok')
                                    .targetEvent(ev)
                            );
                        }
                    });
            });
    };

    /*
    * Mini custom dialog to edit a document's description
    */
    $scope.editDescription = function($event, doc) {
        var parentEl = angular.element(document.body);
        $mdDialog.show({
            parent: parentEl,
            targetEvent: $event,
            clickOutsideToClose: true,
            escapeToClose: true,
            templateUrl: 'index.php/documents/EditRequestController/editionDialog',
            locals: {
                doc: doc
            },
            controller: DialogController
        });

        function DialogController($scope, $mdDialog, doc) {
            $scope.doc = doc;

            $scope.saveEdition = function() {
                $http.get('index.php/documents/EditRequestController/updateDocDescription', {params:doc});
                $mdDialog.hide();
            }
        }
    };

    $scope.loadHistory = function() {
        // Save data before going to history page
        sessionStorage.setItem("requests", JSON.stringify($scope.requests));
        sessionStorage.setItem("fetchId", $scope.fetchId);
        sessionStorage.setItem("selectedReq", $scope.selectedReq);
        sessionStorage.setItem("showList", $scope.showList ? 1 : 0);

        $state.go('history');

    };

    $scope.downloadDoc = function(doc) {
        window.open('index.php/home/UserHomeController/download?lpath=' + doc.lpath, '_blank');
    };

    $scope.downloadAll = function() {
        // Bits of pre-processing before passing objects to URL
        var paths = new Array();
        angular.forEach($scope.docs, function(doc) {
            paths.push(doc.lpath);
        });
        location.href = 'index.php/home/UserHomeController/downloadAll?docs=' + JSON.stringify(paths);
    };

    $scope.loadUserData = function() {
        sessionStorage.setItem("fetchId", $scope.fetchId);
        window.open('http://localhost:8080/sgdp/#/userInfo', '_blank');
    };

    $scope.onIdOpen = function() {
        $scope.backup = $scope.idPrefix;
        $scope.idPrefix = null;
    };

    $scope.onIdClose = function() {
        if ($scope.idPrefix === null) {
            $scope.idPrefix = $scope.backup;
        }
    };

    $scope.openMenu = function() {
       $mdSidenav('left').toggle();
    };

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
        if (!$scope.contentAvailable) {
            // Indicate user to input another user's ID.
            showSearchbarHelp(options);
        } else if ($scope.docs.length == 0) {
            // User has not selected any request yet, tell him to do it.
            showSidenavHelp(options);
        } else {
            // Guide user through request selection's possible actions.
            showRequestHelp(options);
        }
    };

    /**
     * Shows tour-based help of searchbar
     * @param options: Obj containing tour.js options
     */
    function showSearchbarHelp(options) {
        if ($mdMedia('gt-xs')) {
            var id = "#search";
        } else {
            var id = "#search-mobile";
        }
        var tripToShowNavigation = new Trip([
            { sel : $(id),
                content : "Ingrese la cédula de identidad de algún afiliado para " +
                "gestionar sus solicitudes.",
                position : "s", animation: 'fadeInDown' }
        ], options);
        tripToShowNavigation.start();
    }

    /**
     * Shows tour-based help of side navigation panel
     * @param options: Obj containing tour.js options
     */
    function showSidenavHelp(options) {
        if ($mdSidenav('left').isLockedOpen()) {
            options.showHeader = true;
            var tripToShowNavigation = new Trip([
                { sel : $("#requests-list"),
                    content : "Consulte datos de interés del afiliado, o seleccione " +
                    "alguna de sus solicitudes en la lista para ver más detalles.",
                    position : "e", expose : true, header: "Panel de navegación", animation: 'fadeInUp' }
            ], options);
            tripToShowNavigation.start();
        } else {
            var tripToShowNavigation = new Trip([
                { sel : $("#nav-panel"),
                    content : "Haga click en el ícono para abrir el panel de navegación," +
                    " donde podrá consultar datos del afiliado o gestionar sus solicitudes.",
                    position : "e", animation: 'fadeInUp'}
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
        // options.showSteps = true;
        var tripToShowNavigation = new Trip([
            // Request summary information
            { sel : $("#request-summary"), content : "Aquí se muestra información acerca de " +
                "la fecha de creación, monto solicitado, y un comentario de haberlo realizado.",
                position : "s", header: "Resumen de la solicitud", expose : true },
            // Request status information
            { sel : $("#request-status-summary"), content : "Esta sección provee información " +
                "acerca del estatus de la solicitud.",
                position : "s", header: "Resumen de estatus", expose : true, animation: 'fadeInDown' },
            // Request documents information
            { sel : $("#request-docs"), content : "Éste y los siguientes items contienen " +
                "el nombre y, de existir, una descripción de cada documento en la solicitud. " +
                "Puede verlos/descargarlos haciendo click encima de ellos.",
                position : "s", header: "Documentos", expose : true, animation: 'fadeInDown' },
            // Request documents actions
            { sel : $("#request-docs-actions"), content : "Siendo un documento adicional, " +
                "puede hacer click en el botón de opciones para proveer una descripción, " +
                "descargarlos o incluso eliminarlos.",
                position : "w", header: "Documentos", expose : true, animation: 'fadeInLeft' },
        ], options);
        if ($scope.docs.length < 3) {
            // This request hasn't additional documents.
            tripToShowNavigation.tripData.splice(3, 1);
        }
        if ($mdSidenav('left').isLockedOpen()) {
            tripToShowNavigation.tripData.push(
                // Download as zip information
                { sel : $("#request-summary-actions"), content : "Puede ver el historial de la solicitud, " +
                    "editarla (si la solicitud no se ha cerrado), o descargar todos " +
                    "sus documentos presionando el botón correspondiente.",
                    position : "w", header: "Acciones", expose : true, animation: 'fadeInLeft' }
            );
        } else {
            tripToShowNavigation.tripData.push(
                // Download as zip information request-summary-actions-menu
                { sel : $("#request-summary-actions-menu"), content : "Haga click en el botón de opciones para " +
                    "ver el historial de la solicitud, editarla (si la solicitud no se ha cerrado)" +
                    ", o descargar todos sus documentos.",
                    position : "w", header: "Acciones", expose : true, animation: 'fadeInLeft' }
            );
        }
        tripToShowNavigation.start();
    }
}

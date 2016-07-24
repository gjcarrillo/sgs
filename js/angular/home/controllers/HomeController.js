angular
    .module('sgdp')
    .controller('HomeController', home);

home.$inject = ['$scope', '$rootScope', '$mdDialog', 'Upload', '$cookies', '$http'];

function home($scope, $rootScope, $mdDialog, Upload, $cookies, $http) {
    'use strict';

    $scope.isOpen = false;
    $scope.loading = false;
    $scope.selectedReq = -1;
    $scope.requests = [];
    $scope.docs = [];
    $scope.request = {};

    $scope.getSidenavHeight = function() {
        return {
            // 129 = header and footer height, approx
            'height':($(window).height() - 129)
        };
    }

    $scope.getDocumentContainerStyle = function() {
        return {
            'background-color': '#F5F5F5',
            'max-height':($(window).height() - 129)
        };
    }

    $scope.selectRequest = function(req) {
        $scope.selectedReq = req;
        if (req != -1) {
            console.log(req);
            console.log($scope.requests[req]);
            $scope.docs = $scope.requests[req].docs;
        }
    };

    $scope.fetchRequests = function(searchInput) {
        $scope.fetchId = searchInput;
        $scope.requests = [];
        $scope.selectedReq = -1;
        $scope.loading = true;
        $scope.docs = [];
        $scope.fetchError = "";
        $http.get('index.php/home/HomeController/getUserRequests', {params:{fetchId:$scope.fetchId}})
            .then(function (response) {
                if (response.data.message === "success") {
                    $scope.requests = response.data.requests;
                    console.log($scope.requests);
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
    }

    /**
    * Custom dialog for creating a new request
    */
    $scope.openNewRequestDialog = function($event) {
        var parentEl = angular.element(document.body);
        $mdDialog.show({
            parent: parentEl,
            targetEvent: $event,
            templateUrl: 'index.php/documents/NewRequestController',
            clickOutsideToClose: false,
            escapeToClose: false,
            locals:{
                fetchId:$scope.fetchId
            },
            controller: DialogController
        });
        // Isolated dialog controller
        function DialogController($scope, $mdDialog, fetchId) {
            $scope.files = [];
            $scope.fetchId = fetchId;
            $scope.uploading = false;
            $scope.enabledDescription = -1;

            $scope.closeDialog = function() {
                $mdDialog.hide();
            };

            $scope.removeDoc = function(index) {
                $scope.files.splice(index, 1);
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
            $scope.createNewRequest = function() {
                $scope.uploading = true;
                console.log($scope.files);
                $http.get('index.php/documents/NewRequestController/createRequest', {params:{userId:$scope.fetchId}})
                    .then(function (response) {
                        if (response.data.message== "success") {
                            $scope.requestId = response.data.requestId;
                            uploadFiles($scope.fetchId, $scope.requestId);
                        }
                    });
            };

            // Uploads each of selected documents to the server
            // and updates database
            function uploadFiles(userId, requestId) {
                var uploadedFiles = 0;
                angular.forEach($scope.files, function(file) {
                    file.upload = Upload.upload({
                        url: 'index.php/documents/NewRequestController/upload',
                        data: {file: file, userId: userId, requestId: requestId},
                    });
                    file.upload.then(function (response) {
                        file.lpath = response.data.lpath;
                        file.requestId = $scope.requestId;
                        // file.name is not passed through GET. Gotta create new property
                        file.docName = file.name;
                        // Doc successfully uploaded. Now create it on database.
                        $http.get('index.php/documents/NewRequestController/createDocument', {params:file})
                            .then(function (response) {
                                if (response.data.message== "success") {
                                    uploadedFiles++;
                                    if (uploadedFiles === $scope.files.length) {
                                        // Update interface
                                        $http.get('index.php/home/HomeController/getUserRequests', {params:{fetchId:$scope.fetchId}})
                                            .then(function (response) {
                                                if (response.data.message === "success") {
                                                    updateContent(response.data.requests, response.data.requests.length-1);
                                                    console.log(response.data.requests);
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
        }
    };

    // Helper function that updates content with new request
    function updateContent(requests, selection) {
        $scope.requests = requests;
        // Automatically select created request
        $scope.selectRequest(selection);
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

            $scope.closeDialog = function() {
                $mdDialog.hide();
            };

            $scope.removeDoc = function(index) {
                $scope.files.splice(index, 1);
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
                console.log($scope.files);
                console.log($scope.request.id);
                $http.get('index.php/documents/EditRequestController/updateRequest', {params:$scope.request})
                    .then(function (response) {
                        if (response.data.message== "success") {
                            if ($scope.files.length === 0) {
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
                                uploadFiles($scope.fetchId, $scope.request.id);
                            }
                        }
                    });
            };

            // Uploads each of selected documents to the server
            // and updates database
            function uploadFiles(userId, requestId) {
                var uploadedFiles = 0;
                angular.forEach($scope.files, function(file) {
                    file.upload = Upload.upload({
                        url: 'index.php/documents/NewRequestController/upload',
                        data: {file: file, userId: userId, requestId: requestId},
                    });
                    file.upload.then(function (response) {
                        file.lpath = response.data.lpath;
                        file.requestId = requestId;
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
                                        $http.get('index.php/home/HomeController/getUserRequests', {params:{fetchId:$scope.fetchId}})
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
                 $http.get('index.php/home/HomeController/deleteDocument',{params:$scope.requests[$scope.selectedReq].docs[dKey]})
                     .then(function(response) {
                         console.log(response)
                         if (response.data.message == "success") {
                             // Update the view
                             $http.get('index.php/home/HomeController/getUserRequests', {params:{fetchId:$scope.fetchId}})
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
                $http.get('index.php/home/HomeController/deleteRequest',{params:$scope.requests[$scope.selectedReq]})
                    .then(function(response) {
                        console.log(response)
                        if (response.data.message == "success") {
                            // Update the view.
                            $scope.docs = [];
                            $http.get('index.php/home/HomeController/getUserRequests', {params:{fetchId:$scope.fetchId}})
                                .then(function (response) {
                                    if (response.data.message === "success") {
                                        // Update content
                                        updateContent(response.data.requests, -1);
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

}

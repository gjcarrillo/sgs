angular
    .module('sgdp')
    .controller('HomeController', home);

home.$inject = ['$scope', '$rootScope', '$timeout', '$mdDialog', 'Upload', '$cookies', '$http'];

function home($scope, $rootScope, $timeout, $mdDialog, Upload, $cookies, $http) {
    'use strict';

    $scope.isOpen = false;
    $scope.states = ["Recibido", "Aprobado", "Rechazado"]
    $scope.request = {};
    // EXAMPLE DATA
    $scope.request.createdDate = '19/04/2014';
    $scope.request.state = $scope.states[1];
    $scope.request.docs = {};
    // END OF EXAMPLE DATA

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

    // On opening, add a delayed property which shows tooltips after the speed dial has opened
    // so that they have the proper position; if closing, immediately hide the tooltips
    $scope.$watch('fab.isOpen', function(isOpen) {
        if (isOpen) {
            $timeout(function() {
                $scope.tooltipVisible = true;
            }, 600);
        } else {
            $scope.tooltipVisible = false;
        }
    });

    /**
    * Custom dialog for creating a new request
    */
    $scope.openNewRequestDialog = function($event) {
        var parentEl = angular.element(document.body);
        $mdDialog.show({
            parent: parentEl,
            targetEvent: $event,
            templateUrl: 'templates/dialogs/newRequest.html',
            clickOutsideToClose: false,
            escapeToClose: false,
            controller: DialogController
        });
        // Isolated dialog controller
        function DialogController($scope, $mdDialog) {
            $scope.files = [];
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

            $scope.createNewRequest = function() {
                $scope.uploading = true;
                console.log($scope.files);
                var userId = $cookies.getObject("session").id;
                $http.get('index.php/home/HomeController/createRequest', {params:{userId:userId}})
                    .then(function (response) {
                        if (response.data.message== "success") {
                            $scope.requestId = response.data.requestId;
                            console.log($scope.requestId);
                            uploadFiles(userId, $scope.requestId);
                        }
                    });
            };

            $scope.gatherFiles = function(files, errFiles) {
                $scope.files = files;
                console.log($scope.files);
                $scope.errFiles = errFiles;
            };

            function uploadFiles(userId, requestId) {
                var uploadedFiles = 0;
                angular.forEach($scope.files, function(file) {
                    file.upload = Upload.upload({
                        url: 'index.php/home/HomeController/upload',
                        data: {file: file, userId: userId, requestId: requestId},
                    });
                    file.upload.then(function (response) {
                        file.lpath = response.data.lpath;
                        file.requestId = $scope.requestId;
                        // file.name is not passed through GET. Gotta create new property
                        file.docName = file.name;
                        uploadedFiles++;
                        // Doc successfully uploaded. Now create it on database.
                        console.log(file);
                        console.log(file.name);
                        $http.get('index.php/home/HomeController/createDocument', {params:file})
                            .then(function (response) {
                                if (response.data.message== "success") {
                                    if (uploadedFiles == $scope.files.length) {
                                        // Close dialog and alert user once everything is completed
                                        $mdDialog.hide();
                                        swal("Solicitud creada", "La solicitud ha sido creada exitosamente.", "success");
                                    }
                                }
                            });
                    }, function (response) {
                        if (response.status > 0)
                            $scope.errorMsg = response.status + ': ' + response.data;
                            console.log($scope.errorMsg);
                    }, function (evt) {
                        file.progress = Math.min(100, parseInt(100.0 *
                                                 evt.loaded / evt.total));
                    });
                });
            }
        }
    };

    $scope.openEditRequestDialog = function($event) {
        var parentEl = angular.element(document.body);
        $mdDialog.show({
            parent: parentEl,
            targetEvent: $event,
            templateUrl: 'templates/dialogs/editRequest.html',
            clickOutsideToClose: true,
            locals: {
                request: $scope.request,
                states: $scope.states
            },
            controller: DialogController
        });
        // Isolated dialog controller
        function DialogController($scope, $mdDialog, request, states) {
            $scope.request = request;
            $scope.states = states;
            // TODO: files = $scope.request.docs;
            $scope.enabledDescription = -1;

            $scope.closeDialog = function() {
                $mdDialog.hide();
            };

            $scope.removeDoc = function(index) {
                $scope.files.splice(index, 1);
            }

            $scope.updateRequest = function() {
                // TODO: Send files to server & update database
            }
        }
    };

    $scope.deleteRequest = function(index) {
           swal({
            title: "Confirmación",
            text: "La solicitud seleccionada será eliminada del sistema. ¿Desea proceder?",
            type: "warning",
            confirmButtonText: "Sí",
            cancelButtonText: "No",
            showCancelButton: true,
            closeOnConfirm: false,
            animation: "slide-from-top",
            showLoaderOnConfirm: true,

        }, function() {


            // $http.get('index.php/configuration/TicketsConfigController/delete',{params:{id:$scope.ticketTypes[index].id}})
            // .then(function(response) {
            //     console.log(response)
            //     if (response.data.message == "success") {
            //     $http.get('index.php/configuration/TicketsConfigController/getTicketTypes')
            //     .then(function(response){
            //     if(response.data.message === "success") {
            //     $scope.ticketTypes = response.data.data;
            //     $scope.edit = false;
            //     initializeChipsContainers();
            //     // Look for active one.
            //     for (var i = 0; i < $scope.ticketTypes.length; i++) {
            //     if ($scope.ticketTypes[i].active) {
            //         $scope.active = i;
            //     }
            //     }
            //     }
            //     })
            //     swal("Solicitud eliminada", "La solicitud selecionada ha sido eliminada exitosamente.", "success");
            //     } else {
            //     swal("Oops!", "Ha ocurrido un error y su solicitud no ha podido ser procesada. Por favor intente más tarde.", "error");
            //     }
            // })

        });
    };

}

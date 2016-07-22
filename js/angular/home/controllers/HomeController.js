angular
    .module('sgdp')
    .controller('HomeController', home);

home.$inject = ['$scope', '$rootScope', '$timeout', '$mdDialog'];

function home($scope, $rootScope, $timeout, $mdDialog) {
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

    /**
    * Custom dialog for creating a new request
    */
    $scope.openNewRequestDialog = function($event) {
        var parentEl = angular.element(document.body);
        $mdDialog.show({
            parent: parentEl,
            targetEvent: $event,
            templateUrl: 'templates/dialogs/newRequest.html',
            clickOutsideToClose: true,
            controller: DialogController
        });
        // Isolated dialog controller
        function DialogController($scope, $mdDialog) {
            $scope.enabledDescription = -1;

            $scope.closeDialog = function() {
                $mdDialog.hide();
            };

            $scope.removeDoc = function(index) {
                $scope.files.splice(index, 1);
            }

            $scope.createNewRequest = function() {
                // TODO: Send files to server & update database
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

            $scope.updateRequet = function() {
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
            $timeout(function() {
                swal("Solicitud eliminada", "La solicitud selecionada ha sido eliminada exitosamente.", "success");
            }, 600);
            // $http.get('index.php/configuration/TicketsConfigController/delete',{params:{id:$scope.ticketTypes[index].id}})
            // .then(function(response) {
            //     console.log(response)
            //     if (response.data.message == "success") {
            //         $http.get('index.php/configuration/TicketsConfigController/getTicketTypes')
            //             .then(function(response) {
            //             if(response.data.message === "success") {
            //                 $scope.ticketTypes = response.data.data;
            //                 $scope.edit = false;
            //                 initializeChipsContainers();
            //                 // Look for active one.
            //                 for (var i = 0; i < $scope.ticketTypes.length; i++) {
            //                     if ($scope.ticketTypes[i].active) {
            //                         $scope.active = i;
            //                     }
            //                 }
            //             }
            //             })
            //         swal("Solicitud eliminada", "La solicitud selecionada ha sido eliminada exitosamente.", "success");
            //     } else {
            //         swal("Oops!", "Ha ocurrido un error y su solicitud no ha podido ser procesada. Por favor intente más tarde.", "error");
            //     }
            // })

        });
    };

}

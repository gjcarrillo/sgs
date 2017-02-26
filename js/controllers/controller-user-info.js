angular
    .module('sgdp')
    .controller('UserInfoController', info);

info.$inject = ['$scope', '$http', '$mdMedia', 'Utils'];

function info($scope, $http, $mdMedia, Utils) {
    'use strict';

    // If no data has been sent, show nothing.
    if (sessionStorage.getItem("fetchId") === null) { return; }

    $scope.loading = true;
    // Take the stored data of interest
    var fetchId = sessionStorage.getItem("fetchId");
    $http.get('UserInfoController/getUserInfo', {params:{userId:fetchId}})
        .then(function(response) {
            console.log(response);
            if (response.data.message = "success") {
                $scope.userData = response.data.data;
                $scope.userName = response.data.userName;
            } else {
                Utils.showAlertDialog('Oops!', response.data.message);
            }
            $scope.loading = false;
        });

    $scope.getConcurranceWarn = function() {
        if ($scope.userData.concurrencia < 15) {
            return {'color':'green'};
        } else if ($scope.userData.concurrencia >= 15 && $scope.userData.concurrencia < 35) {
            return {'color':'orange'};
        } else {
            return {'color':'red'};
        }
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

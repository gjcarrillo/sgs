angular
    .module('sgdp')
    .controller('DocumentGenerator', generator);

generator.$inject = ['$scope', '$rootScope', '$http'];

function generator($scope, $rootScope, $http) {
    'use strict';

    var today = new Date();
    $scope.model = {
        lastname : "",
        surname : "",
        marriedName : "",
        firstname : "",
        middlename : "",
        id : "",
        date : today.getDate() + "/" + (today.getMonth()+1) + "/" + today.getFullYear(),
        basicSalary : "",
        dependancy : "",
        address : "",
        phone : "",
        requestedAmount : "",
        paymentMode : ""
    };

    $scope.loading = false;

    $scope.generatePdfDoc = function() {
        $scope.loading = true;
        $http.get('index.php/documents/DocumentGenerator/generatePdf', {params:$scope.model})
            .then(function (response) {
                if (response.data.message = "success") {
                    console.log(response);
                    location.href = 'index.php/documents/DocumentGenerator/download?docName=' + response.data.docName;
                }
                $scope.loading = false;
            });
    };
}

/**
 * Created by Kristopher on 12/5/2016.
 */
angular
    .module('sgdp.service-validation', [])
    .factory('Validation', validation);

validation.$inject = ['$q', '$http'];

function validation($q, $http) {
    'use strict';

    var self = this;

    self.validate = function(token) {
        var qVal = $q.defer();

        $http.get('index.php/ValidationController/validate', {params: {token: token}})
            .then(
            function (response) {
                console.log(response);
                if (response.data.message === "success") {
                    qVal.resolve();
                } else {
                    qVal.reject(response.data.message);
                }
            }
        );
        return qVal.promise;
    };

    return self;
}
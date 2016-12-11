/**
 * Created by Kristopher on 12/10/2016.
 */
angular
    .module('sgdp.service-delete', [])
    .factory('Delete', eliminate);

eliminate.$inject = ['$q', '$http'];

function eliminate($q, $http) {
    'use strict';

    var self = this;

    /**
     * Eliminates the specified request from the system.
     *
     * @param rid - request id.
     * @returns {*} promise containing the operation's result.
     */
    self.deleteRequest = function (rid) {
        var qEliminate = $q.defer();
        $http.post('index.php/DeleteController/deleteRequest', {rid: rid})
            .then(
            function (response) {
                console.log(response);
                if (response.data.message == 'success') {
                    qEliminate.resolve();
                } else {
                    qEliminate.reject(response.data.message);
                }
            }

        );

        return qEliminate.promise;
    };

    return self;
}
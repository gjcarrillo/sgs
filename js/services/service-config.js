/**
 * Created by Kristopher on 1/18/2017.
 */
angular
    .module('sgdp.service-config', [])
    .factory('Config', config);

config.$inject = ['$http', '$q'];

function config ($http, $q) {
    var self = this;


    /**
     * Fetches all the existing statuses configuration.
     *
     * @returns {*} promise with the operation's result.
     */
    self.getStatuses = function () {
        var qStatuses = $q.defer();
        $http.get('index.php/ConfigController/getStatuses')
            .then(
            function (response) {
                if (response.data.message === "success") {
                    qStatuses.resolve(response.data.statuses);
                } else {
                    qStatuses.reject(response.data.message);
                }
            });

        return qStatuses.promise;
    };

    /**
     * Fetches all the existing statuses configuration.
     * Returns all existing status configuration and indicates if they're being used.
     *
     * @returns {*} promise with the operation's result.
     */
    self.getStatusesForConfig = function() {
        var qStatuses = $q.defer();
        $http.get('index.php/ConfigController/getStatusesForConfig')
            .then(
            function (response) {
                console.log(response);
                if (response.data.message === "success") {
                    qStatuses.resolve(response.data.statuses);
                } else {
                    qStatuses.reject(response.data.message);
                }
            });

        return qStatuses.promise;
    };

    /**
     * Saves all the additional request statuses the user specified.
     *
     * @param statuses - Array of additional statuses.
     * @returns {*} - promise with the operation's result.
     */
    self.saveStatuses = function (statuses) {
        var qStatuses = $q.defer();
        $http.post('index.php/ConfigController/saveStatuses', {statuses: statuses})
            .then(
            function (response) {
                if (response.data.message === "success") {
                    qStatuses.resolve();
                } else {
                    console.log(response);
                    qStatuses.reject(response.data.message);
                }
            });
        return qStatuses.promise;
    };

    /**
     * Fetches the max. possible amount of money a user can request.
     *
     * @returns {*} - promise with the operation's result.
     */
    self.getMaxReqAmount = function () {
        var qReqAmount = $q.defer();
        $http.get('index.php/ConfigController/getMaxReqAmount')
            .then(
            function (response) {
                if (response.data.message === "success") {
                    qReqAmount.resolve(parseInt(response.data.maxAmount, 10));
                } else {
                    qReqAmount.reject(response.data.message);
                }
            });

        return qReqAmount.promise;
    };

    /**
     * Fetches the min. possible amount of money a user can request.
     *
     * @returns {*} - promise with the operation's result.
     */
    self.getMinReqAmount = function () {
        var qReqAmount = $q.defer();
        $http.get('index.php/ConfigController/getMinReqAmount')
            .then(
            function (response) {
                if (response.data.message === "success") {
                    qReqAmount.resolve(parseInt(response.data.minAmount, 10));
                } else {
                    qReqAmount.reject(response.data.message);
                }
            });

        return qReqAmount.promise;
    };

    /**
     * Updates both min. amount and max. amount of money a user can request.
     *
     * @param minAmount - min amount a user can request.
     * @param maxAmount - max amount a user can request.
     * @returns {*} - promise with the operation's result.
     */
    self.updateReqAmount = function (minAmount, maxAmount) {
        var qReqAmount = $q.defer();
        $http.post('index.php/ConfigController/setReqAmount',
            {minAmount: minAmount, maxAmount: maxAmount})
            .then(
            function (response) {
                if (response.data.message === "success") {
                    qReqAmount.resolve();
                } else {
                    qReqAmount.reject();
                }
            });

        return qReqAmount.promise;
    };
    return self;
}
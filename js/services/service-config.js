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
     *
     * @param statuses
     * @returns {*}
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
     *
     * @returns {*}
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
     *
     * @returns {*}
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
     *
     * @param minAmount
     * @param maxAmount
     * @returns {*}
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
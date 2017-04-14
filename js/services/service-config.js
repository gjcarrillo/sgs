/**
 * Created by Kristopher on 1/18/2017.
 */
angular
    .module('sgdp.service-config', [])
    .factory('Config', config);

config.$inject = ['$http', '$q'];

function config ($http, $q) {
    var self = this;

    self.loanConcepts = null;

    /**
     * Fetches all the existing statuses configuration.
     *
     * @returns {*} promise with the operation's result.
     */
    self.getStatuses = function () {
        var qStatuses = $q.defer();
        $http.get('ConfigController/getStatuses')
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
     * Obtains the available loan types.
     * @returns {*}
     */
    self.getLoanTypes = function () {
        var qReq = $q.defer();
        $http.get('configController/getLoanTypes').then(
            function (response) {
                console.log(response);
                if (response.data.message = "success") {
                    qReq.resolve(response.data.type);
                } else {
                    qReq.reject(response.data.message);
                }
            }
        );
        return qReq.promise;
    };

    /**
     * Fetches all the existing statuses configuration.
     * Returns all existing status configuration and indicates if they're being used.
     *
     * @returns {*} promise with the operation's result.
     */
    self.getStatusesForConfig = function() {
        var qStatuses = $q.defer();
        $http.get('ConfigController/getStatusesForConfig')
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
     * Saves all the additional request statuses the user specified.
     *
     * @param statuses - Array of additional statuses.
     * @returns {*} - promise with the operation's result.
     */
    self.saveStatuses = function (statuses) {
        var qStatuses = $q.defer();
        $http.post('ConfigController/saveStatuses', {statuses: statuses})
            .then(
            function (response) {
                if (response.data.message === "success") {
                    qStatuses.resolve();
                } else {
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
    self.getCashVoucherPercentage = function () {
        var qReqAmount = $q.defer();
        $http.get('ConfigController/getCashVoucherPercentage')
            .then(
            function (response) {
                if (response.data.message === "success") {
                    qReqAmount.resolve(parseInt(response.data.percentage, 10));
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
        $http.post('ConfigController/setReqAmount',
            {minAmount: minAmount, maxAmount: maxAmount})
            .then(
            function (response) {
                if (response.data.message === "success") {
                    qReqAmount.resolve();
                } else {
                    qReqAmount.reject(response.data.message);
                }
            });

        return qReqAmount.promise;
    };

    /**
     * Gets the configured month span required for applying to same type of loan once again.
     *
     * @returns {*} - promise with the operation's result.
     */
    self.getRequestsSpan = function () {
        var qSpan = $q.defer();
        $http.get('ConfigController/getRequestsSpan')
            .then(
            function (response) {
                console.log(response);
                if (response.data.message === "success") {
                    qSpan.resolve(response.data.loanTypes);
                } else {
                    qSpan.reject(response.data.message);
                }
            });

        return qSpan.promise;
    };

    /**
     * Obtains the configured rquests terms.
     *
     * @returns {*} - promise with the operation's result.
     */
    self.getRequestsTerms = function () {
        var qTerm = $q.defer();
        $http.get('ConfigController/getRequestsTerms')
            .then(
            function (response) {
                console.log(response);
                if (response.data.message === "success") {
                    qTerm.resolve(response.data.loanTypes);
                } else {
                    qTerm.reject(response.data.message);
                }
            });
        return qTerm.promise;
    };

    /**
     * Updates all the available request types' payment terms.
     *
     * @param terms - the updated payment terms of all available request types.
     * @returns {*} - promise with the operation's result.
     */
    self.updateRequestsTerms = function (terms) {
        var qTerm = $q.defer();
        $http.post('ConfigController/updateRequestsTerms', {terms: terms})
            .then(
            function (response) {
                console.log(response);
                if (response.data.message === "success") {
                    qTerm.resolve();
                } else {
                    qTerm.reject(response.data.message);
                }
            });
        return qTerm.promise;
    };

    /**
     * Updates the requests month span required for applying to same type of loan once again.
     *
     * @param span - time in months.
     * @returns {*} promise with the operation's result.
     */
    self.updateRequestsSpan = function (span) {
        var qSpan = $q.defer();
        $http.post('ConfigController/updateRequestsSpan', {span: span})
            .then(
            function (response) {
                if (response.data.message === "success") {
                    qSpan.resolve();
                } else {
                    qSpan.reject(response.data.message);
                }
            });

        return qSpan.promise;
    };
    return self;
}
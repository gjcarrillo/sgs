angular.module('sgdp.service-reviser', []).factory('Reviser', function ($http, $q, Requests) {

    var self = this;

    // Data initialization.
    var data = {};
    data.requests = {};
    data.loanTypes = null;

    self.data = data;

    /**
     * Updates the data.
     *
     * @param data - data to be updated.
     */
    self.updateData = function (data) {
        self.data = data;
    };

    /**
     * Clears the service's data (i.e. re-initializes it)
     */
    self.clearData = function () {
        data.requests = {};
        data.loanTypes = null;
    };

    /**
     * Obtains all currently pre-approved requests.
     * @returns {*}
     */
    self.getPreApprovedRequests = function () {
        var qReq = $q.defer();

        $http.get('reviserHomeController/getPreApprovedRequests').then(
            function (response) {
                if (response.data.message == "success") {
                    qReq.resolve(Requests.filterRequests(response.data.requests));
                } else {
                    qReq.reject(response.data.message);
                }
            }
        );
        return qReq.promise;
    };

    return self;
});
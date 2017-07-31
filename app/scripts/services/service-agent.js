/**
 * Created by Kristopher on 11/27/2016.
 */
angular.module('sgdp.service-agent', []).factory('Agent', function ($http, $q, Constants) {
    var self = this;

    // Data initialization.
    var data = {};
    data.fetchId = '';
    data.idPrefix = 'V';
    data.searchInput = '';
    // This will enable / disable search bar in mobile screens
    data.searchEnabled = false;
    // End of data initialization.

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
        var data = {};
        data.fetchId = '';
        data.idPrefix = 'V';
        data.searchInput = '';
        data.searchEnabled = false;
    };

    /**
     * Validates whether user exists in system (ipapedi db or sgs db).
     *
     * @param uid - user's id.
     * @returns {*} - promise with the operation's result.
     */
    self.validateUser = function (uid) {
        var qUser = $q.defer();

        $http.get(Constants.SERVER_URL + 'agentHomeController/validateUser', {params: {uid: uid}}).then(
            function (response) {
                if (response.data.message == 'success') {
                    qUser.resolve();
                } else {
                    qUser.reject(response.data.message);
                }
            }
        );
        return qUser.promise;
    };

    return self;
});
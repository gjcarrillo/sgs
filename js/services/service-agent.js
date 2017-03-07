/**
 * Created by Kristopher on 11/27/2016.
 */
angular
    .module('sgdp.service-agent', [])
    .factory('Agent', agent);

agent.$inject = ['Requests'];

function agent (Requests) {

    var self = this;

    // Data initialization.
    var data = {};
    data.selectedReq = '';
    data.selectedLoan = -1;
    data.requests = {};
    data.req = null; // Will contain the selected request object.
    data.fetchError = '';
    data.showList = Requests.initializeListType();
    data.fetchId = '';
    data.searchInput = '';
    // contentAvailable will indicate whether sidenav can be visible
    data.contentAvailable = false;
    // contentLoaded will indicate whether sidenav can be locked open
    data.contentLoaded = false;
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
        self.data.selectedReq = '';
        self.data.selectedLoan = -1;
        self.data.requests = {};
        self.data.req = null;
        self.data.fetchError = '';
        self.data.showList = Requests.initializeListType();
        self.data.fetchId = '';
        self.data.searchInput = '';
        self.data.contentAvailable = false;
        self.data.contentLoaded = false;
        self.data.searchEnabled = false;
    };

    return self;
}
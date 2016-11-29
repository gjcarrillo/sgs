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
    data.req = {}; // Will containg the selected request object.
    data.fetchError = '';
    data.showList = Requests.initializeListType();
    data.fetchId = '';
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
    self.updateData = function(data) {
        self.data = data;
    };

    return self;
}
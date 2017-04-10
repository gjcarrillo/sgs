/**
 * Created by Kristopher on 4/3/2017.
 */
angular
    .module('sgdp.service-applicant', [])
    .factory('Applicant', applicant);

applicant.$inject = [];

function applicant () {

    var self = this;

    // Data initialization.
    var data = {};
    data.fetchError = '';
    data.loading = true;
    data.selectedReq = '';
    data.selectedLoan = -1;
    data.selectedAction = -1;
    data.requests = {};
    data.req = null;
    data.loanTypes = null;
    data.queryList = [
        {id: 1, text: 'Todas mis solicitudes'},
        {id: 2, text: 'Solicitud por ID'},
        {id: 3, text: 'Solicitudes por fecha'},
        {id: 4, text: 'Solicitudes por estatus'},
        {id: 5, text: 'Solicitudes por tipo'},
        {id: 6, text: 'Solicitudes abiertas'}
    ];
    data.queries = {};
    // initialize all ng-model variables.
    for (var i = 0; i < data.queryList.length; i++) {
        data.queries[data.queryList[0].id] = null;
    }
    data.newRequestList = false;
    data.selectedList = 0;
    data.fetchError = '';
    // contentAvailable will indicate whether sidenav can be visible
    data.contentAvailable = false;
    // contentLoaded will indicate whether sidenav can be locked open
    data.contentLoaded = false;

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
        self.data.req = null; // Will contain the selected request object.
        self.data.fetchError = '';
        self.data.loading = true;
        self.data.selectedReq = '';
        self.data.selectedLoan = -1;
        self.data.requests = {};
        self.data.loanTypes = null;
        self.data.newRequestList = false;
        self.data.selectedList = 0;
        self.data.fetchError = '';
        self.data.contentAvailable = false;
        self.data.contentLoaded = false;
    };

    return self;
}
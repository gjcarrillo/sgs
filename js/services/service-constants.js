/**
 * Created by Kristopher on 11/16/2016.
 */
var constants = angular.module('sgdp.constants', []);

var obj = {
    Users: {
        AGENT: 1,
        MANAGER: 2,
        APPLICANT: 3
    },
    LoanTypes: {
        PERSONAL: 40,
        CASH_VOUCHER: 31
    },
    Statuses: {
        RECEIVED: 'Recibida',
        APPROVED: 'Aprobada',
        REJECTED: 'Rechazada'
    },
    BASEURL: 'http://localhost:8080/sgdp/',
    IPAPEDI_URL: 'http://localhost:8080/ipapedi_en_linea/'
};

constants.constant('Constants', obj);
// lodash support
constants.constant('_', window._);
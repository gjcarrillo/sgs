/**
 * Created by Kristopher on 11/16/2016.
 */
var constants = angular.module('sgdp.constants', []);

var obj = {
    Users: {
        AGENT: 1,
        MANAGER: 2,
        APPLICANT: 3,
        REVISER: 4
    },
    LoanTypes: {
        PERSONAL_LOAN: 40,
        CASH_VOUCHER: 31
    },
    DocTypes: {
        MANDATORY: 1,
        ADDITIONAL: 2
    },
    Statuses: {
        RECEIVED: 'Recibida',
        PRE_APPROVED: 'Pre-Aprobada',
        APPROVED: 'Aprobada',
        REJECTED: 'Rechazada'
    },
    BASEURL: 'http://localhost:8080/sgs/',
    SERVER_URL: 'http://localhost:8080/sgs/server/index.php/',
    IPAPEDI_URL: 'http://localhost:8080/ipapedi_en_linea/'
};

constants.constant('Constants', obj);
// lodash support
constants.constant('_', window._);
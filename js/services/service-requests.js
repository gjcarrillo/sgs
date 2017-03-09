/**
 * Created by Kristopher on 11/16/2016.
 */
var requests = angular
    .module('sgdp.service-requests', [])
    .factory('Requests', reqService);

reqService.$inject = ['$q', '$http', 'Constants', '$filter', 'Utils'];

function reqService($q, $http, Constants, $filter, Utils) {
    'use strict';

    var self = this;

    var loanTitles = {
        pp: "pr\u00E9stamos personales",
        vc: 'vales de caja'
    };

    var maxAmount = 0;
    var minAmount = 0;

    /**
     * Fetches the specified user's requests.
     *
     * @param fetchId - User's ID.
     * @returns {*}
     */
    self.getUserRequests = function (fetchId) {
        var qReq = $q.defer();
        $http.get('RequestsController/getUserRequests',
            {params: {fetchId: fetchId}})
            .then(
            function (response) {
                minAmount = parseInt(response.data.minReqAmount, 10);
                maxAmount = parseInt(response.data.maxReqAmount, 10);
                if (response.data.message === "success") {
                    if (typeof response.data.requests !== "undefined") {
                        qReq.resolve(self.filterRequests(response.data.requests));
                    } else {
                        qReq.resolve({});
                    }
                } else {
                    qReq.reject(response.data.error);
                }
            });
        return qReq.promise;
    };

    /**
     * Updates the given request.
     *
     * @param postData - data to be sent to the server for updating the request.
     * @returns {*} - promise containing the operation's result.
     */
    self.updateRequest = function (postData) {
        var qUpdate = $q.defer();
        $http.post('index.php/EditRequestController/updateRequest',
                   JSON.stringify(postData))
            .then(function (response) {
                      if (response.data.message == "success") {
                          qUpdate.resolve();
                      } else {
                          qUpdate.reject(response.data.message);
                      }
                  });
        return qUpdate.promise;
    };

    /**
     * Edits the given request.
     *
     * @param postData - data to be sent to the server for editing the request.
     * @returns {*} - promise containing the operation's result.
     */
    self.editRequest = function (postData) {
        var qEdit = $q.defer();
        $http.post('index.php/EditRequestController/editRequest',
                   JSON.stringify(postData))
            .then(function (response) {
                             console.log(response);
                      if (response.data.message == "success") {
                          qEdit.resolve();
                      } else {
                          qEdit.reject(response.data.message);
                      }
                  });
        return qEdit.promise;
    };

    /**
     * Deletes the specified document from database.
     *
     * @param doc - doc obj to erase from database.
     * @returns {*} - promise with the operation's result.
     */
    self.deleteDocument = function (doc) {
        var qDelete = $q.defer();
        $http.post('index.php/RequestsController/deleteDocument',
                   JSON.stringify(doc)).then(
            function (response) {
                if (response.data.message == "success") {
                    // Update interface
                    qDelete.resolve();
                } else {
                    qDelete.reject(response.data.message);
                }
            }
        );
        return qDelete.promise;
    };

    /**
     * Deletes the specified request (and it's documents) from database.
     *
     * @param request - the request obj to erase from database.
     * @returns {*} - promise with the operation's result.
     */
    self.deleteRequestUI = function (request) {
        var qDelReq = $q.defer();
        $http.post('index.php/RequestsController/deleteRequestUI',
                   JSON.stringify(request))
            .then(function (response) {
                      if (response.data.message == "success") {
                          qDelReq.resolve();
                      } else {
                          qDelReq.reject(response.data.message ? response.data.message :
                                         'Ha ocurrido un error en el sistema. Por favor intente más tarde');
                      }
                  });
        return qDelReq.promise;
    };

    /**
     * Eliminates the specified request from the system.
     *
     * @param rid - request id as an encoded token.
     * @returns {*} promise containing the operation's result.
     */
    self.deleteRequestJWT = function (rid) {
        var qEliminate = $q.defer();
        $http.post('RequestsController/deleteRequestJWT', {rid: rid})
            .then(
            function (response) {
                console.log(response);
                if (response.data.message == 'success') {
                    qEliminate.resolve();
                } else {
                    qEliminate.reject(response.data.message);
                }
            }

        );

        return qEliminate.promise;
    };

    /**
     * Validates a request through the specified token.
     *
     * @param token - JWT
     * @returns {*} - promise with the operation's result.
     */
    self.validate = function(token) {
        var qVal = $q.defer();

        $http.get('ValidationController/validate', {params: {token: token}})
            .then(
            function (response) {
                console.log(response);
                if (response.data.message === "success") {
                    qVal.resolve();
                } else {
                    qVal.reject(response.data.message);
                }
            }
        );
        return qVal.promise;
    };

    self.updateDocDescription = function (doc) {
        var updateDoc = $q.defer();
        $http.post('EditRequestController/' +
                   'updateDocDescription', JSON.stringify(doc)).then(
            function (response) {
                if (response.data.message == "success") {
                    updateDoc.resolve();
                } else {
                    updateDoc.reject(response.data.message);
                }
            }
        );
        return updateDoc.promise;
    };

    /**
     * Returns the max amount of money the user can ask for in a request.
     *
     * @returns {number} - containing the max. amount the applicant can request.
     */
    self.getMaxAmount = function () {
        return maxAmount;
    };

    /**
     * Returns the min amount of money the user can ask for in a request.
     *
     * @returns {number} - containing the min. amount the applicant can request.
     */
    self.getMinAmount = function () {
        return minAmount;
    };

    /**
     * * Filters all requests by type.
     *
     * @param requests - Requests array returned by the server.
     * @returns {{}} - Obj containing arrays of different types of loans.
     */
    self.filterRequests = function (requests) {
        var req = {};
        var codes = Constants.LoanTypes;
        angular.forEach(codes, function (code) {
            req[self.mapLoanTypeAsCode(code)] = requests.filter(function (loan) {
                return loan.type == code;
            });
        });
        return req;
    };

    /**
     * Gets the different kind of requests' title.
     *
     * @returns {*} - a string corresponding to the loan type's title.
     */
    self.getRequestsListTitle = function () {
        return loanTitles;
    };

    /**
     * Gets all the system-default statuses.
     *
     * @returns {Array} containing all the request statuses.
     */
    self.getAllStatuses = function () {
        var statuses = [];
        angular.forEach(Constants.Statuses, function (status) {
            statuses.push(status);
        });
        return statuses;
    };

    /**
     * Gets the different request types as strings.
     *
     * @returns {Array} containing all the loan types mapped as strings.
     */
    self.getLoanTypesTitles = function () {
        var codes = Constants.LoanTypes;
        var titles = [];
        angular.forEach(codes, function (code) {
            titles.push(self.mapLoanType(code));
        });

        return titles;
    };

    /**
     * Gets all the existing loan types.
     *
     * @returns {Array} containing all the requests loan types.
     */
    self.getAllLoanTypes = function () {
        var loanTypes = [];
        angular.forEach(Constants.LoanTypes, function (type) {
            loanTypes.push(type);
        });
        return loanTypes;
    };

    /**
     * Maps the specified (int) type to it's corresponding string code type.
     *
     * @param type - loan type's code.
     * @returns {*} - string containing the corresponding mapped string code type.
     */
    self.mapLoanTypeAsCode = function (type) {
        switch (type) {
            case Constants.LoanTypes.PERSONAL:
                return 'pp';
                break;
            case Constants.LoanTypes.CASH_VOUCHER:
                return 'vc';
                break;
            default:
                return type;
        }
    };

    /**
     * Maps a loan type code as string, to loan type code as int.
     *
     * @param type - string loan type code.
     * @returns {*} - integer containing the corresponding mapped loan type code.
     */
    self.mapLoanTypeStringCode = function (type) {
        switch (type) {
            case 'pp':
                return Constants.LoanTypes.PERSONAL;
                break;
            case 'vc':
                return Constants.LoanTypes.CASH_VOUCHER;
                break;
            default:
                return type;
        }
    };

    /**
     * Maps the specified (int) type to it's corresponding string type.
     *
     * @param type - loan type's code.
     * @returns {*} - string containing the corresponding mapped string type.
     */
    self.mapLoanType = function (type) {
        switch (type) {
            case Constants.LoanTypes.PERSONAL:
                return 'Préstamo Personal';
                break;
            case Constants.LoanTypes.CASH_VOUCHER:
                return 'Vale de Caja';
                break;
            default:
                return type;
        }
    };

    /**
     * Initializes a list type as false.
     */
    self.initializeListType = function () {
        var list = {};
        var codes = Constants.LoanTypes;
        angular.forEach(codes, function (code) {
            list[self.mapLoanTypeAsCode(code)] = false;
        });
        return list;
    };

    /**
     * Calculates the total loans contained in the specified array obj.
     *
     * @param filteredRequests - Filtered requests container, containing
     * the different loans.
     * @returns {number} - containing the total amount of loans in the array obj.
     */
    self.getTotalLoans = function (filteredRequests) {
        var total = 0;
        angular.forEach(filteredRequests, function (loan) {
            total += loan.length;
        });
        return total;
    };

    /**
     * Finds the specified loan within the requests obj.
     * @param requests - object containing all the requests.
     * @param id - corresponding loan's id.
     */
    self.findRequest = function (requests, id) {
        var index = {};
        var found = false;

        angular.forEach(requests, function (request, rKey) {
            var i = 0;
            while (i < request.length && !found) {
                if (request[i].id === id) {
                    index.request = rKey;
                    index.loan = i;
                    found = true;
                }
                i++;
            }
        });
        return index;
    };

    /**
     * Creates the new request.
     *
     * @param postData - Data to be sent to the server for the request creation.
     * @returns {*} - promise containing the operation's result.
     */
    self.createRequest = function (postData) {
        var qReqCreation = $q.defer();
        $http.post('NewRequestController/createRequest',
                   JSON.stringify(postData))
            .then(function (response) {
                      console.log(response);
                      if (response.data.message == "success") {
                          qReqCreation.resolve();
                      } else {
                          qReqCreation.reject(response.data.message);
                      }
                  });
        return qReqCreation.promise;
    };

    /**
     * Creates POST data for the request doc.
     *
     * @param userId - user applicant's id.
     * @returns {{lpath: string, description: string, docName: string}}
     */
    self.createRequestDocData = function (userId) {
        var docName = 'Constancia';
        return {
            lpath: userId + '.' + Utils.generateUUID() + '.' + docName + '.pdf',
            description: 'Documento declarativo referente a la solicitud',
            docName: docName
        }
    };

    /**
     * Edits the request's email address.
     *
     * @param reqId - selected request's id.
     * @param newAddress - new email address.
     */
    self.editEmail = function (reqId, newAddress) {
        var qEmail = $q.defer();

        var postData = {reqId: reqId, newAddress: newAddress};
        $http.post('EditRequestController/updateEmail', postData)
            .then(
            function (response) {
                console.log(response);
                if (response.data.message == "success") {
                    qEmail.resolve();
                } else {
                    qEmail.reject(response.data.message);
                }
            });
        return qEmail.promise;
    };

    /**
     * Sends a validation email for the specified request.
     *
     * @param reqId - request id.
     */
    self.sendValidation = function(reqId) {
        var qValidation = $q.defer();

        $http.post('ApplicantHomeController/sendValidation', reqId)
            .then(
            function (response) {
                console.log(response);
                if (response.data.message == "success") {
                    qValidation.resolve();
                } else {
                    qValidation.reject(response.data.message);
                }
            });
        return qValidation.promise;
    };

    /**
     * Returns a specific doc's download link.
     *
     * @param docPath - Doc's name on disk.
     * @returns {string} - Formed URL containing link to download doc.
     */
    self.getDocDownloadUrl = function (docPath) {
        return 'RequestsController/download?lpath=' + docPath;
    };

    /**
     * Returns all docs' download link.
     *
     * @param docs - Array containing all docs.
     * @returns {string} - Formed URL containing link to download doc.
     */
    self.getAllDocsDownloadUrl = function (docs) {
        // Bits of pre-processing before passing objects to URL
        var paths = [];
        angular.forEach(docs, function (doc) {
            paths.push(doc.lpath);
        });
        return 'RequestsController/downloadAll?docs=' + JSON.stringify(paths);
    };

    /**
     * Calculates the monthly payment fee the applicant must pay.
     *
     * @param reqAmount - the amount of money the applicant is requesting.
     * @param paymentDue - number in months the applicant chose to pay his debt.
     * @param interest - payment interest (percentage).
     * @returns {number} - monthly payment fee.
     */
    self.calculatePaymentFee = function (reqAmount, paymentDue, interest) {
        var rate = interest / 100;
        // monthly payment.
        var nFreq = 12;
        // calculate the interest as a factor.
        var interestFactor = rate / nFreq;
        // calculate the monthly paymeny fee.
        var paymentFee = reqAmount / ((1 - Math.pow(interestFactor + 1, paymentDue * -1)) / interestFactor);
        return $filter('number')(paymentFee, 2);
    };

    /**
     * Gets a specific user's concurrence.
     *
     * @param userId - user's id.
     * @returns {*} promise with the operation's result.
     */
    self.getUserConcurrence = function (userId) {
        var qUser = $q.defer();

        $http.get('NewRequestController/getUserConcurrence', {params: {userId: userId}})
            .then(
            function (response) {
                console.log(response);
                if (response.data.message == "success") {
                    qUser.resolve(response.data.concurrence);
                } else {
                    qUser.reject(response.data.message);
                }
            });
        return qUser.promise;
    };

    /**
     * Gets a specific user's last requests granting, which indicates whether user can
     * request the same type of request or not.
     *
     * @param userId - user's id.
     * @returns {*} - promise with the operation's result.
     */
    self.getLastRequestsGranting = function (userId) {
        var qGranting = $q.defer();

        $http.get('NewRequestController/getLastRequestsGranting',
            {params: {userId: userId}})
            .then(
            function (response) {
                console.log(response);
                if (response.data.message == "success") {
                    qGranting.resolve(response.data.granting);
                } else {
                    qGranting.reject(response.data.message);
                }
            });
        return qGranting.promise;
    };

    /**
     * Checks specified requests and indicates whether there is any type of request still open.
     *
     * @param requests - all requests from a user.
     * @returns {{hasOpen: {}, allTypesOpen: boolean}}
     */
    self.checkPreviousRequests = function (requests) {
        var hasOpen = {};
        angular.forEach(requests, function (typeList, typeCode) {
            hasOpen[self.mapLoanTypeStringCode(typeCode)] =
                typeList.filter(function (loan) {
                   return loan.status != Constants.Statuses.APPROVED && 
                          loan.status != Constants.Statuses.REJECTED;
                }).length > 0;
        });
        var allTypesOpen = true;
        angular.forEach (hasOpen, function(unvalidated) {
            if (!unvalidated) {
                allTypesOpen = false;
            }
        });
        return {
            hasOpen: hasOpen,
            allTypesOpen: allTypesOpen
        };
    };

    /**
     * Gets a user's availability data (i.e. conditions for creating new requests)
     *
     * @param userId - user's id.
     * @returns {*} - promise with the operation's result.
     */
    self.getAvailabilityData = function (userId) {
        var qAvailability = $q.defer();

        $http.get('NewRequestController/getAvailabilityData',
            {params: {userId: userId}})
            .then(
            function (response) {
                console.log(response);
                if (response.data.message == "success") {
                    qAvailability.resolve(response.data);
                } else {
                    qAvailability.reject(response.data.message);
                }
            });
        return qAvailability.promise;
    };

    self.verifyAvailability = function (data) {
        var available = null;
        if (data.concurrence >= 45) {
            Utils.showAlertDialog('No permitido',
                                  'Estimado usuario, debido a que su nivel de concurrencia sobrepasa ' +
                                  'los niveles permitidos, usted no se encuentra en condiciones de ' +
                                  'solicitar un nuevo préstamo.');
        } else {
            var types = Constants.LoanTypes;
            var anyTypeAvailable = false;
            // A request type is available for creation if the following is tue:
            // 1. there are no opened requests of the same type.
            // 2. span creation constrain between requests of same time is over.
            for (var type in types) {
                if (types.hasOwnProperty(type)) {
                    if (!data.opened.hasOpen[types[type]] && data.granting.allow[types[type]]) {
                        anyTypeAvailable = true;
                        available = parseInt(types[type], 10);
                        break;
                    }
                }
            }
            if (!anyTypeAvailable) {
                // throw error msg
                Utils.showAlertDialog('No permitido',
                                      'Etimado usuario, no puede solicitar ningún tipo de solicitud adicional ' +
                                      'debido a cualquiera de las siguientes razones:<br/><br/>' +
                                      '1. Posee distintos tipos de solicitudes en transcurso.<br/>' +
                                      '2. Aún no ha' + (data.granting.span == 1 ? '' : 'n') +
                                      ' transcurrido ' + data.granting.span + (data.granting.span == 1 ? ' mes' : ' meses') +
                                      ' desde el último préstamo otorgado.');
            }
        }
        return available;
    };


    return self;
}

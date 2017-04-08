/**
 * Created by Kristopher on 11/16/2016.
 */
var requests = angular
    .module('sgdp.service-requests', [])
    .factory('Requests', reqService);

reqService.$inject = ['$q', '$http', 'Constants', '$filter', 'Utils', 'Config'];

function reqService($q, $http, Constants, $filter, Utils, Config) {
    'use strict';

    var self = this;

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
                if (response.data.message === "success") {
                    if (typeof response.data.requests !== "undefined") {
                        qReq.resolve(self.filterRequests(response.data.requests));
                    }
                } else {
                    qReq.reject(response.data.message);
                }
            });
        return qReq.promise;
    };

    /**
     * Fetches details of specified request.
     * @param id - request id.
     * @param uid - user owner's id.
     * @returns {*}
     */
    self.getRequestById = function (id, uid) {
        var qReq = $q.defer();
        $http.get('requestsController/getRequestById', {params:{rid:id, uid: uid}}).then(
            function (response) {
                console.log(response);
                if (response.data.message == "success") {
                    qReq.resolve(response.data.request);
                } else {
                    qReq.reject(response.data.message);
                }

            });
        return qReq.promise;
    };

    /**
     * Gets the requests created within the specified date interval.
     *
     * @param from - date from which to start the look up.
     * @param to - date from which to end the look up.
     * @param uid - user owner's id.
     * @returns {*} - promise containing the operation's result.
     */
    self.getRequestsByDate = function (from, to, uid) {
        var qRequests = $q.defer();
        $http.get('requestsController/getRequestsByDate',
            {
                params: {
                    from: moment(from).format('DD/MM/YYYY'),
                    to: moment(to).format('DD/MM/YYYY'),
                    uid: uid
                }
            })
            .then(
            function (response) {
                console.log(response);
                if (response.data.message === "success") {
                    qRequests.resolve(self.filterRequests(response.data.requests));
                } else {
                    qRequests.reject(response.data.message);
                }
            });
        return qRequests.promise;
    };

    /**
     * Fetches requests the match the specified status.
     *
     * @param status - request status.
     * @param uid - user owner's id.
     * @returns {*} - promise containing the operation's result.
     */
    self.getRequestsByStatus = function (status, uid) {
        var qRequests = $q.defer();
        $http.get('requestsController/getRequestsByStatus', {params: {status: status, uid: uid}})
            .then(
            function (response) {
                if (response.data.message === "success") {
                    qRequests.resolve(self.filterRequests(response.data.requests));
                } else {
                    qRequests.reject(response.data.message);
                }
            });
        return qRequests.promise;
    };

    /**
     * Fetches requests the match the specified loan type.
     *
     * @param concept - loan type code.
     * @param uid - user owner's id.
     * @returns {*} - promise containing the operation's result.
     */
    self.getRequestsByType = function (concept, uid) {
        var qRequests = $q.defer();
        $http.get('requestsController/getRequestsByType', {params: {concept: concept, uid: uid}})
            .then(
            function (response) {
                if (response.data.message === "success") {
                    qRequests.resolve(response.data.requests);
                } else {
                    qRequests.reject(response.data.message);
                }
            });
        return qRequests.promise;
    };

    /**
     * Fetches user requests that have not been yet closed.
     *
     * @param uid - user's id.
     * @returns {*} - promise with the operation's result.
     */
    self.getOpenedRequests = function (uid) {
        var qRequests = $q.defer();
        $http.get('requestsController/getOpenedRequests', {params: {uid: uid}})
            .then(
            function (response) {
                console.log(response);
                if (response.data.message === "success") {
                    qRequests.resolve(self.filterRequests(response.data.requests));
                } else {
                    qRequests.reject(response.data.message);
                }
            });
        return qRequests.promise;
    };

    /**
     * Fetches user's requests have can be edited (i.e. have not yet been validated).
     *
     * @param fetchId - user's id.
     * @returns {*} - promise with the operation's result.
     */
    self.getUserEditableRequests = function (fetchId) {
        var qReq = $q.defer();
        $http.get('requestsController/getUserEditableRequests',
            {params: {fetchId: fetchId}}).then(
            function (response) {
                console.log(response);
                if (response.data.message === "success") {
                    qReq.resolve(response.data.requests);
                } else {
                    qReq.reject(response.data.message);
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
                          qUpdate.resolve(response.data.request);
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
                      if (response.data.message == "success") {
                          qEdit.resolve(response.data.request);
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
                    qDelete.resolve(response.data.request);
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
     * Validates the specified request.
     *
     * @param rid - request's id.
     * @returns {*} - promise with the operation's result.
     */
    self.validateRequest = function (rid) {
        var qVal = $q.defer();
        $http.post('ValidationController/validateReq', {rid: rid})
            .then(
            function (response) {
                if (response.data.message === "success") {
                    qVal.resolve(response.data.date);
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
                    updateDoc.resolve(response.data.request);
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
        angular.forEach(Config.loanConcepts, function (type, concept) {
            req[concept] = requests.filter(function (loan) {
                return loan.type == concept;
            });
        });
        return req;
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
     * Initializes a list type as false.
     */
    self.initializeListType = function () {
        var qReq = $q.defer();
        var list = {};
        if (!Config.loanConcepts) {
            Config.getLoanTypes().then(
              function (types) {
                  Config.loanConcepts = types;
                  angular.forEach(types, function (type, concept) {
                      list[concept] = type;
                      list[concept].selected = false;
                  });
                  qReq.resolve(list);
              },
              function (error) {
                  qReq.reject(error);
              }
            );
        } else {
            angular.forEach(Config.loanConcepts, function (type, concept) {
                list[concept] = type;
                list[concept].selected = false;
            });
            qReq.resolve(list);
        }

        return qReq.promise;
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
                      if (response.data.message == "success") {
                          qReqCreation.resolve(response.data.request);
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
     * Returns a specific doc's download link.
     *
     * @param docId - Doc's id.
     * @returns {string} - Formed URL containing link to download doc.
     */
    self.getDocDownloadUrl = function (docId) {
        return 'RequestsController/download?doc=' + docId;
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
            paths.push(doc.id);
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
                if (response.data.message == "success") {
                    qUser.resolve(response.data.concurrence);
                } else {
                    qUser.reject(response.data.message);
                }
            });
        return qUser.promise;
    };

    /**
     * Indicates whether there is an open request for a specific type of requests belongning to a user.
     *
     * @param fetchId - user ID.
     * @param concept - requests' concept.
     * @returns {{}}
     */
    self.checkPreviousRequests = function (fetchId, concept) {
        var qReq = $q.defer();
        $http.get('requestsController/getUserOpenedRequest',
            {params: {fetchId: fetchId, concept: concept}}).then(
            function (response) {
                console.log(response);
                if (response.data.message === "success") {
                    qReq.resolve(response.data);
                } else {
                    qReq.reject(response.data.message);
                }
            });
        return qReq.promise;
    };

    /**
     * Gets a user's availability data (i.e. conditions for creating new requests)
     *
     * @param userId - user's id.
     * @param concept - request's concept.
     * @returns {*} - promise with the operation's result.
     */
    self.getAvailabilityData = function (userId, concept) {
        var qAvailability = $q.defer();

        $http.get('NewRequestController/getAvailabilityData',
            {params: {userId: userId, concept: concept}})
            .then(
            function (response) {
                if (response.data.message == "success") {
                    minAmount = parseInt(response.data.minReqAmount, 10);
                    maxAmount = parseInt(response.data.maxReqAmount, 10);
                    qAvailability.resolve(response.data);
                } else {
                    qAvailability.reject(response.data.message);
                }
            });
        return qAvailability.promise;
    };

    // A request type is available for creation if the following is tue:
    // 1. for personal loans, user has at least 6 months old in our system.
    // 2. User's concurrence level is below 40%.
    // 3. there are no opened requests of the same type.
    // 4. span creation constrain between requests of same time is over.
    self.verifyAvailability = function (data, concept, editMode) {
        if (data.admissionDate && !data.sixMonthsOld) {
            // If admissionDate exists in response, it means we should perform the check
            Utils.showAlertDialog('No permitido',
                                  'Estaimdo usuario, para solicitar un préstamo personal es necesario que ' +
                                  'hayan transcurrido al menos seis (6) meses desde su fecha de ingreso, por lo que ' +
                                  'podrá solicitar dicho préstamo al ' + data.dateAvailable);
        } else if (data.concurrence >= 40) {
            Utils.showAlertDialog('No permitido',
                                  'Estimado usuario, debido a que su nivel de concurrencia sobrepasa ' +
                                  'el 40%, usted no se encuentra en condiciones de ' +
                                  'solicitar un nuevo préstamo.');
        } else if (data.opened.hasOpened && !editMode) {
            Utils.showAlertDialog('No permitido', 'Estimado usuario, no puede realizar otra solicitud del tipo ' +
                                                  Config.loanConcepts[concept].description + ' debido a que ya posee ' +
                                                  'una solicitud (con ID #' + Utils.pad(data.opened.id, 6) + ') de ' +
                                                  'dicho tipo abierta.');
        } else if (!data.granting.allow) {
            Utils.showAlertDialog('No permitido', 'Estimado usuario, no puede realizar otra solicitud del tipo ' +
                                                  Config.loanConcepts[concept].description + ' debido a que aún no ' +
                                                  'ha' + (data.granting.span == 1 ? '' : 'n') +
                                                  ' transcurrido ' + data.granting.span + (data.granting.span == 1 ? ' mes' : ' meses') +
                                                  ' desde el último préstamo otorgado.<br/><br/>' +
                                                  'Podrá volver a solicitar un préstamo de dicho tipo el ' + data.granting.dateAvailable);
        }
    };

    /**
     * Obtains loan types' available terms for payment.
     *
     * @param concept - request's concept.
     * @returns {*} - promise with the result's operation.
     */
    self.getLoanTerms = function (concept) {
        var qReq = $q.defer();

        $http.get('ConfigController/getRequestTerms',
            {params: {concept: concept}}).then(
            function (response) {
                if (response.data.message == "success") {
                    qReq.resolve(response.data.terms);
                } else {
                    qReq.reject(response.data.message);
                }
            });

        return qReq.promise;
    };

    self.getInterestRate = function (loanType) {
        return Config.loanConcepts[loanType].InteresAnual;
    };

    return self;
}

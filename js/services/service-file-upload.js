/**
 * Created by Kristopher on 11/16/2016.
 */
angular
    .module('sgdp.service-file-upload', [])
    .factory('FileUpload', fileUpload);

fileUpload.$inject = ['$q', 'Upload'];

function fileUpload($q, Upload) {
    'use strict';

    var self = this;

    /**
     *
     * Uploads selected document to the server.
     *
     * @param file - file to upload.
     * @param userId - document's user owner ID. Will be used to compose doc's name in server's storage.
     * @param requestNumb - request's number. Will be used to compose doc's name in server's storage.
     * @returns {*} - promise with the operation's results.
     */
    self.uploadFile = function(file, userId, requestNumb) {
        var qUpload = $q.defer();

        file.upload = Upload.upload({
            url: 'index.php/NewRequestController/upload',
            data: {
                file: file,
                userId: userId,
                requestNumb: requestNumb
            }
        });

        file.upload.then(function (response) {
            // Return doc info
            var doc = {
                lpath: response.data.lpath,
                docName: file.docName,
                description: file.description
            };
            return qUpload.resolve(doc);
        }, function (response) {
            // Show upload error
            if (response.status > 0)
                qUpload.reject(response.status + ': ' + response.data);
        }, function (evt) {
            // Upload file upload progress
            file.progress = Math.min(100, parseInt(100.0 *
                                                   evt.loaded / evt.total));
        });

        return qUpload.promise;
    };

    /**
     * Returns an error message depending upon error and upload parameters.
     *
     * @param error - error thrown.
     * @param param - upload parameter obj.
     * @returns {string} containing error message.
     */
    self.showIdUploadError = function (error, param) {
        if (error === "pattern") {
            return "Archivo no aceptado. Por favor seleccione " +
                   "imágenes o archivos PDF.";
        } else if (error === "maxSize") {
            return "El archivo es muy grande. Tamaño máximo es: " +
                   param;
        }
    };

    /**
     * Returns an error message depending upon error and upload parameters.
     *
     * @param error - error thrown.
     * @param param - upload parameter obj.
     * @returns {string} containing error message.
     */
    self.showDocUploadError = function (error, param) {
        if (error === "pattern") {
            return "Archivo no aceptado. Por favor seleccione " +
                   "sólo documentos.";
        } else if (error === "maxSize") {
            return "El archivo es muy grande. Tamaño máximo es: " +
                   param;
        }
    };

    return self;
}

/**
 * Created by Kristopher on 11/16/2016.
 */
angular
    .module('sgdp.service-file-upload', [])
    .factory('FileUpload', fileUpload);

fileUpload.$inject = ['$q', 'Upload', '$http'];

function fileUpload($q, Upload, $http) {
    'use strict';

    var self = this;

    /**
     *
     * Uploads selected document to the server.
     *
     * @param file - file to upload.
     * @param userId - document's user owner ID.
     * @param requestNumb - Number of the request (i.e, [type].[number]).
     * @returns {*} - promise with the operation's results.
     */
    self.uploadFile = function (file, userId, requestNumb) {
        var qUpload = $q.defer();

        file.upload = Upload.upload({
            url: 'NewRequestController/upload',
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
                qUpload.reject(response.data.message);
        }, function (evt) {
            // Upload file upload progress
            file.progress = Math.min(100, parseInt(100.0 *
                                                   evt.loaded / evt.total));
        });

        return qUpload.promise;
    };

    /**
     * Uploads all the specified files.
     *
     * @param files - files to upload.
     * @param userId - request owner' ID.
     * @param requestNumb - Number of the request (i.e, [type].[number]).
     * @returns {*} - promise with the operation's results.
     */
    self.uploadFiles = function(files, userId, requestNumb) {
        var qUploadFiles = $q.defer();
        // Notifies whether all files were successfully uploaded.
        var uploadedFiles = new Array(files.length).fill(false);
        // Will contain docs to create in DB
        var docs = [];

        angular.forEach(files, function (file, index) {
            file.upload = Upload.upload({
                url: 'NewRequestController/upload',
                data: {
                    file: file,
                    userId: userId,
                    requestNumb: requestNumb
                }
            });
            file.upload.then(function (response) {
                // Register upload success
                uploadedFiles[index] = true;
                // Add document info
                docs.push({
                    lpath: response.data.lpath,
                    description: file.description,
                    docName: file.name
                });
                if (uploadsFinished(uploadedFiles)) {
                    // All files uploaded! Send docs data as result.
                    qUploadFiles.resolve(docs);
                }
            }, function (response) {
                if (response.status > 0) {
                    // Show file error message
                    qUploadFiles.reject(response.data.message);
                }
            }, function (evt) {
                // Fetch file updating progress
                file.progress = Math.min(100, parseInt(100.0 *
                                                       evt.loaded / evt.total));
            });
        });
        return qUploadFiles.promise;
    };

    /**
     * Helper function that determines whether all uploads have finished
     * @param uploadedFiles - flag array indicating each file's upload status.
     *
     * @returns {boolean} true if all files were uploaded. False otherwise.
     */
    function uploadsFinished(uploadedFiles) {
        return (uploadedFiles.filter(function (bool) {
            return !bool;
        }).length == 0);
    }

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

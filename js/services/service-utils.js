/**
 * Created by Kristopher on 11/16/2016.
 */
angular
    .module('sgdp.service-utils', [])
    .factory('Utils', utils);

utils.$inject = ['$mdDialog'];

function utils($mdDialog) {
    'use strict';

    var self = this;

    /**
     * Shows a material design confirmation dialog.
     *
     * @param title - dialog's title text.
     * @param body - dialog's body text.
     * @param pos - dialog-s positive button text.
     * @param neg - dialog's positive button text.
     * @param ev - DOM event that triggered the dialog.
     * @param smaller - whether dialog should have small (50%) width or not.
     * @returns {*} - Dialog's promise (confirmation and rejection).
     */
    self.showConfirmDialog = function (title, body, pos, neg, ev, smaller) {
        var confirm = $mdDialog.confirm()
            .title(title)
            .textContent(body)
            .css(smaller ? 'smaller-dialog-content' : '')
            .ariaLabel(title)
            .targetEvent(ev)
            .ok(pos)
            .cancel(neg);
        return $mdDialog.show(confirm);
    };

    /**
     * Helper function that shows an alert dialog message to user.
     *
     * @param dialogTitle - dialog's title text.
     * @param dialogContent - dialog's body text.
     */
    self.showAlertDialog = function(dialogTitle, dialogContent) {
        $mdDialog.show(
            $mdDialog.alert()
                .parent(angular.element(document.body))
                .clickOutsideToClose(true)
                .title(dialogTitle)
                .textContent(dialogContent)
                .ariaLabel(dialogTitle)
                .ok('Ok')
        );
    };

    /**
     * Helper function for formatting numbers with leading zeros.
     *
     * @param n - number to format.
     * @param width - desired number's width.
     * @param z - (optional) character to used in padding.
     *
     * @returns {*} String containing the new formatted number.
     */
    self.pad = function (n, width, z) {
        z = z || '0';
        n = n + '';
        return n.length >= width ? n :
        new Array(width - n.length + 1).join(z) + n;
    };

    return self;
}

/**
 * Created by Kristopher on 11/16/2016.
 */
angular
    .module('sgdp.service-helps', [])
    .factory('Helps', helps);

helps.$inject = [];

function helps() {
    'use strict';

    var self = this;

    var dialogOptions = {
        showNavigation: true,
        showCloseBox: true,
        delay: -1,
        tripTheme: "dark",
        prevLabel: "Anterior",
        nextLabel: "Siguiente",
        finishLabel: "Entendido"
    };

    /**
     * Returns the pre-configure help dialogs options.
     * @returns {{showNavigation: boolean, showCloseBox: boolean, delay: number, tripTheme: string, prevLabel: string,
     *     nextLabel: string, finishLabel: string}}
     */
    self.getDialogsHelpOpt = function() {
        return dialogOptions;
    };

    /**
     * Adds a help dialog for the specified field.
     *
     * @param trip - Initialized trip.js object.
     * @param id - DOM id the dialog will be focused in.
     * @param content - help dialog's body text.
     * @param pos - dialog's position (n, s, e, w)
     * @param expose - whether to expose the view.
     */
    self.addFieldHelp = function(trip, id, content, pos, expose) {
        trip.tripData.push(
            {
                sel: $(id), content: content, position: pos,
                animation: 'fadeInUp', expose: expose ? true : false
            }
        );
    };


    /**
     * Adds a help dialog (with a header) for the specified field.
     *
     * @param trip - Initialized trip.js object.
     * @param id - DOM id the dialog will be focused in.
     * @param content - help dialog's body text.
     * @param pos - dialog's position (n, s, e, w)
     * @param header - help dialog's header text.
     * @param expose - whether to expose the view.
     */
    self.addFieldHelpWithHeader = function(trip, id, content, pos, header, expose) {
        trip.tripData.push(
            {
                sel: $(id), content: content, position: pos,
                animation: 'fadeInUp', header: header, expose: expose ? true : false
            }
        );
    };

    return self;
}
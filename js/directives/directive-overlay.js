var app = angular.module('sgdp.directive-overlay', []);

/**
 * Directive created to put an overlay that will avoid any kind of interactions
 * while waiting for some operation to finish.
 */
app.directive('overlay', function() {
    return {
        restrict: 'E',
        template:'<div class="overlay"></div>'
    };
});

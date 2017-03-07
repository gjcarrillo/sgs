var app = angular.module('sgdp.directive-animate-change', []);

/**
 * Directive created to animate request details cards.
 */
app.directive('animateOnChange', function($animate, $timeout) {
    return function(scope, elem, attr) {
        scope.$watch(attr.animateOnChange, function(nv, ov) {
            if (ov != null) {
                $animate.addClass(elem, 'animated fadeIn').then(function() {
                    $timeout(function() {$animate.removeClass(elem, 'animated fadeIn');});
                });
            }
        });
    };
});

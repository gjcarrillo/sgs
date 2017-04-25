var app = angular.module('sgdp.directive-set-limit', []);

app.directive('setLimit', function() {
    return {
        restrict: 'A',
        require: 'ngModel',
        scope: {
            limit: '=setLimit'
        },
        link: function (scope, element, attrs, ngModel) {
            // Watch the ng model value changes.
            scope.$watch(function () {
                return ngModel.$modelValue;
            }, function(newValue) {
                // if new value is greater than limit, replace value for limit.
                if (scope.limit < newValue) {
                    element.val(scope.limit);
                    ngModel.$modelValue = scope.limit;
                }
            });
        }
    };
});

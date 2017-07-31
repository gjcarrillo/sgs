var app = angular.module('sgdp.directive-select-fix', []);

/**
 * Directive created for fixing usability (selection menu translation) error on md-select
 * This will force it to stay in correct position once selection menu has open.
 */
app.directive('mdSelectFix', function($timeout) {
    return {
        restrict: 'A',
        scope: {
            value: '=mdSelectFix'
        },
        link: function ($scope, elem) {
            var backup;

            $(elem).data('open',false);
            $(elem).click( function() {
                if ( $(elem).data('open') == false) {
                    $(elem).data('open', true);
                    onOpen();
                    $(document).mouseup(waitForCloseClick);
                } else {
                    $(elem).data('open', false );
                }
            });

            function waitForCloseClick() {
                $(document).unbind('mouseup');
                onClose();
                $timeout( function(){
                    $(elem).data('open', false);
                });
                return false;
            }

            function onOpen() {
                backup = $scope.value;
                $scope.value = null;
            }

            function onClose() {
                if ($scope.value === null) {
                    $scope.value = backup;
                }
            }
        }
    };
});

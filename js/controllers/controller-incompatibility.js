/**
 * Created by Kristopher on 2/18/2017.
 */
angular
    .module('sgdp')
    .controller('IncompatibilityController', incompatible);

incompatible.$inject = ['$scope', 'Utils', '$state'];

function incompatible($scope, Utils, $state) {
    var md = new MobileDetect(window.navigator.userAgent);
    var error = '';
    var browser = bowser.name;
    var version = bowser.version;
    var supported = Utils.getSupportedBrowsers();

    if ((md.is('iPhone') && md.version('iPhone') < supported['iPhone'])){
        error += 'Se ha detectado que la versión de su software es iOS ' + md.version('iPhone') + ', ' +
                 'y se require mínimo iOS .' + supported['iPhone'] +'<br/><br/>';
    } else if (md.is('AndroidOS') && md.version('Android') < supported['Android']){
        error += 'Se ha detectado que la versión de su software es Android ' + md.version('Android') + ', ' +
                 'y se require mínimo Android ' + supported['Android'] +'<br/><br/>';
    } else if (version < supported[browser]) {
        error += 'Se ha detectado que está utilizando el navegador ' + browser + ' ' + version + ', ' +
                 'y se requiere mínimo ' + browser + ' ' + supported[browser] + '<br/><br/>';
    } else {
        //$state.go('login');
    }
    error += 'Lista de navegadores y versiones compatibles: <br/>';
    for (var key in supported) {
        if (supported.hasOwnProperty(key)) {
            error += key + ' ' + supported[key] + '<br/>';
        }
    }
    $scope.message = error;
}
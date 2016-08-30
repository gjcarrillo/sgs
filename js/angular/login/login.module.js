var login = angular.module('sgdp.login', ['ngCookies', 'ngMaterial']);

login.config(function($mdIconProvider) {
    $mdIconProvider.icon('account-box', 'images/icons/ic_account_box_black_48px.svg', 24);
    $mdIconProvider.icon('assignment', 'images/icons/ic_assignment_black_48px.svg', 24);
    $mdIconProvider.icon('assessment', 'images/icons/ic_assessment_black_48px.svg', 24);
});

// Cache svg's
login.run(function($http, $templateCache) {
    var urls = [
        'images/icons/ic_account_box_black_48px.svg',
        'images/icons/ic_assignment_black_48px.svg',
        'images/icons/ic_assessment_black_48px.svg'
    ];
    angular.forEach(urls, function(url) {
        $http.get(url, {cache: $templateCache});
    });
});

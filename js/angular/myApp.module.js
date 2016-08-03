var sgdp = angular.module("sgdp", ["sgdp.login", "ui.router", "ngMaterial", "ngFileUpload", "webcam"]);


sgdp.config(function($stateProvider, $urlRouterProvider, $mdThemingProvider, $locationProvider) {
  $urlRouterProvider.otherwise('login');
  $stateProvider
    .state('login', {
        url: '/login',
        templateUrl: 'index.php/login/LoginController',
        controller: 'LoginController'
    })
    .state('userHome', {
        url: '/userHome',
        templateUrl: 'index.php/home/HomeController/user',
        controller: 'UserHomeController'
    })
    .state('agentHome', {
        url: '/agentHome',
        templateUrl: 'index.php/home/HomeController/agent',
        controller: 'AgentHomeController'
    })
    .state('docGenerator', {
        url: '/generator',
        templateUrl: 'index.php/documents/DocumentGenerator',
        controller: 'DocumentGenerator'
    })
    .state('history', {
        url: '/history',
        templateUrl: 'index.php/history/HistoryController',
        controller: 'HistoryController'
    })
    .state('userInfo', {
        url: '/userInfo',
        templateUrl: 'index.php/userInfo/UserInfoController',
        controller: 'UserInfoController'
    });
    // $locationProvider.html5Mode(true);
    // Application theme
    $mdThemingProvider.definePalette('golden', {
        '50': '8F731F',
        '100': '8F731F',
        '200': '8F731F',
        '300': '8F731F',
        '400': '8F731F',
        '500': '8F731F',
        '600': '8F731F',
        '700': '8F731F',
        '800': '8F731F',
        '900': '8F731F',
        'A100': '8F731F',
        'A200': '8F731F',
        'A400': '8F731F',
        'A700': '8F731F',
        'contrastDefaultColor': 'light',    // whether, by default, text (contrast)
                                      // on this palette should be dark or light
        'contrastDarkColors': ['50', '100', //hues which contrast should be 'dark' by default
        '200', '300', '400', 'A100'],
        'contrastLightColors': undefined    // could also specify this if default was 'dark'
    });
    $mdThemingProvider.definePalette('white', {
        '50': 'fff',
        '100': 'fff',
        '200': 'fff',
        '300': 'fff',
        '400': 'fff',
        '500': 'fff',
        '600': 'fff',
        '700': 'fff',
        '800': 'fff',
        '900': 'fff',
        'A100': 'fff',
        'A200': 'fff',
        'A400': 'fff',
        'A700': 'fff',
        'contrastDefaultColor': 'dark',    // whether, by default, text (contrast)
                                      // on this palette should be dark or light
        'contrastDarkColors': ['50', '100', //hues which contrast should be 'dark' by default
        '200', '300', '400', 'A100'],
        'contrastLightColors': undefined    // could also specify this if default was 'dark'
    });
    var darkBlue = $mdThemingProvider.extendPalette('blue', {
        '500' : '0D47A1'
    });
    $mdThemingProvider.definePalette('darkBlue', darkBlue);

    $mdThemingProvider.theme('default')
        .primaryPalette('darkBlue')
        .accentPalette('golden');
    $mdThemingProvider.theme('whiteInput')
        .primaryPalette('white')
        .accentPalette('blue');
});


sgdp.run(['$rootScope', '$location','$state','auth', '$cookies', '$http',
    function ($rootScope, $location, $state, auth, $cookies, $http) {

        $rootScope.logout = function() {
            $http.get('index.php/login/LoginController/logout');
            auth.logout();
        };
        $rootScope.$on("$locationChangeStart", function(e, toState, toParams, fromState, fromParams) {

        if (!auth.isLoggedIn() && $location.url() != "/login") {
            // if user is not logged in and is trying to access
            // private content, send to login.
            e.preventDefault();
            $state.go('login');
        }
        else if(auth.isLoggedIn() && $location.url() == "/login") {
            // if user Is logged in and is trying to access login page
            // send to home page
            e.preventDefault();
            auth.sendHome();
        } else if (auth.isLoggedIn() && !userHasPermission(auth.permission(), $location.url())) {
            // check if user actually has access permission to intended url

            e.preventDefault();
            // if user does not have the propper permission, send home
            // or maybe send to error page.
            auth.sendHome();
        }
  });

  function userHasPermission(userType, url) {
      switch (url) {
        case '/userHome':
            // Anyone can access user home page
            return true;
        case '/agentHome':
            // Check for agent rights
            return userType == 1;
        case '/history':
            // check for agent rights
            return userType == 1;
        case '/userInfo':
            // check for agent rights
            return userType == 1;
      }
      //  Going to login (.otherwise('login')), so keep going!
      return true;
  }
}]);

// Slide up/down animation for ng-hide
sgdp.animation('.slide-toggle', ['$animateCss', function($animateCss) {
    return {
        addClass: function(element, className, doneFn) {
            if (className == 'ng-hide') {
                var animator = $animateCss(element, {
                    to: {height: '0px'}
                });
                if (animator) {
                    return animator.start().finally(function() {
                        element[0].style.height = '';
                        doneFn();
                    });
                }
            }
            doneFn();
        },
        removeClass: function(element, className, doneFn) {
            if (className == 'ng-hide') {
                var height = element[0].offsetHeight;
                var animator = $animateCss(element, {
                    from: {height: '0px'},
                    to: {height: height + 'px'}
                });
                if (animator) {
                 return animator.start().finally(doneFn);
                }
            }
            doneFn();
        }
    };
}]);

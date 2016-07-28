var sgdp = angular.module("sgdp", ["sgdp.login", "ui.router", "ngMaterial", "ngFileUpload"]);


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
    .state('adminHome', {
        url: '/adminHome',
        templateUrl: 'index.php/home/HomeController/admin',
        controller: 'AdminHomeController'
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
    });
    // $locationProvider.html5Mode(true);
    // Application theme
    $mdThemingProvider.theme('default')
        .primaryPalette('teal')
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
        case '/adminHome':
            // Check for admin rights
            return userType == 1;
        case '/history':
            // check for admin rights
            return userType == 1;
      }
      // maybe going to login (.otherwise('login'))? if so, keep going!
      return true;
  }
}])

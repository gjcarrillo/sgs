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
        url: 'userHome',
        templateUrl: 'index.php/home/HomeController/user',
        controller: 'UserHomeController'
    })
    .state('adminHome', {
        url: 'adminHome',
        templateUrl: 'index.php/home/HomeController/admin',
        controller: 'AdminHomeController'
    })
    .state('history', {
        url: '/history',
        templateUrl: 'index.php/history/HistoryController',
        controller: 'HistoryController'
    })
    .state('forbidden', {
        url: '/forbidden',
        templateUrl: 'index.php/ForbiddenAccessController'
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
            // send to home page (tickets)
            e.preventDefault();
            if (auth.permission() == 1) {
                $state.go('adminHome');
            } else {
                $state.go('userHome');
            }
        }
        // else if (auth.isLoggedIn() && !userHasPermission(auth.profile(), $location.url())) {
        //     // check if user actually has access permission to intended url
        //
        //     e.preventDefault();
        //     // if user does not have the propper permission, send home
        //     // or maybe send to error page.
        //     $state.go('forbidden');
        // }
  });

  function userHasPermission(userType, url) {
      switch (url) {
        case '/':
            // Anyone can access home page
            return true;
        case '/history':
            // check for manager rights
            return userType == 1;
      }
      // maybe going to login (.otherwise('login'))? if so, keep going!
      return true;
  }
}])

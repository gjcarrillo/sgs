'use strict';

/**
 * @ngdoc overview
 * @name SGS
 * @description
 *
 * Main module of the application.
 */
var sgdp = angular.module("sgdp",
    [
      "sgdp.login",
      "sgdp.constants",
      "sgdp.service-requests",
      "sgdp.service-utils",
      "sgdp.service-file-upload",
      "sgdp.service-helps",
      "sgdp.service-manager",
      "sgdp.service-config",
      "sgdp.service-agent",
      "sgdp.service-applicant",
      "sgdp.service-reviser",
      "sgdp.directive-animate-change",
      "sgdp.directive-select-fix",
      "sgdp.directive-helps",
      "sgdp.directive-overlay",
      "sgdp.directive-psw-match",
      "sgdp.directive-set-limit",
      "sgdp.filter-abs",
      "ui.router",
      "ngMaterial",
      "md.data.table",
      "material.components.expansionPanels",
      "ngFileUpload",
      "ngMessages"
    ]);


sgdp.config(function ($stateProvider, $urlRouterProvider, $mdThemingProvider,
                      $mdDateLocaleProvider, $locationProvider, Constants) {
  $urlRouterProvider.otherwise('login');
  $stateProvider
      .state('login', {
        url: '/login',
        views: {
          'content': {
            templateUrl: 'views/login.html',
            controller: 'LoginController'
          },
          'footer': {
            templateUrl: Constants.SERVER_URL + 'mainController/footer'
          }
        }
      })
      .state('applicantHome', {
        url: '/applicantHome',
        views: {
          'content': {
            templateUrl: 'views/applicantHome.html',
            controller: 'ApplicantHomeController'
          },
          'footer': {
            templateUrl: Constants.SERVER_URL + 'mainController/footer'
          }
        }
      })
      .state('agentHome', {
        url: '/agentHome',
        views: {
          'content': {
            templateUrl: 'views/agentHome.html',
            controller: 'AgentHomeController'
          },
          'footer': {
            templateUrl: Constants.SERVER_URL + 'mainController/footer'
          }
        }
      })
      .state('managerHome', {
        url: '/managerHome',
        views: {
          'content': {
            templateUrl: 'views/managerHome.html',
            controller: 'ManagerHomeController'
          },
          'footer': {
            templateUrl: Constants.SERVER_URL + 'mainController/footer'
          }
        }
      })
      .state('reviserHome', {
        url: '/reviserHome',
        views: {
          'content': {
            templateUrl: 'views/reviserHome.html',
            controller: 'ReviserHomeController'
          },
          'footer': {
            templateUrl: Constants.SERVER_URL + 'mainController/footer'
          }
        }
      })
      .state('actions', {
        url: '/actions',
        views: {
          'content': {
            templateUrl: 'views/history.html',
            controller: 'HistoryController'
          }
        }
      })
      .state('perspective', {
        url: '/perspective',
        views: {
          'content': {
            templateUrl: 'views/perspective.html',
            controller: 'PerspectiveController'
          },
          'footer': {
            templateUrl: Constants.SERVER_URL + 'mainController/footer'
          }
        }
      })
      .state('transition', {
        url: '/transition/:token',
        views: {
          'content': {
            templateUrl: 'views/transition.html',
            controller: 'LoginController'
          }
        }
      })
      .state('expired', {
        url: '/expired',
        views: {
          'content': {
            templateUrl: 'views/sessionExpired.html',
            controller: 'SessionExpiredController'
          },
          'footer': {
            templateUrl: Constants.SERVER_URL + 'mainController/footer'
          }
        }
      })
      .state('details', {
        url: '/details',
        views: {
          'content': {
            templateUrl: 'views/details.html',
            controller: 'DetailsController'
          },
          'footer': {
            templateUrl: Constants.SERVER_URL + 'mainController/footer'
          }
        }
      })
      .state('userInfo', {
        url: '/userInfo',
        views: {
          'content': {
            templateUrl: 'views/userInfo.html',
            controller: 'UserInfoController'
          }
        }
      })
      .state('incompatibility', {
        url: '/incompatible',
        views: {
          'content': {
            templateUrl: 'views/incompatible.html',
            controller: 'IncompatibilityController'
          }
        }
      });
   $locationProvider.html5Mode(true);
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
    '500': '0D47A1'
  });
  $mdThemingProvider.definePalette('darkBlue', darkBlue);
  $mdThemingProvider.theme('help-card').backgroundPalette('lime', {
    'default': '100'
  });
  $mdThemingProvider.theme('manual-card').backgroundPalette('green', {
    'default': '50'
  });
  $mdThemingProvider.theme('default')
      .primaryPalette('darkBlue')
      .accentPalette('red');
  $mdThemingProvider.theme('whiteInput')
      .primaryPalette('white')
      .accentPalette('red');

  // Translation of calendar to Venezuelan localization
  $mdDateLocaleProvider.months =
      ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Septiembre', 'Octubre', 'Noviembre',
       'Diciembre'];
  $mdDateLocaleProvider.shortMonths =
      ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
  $mdDateLocaleProvider.days = ['Domingo', 'Lunes', 'Martes', 'Mi�rcoles', 'Jueves', 'Viernes', 'S�bado'];
  $mdDateLocaleProvider.shortDays = ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'];
  // Can change week display to start on Monday.
  $mdDateLocaleProvider.firstDayOfWeek = 1;

  $mdDateLocaleProvider.formatDate = function (date) {
    return date ? moment(date).format('DD/MM/YYYY') : '';
  };
  $mdDateLocaleProvider.parseDate = function (dateString) {
    var m = moment(dateString, 'DD/MM/YYYY', true);
    return m.isValid() ? m.toDate() : new Date(NaN);
  };
  $mdDateLocaleProvider.monthHeaderFormatter = function (date) {
    return $mdDateLocaleProvider.shortMonths[date.getMonth()] + ' ' + date.getFullYear();
  };
  // In addition to date display, date components also need localized messages
  // for aria-labels for screen-reader users.
  $mdDateLocaleProvider.weekNumberFormatter = function (weekNumber) {
    return 'Semana ' + weekNumber;
  };
  $mdDateLocaleProvider.msgCalendar = 'Calendario';
  $mdDateLocaleProvider.msgOpenCalendar = 'Abra el calendario';
});


sgdp.run(['$rootScope', '$location', '$state', 'Auth', '$cookies', '$http', 'Constants',
          function ($rootScope, $location, $state, Auth, $cookies, $http, Constants) {
            $rootScope.logout = function () {
              $http.get(Constants.SERVER_URL + 'LoginController/logout');
              Auth.logout();
            };
            $rootScope.$on("$locationChangeStart", function (e, toState, toParams, fromState, fromParams) {
              var url = $location.url();
              if (!Auth.isLoggedIn() &&
                  url != "/login" &&
                  !url.startsWith('/validate') &&
                  !url.startsWith('/delete') &&
                  !url.startsWith('/incompatible') &&
                  !url.startsWith('/expired') &&
                  !url.startsWith('/transition')) {
                // if user is not logged in and is trying to access
                // private content, send to login.
                e.preventDefault();
                window.location.replace(Constants.IPAPEDI_URL);
              } else if (Auth.isLoggedIn() && url == "/login") {
                // if user Is logged in and is trying to access login page
                // send to home page
                e.preventDefault();
                Auth.sendHome();
              } else if (Auth.isLoggedIn() && !userHasPermission(Auth.permission(), url)) {
                // check if user actually has access permission to intended url

                e.preventDefault();
                // if user does not have the proper permission, send to session expired view.
                $state.go('expired');
              }
            });

            // This will redirect appropriately when user clicks on BACK btn from browser.
            $rootScope.$on('$stateChangeStart', function(e, toState, toParams, fromState, fromParams) {
              if (toState.name == "transition" && fromState.name) {
                // if user clicked browser's back btn from HOME state...
                if (Auth.userType(Constants.Users.MANAGER)) {
                  // if manager then send ipapedi en linea's admin home
                  e.preventDefault();
                  window.location.replace(Constants.IPAPEDI_URL + 'administracion/admin');
                } else if (Auth.userType(Constants.Users.AGENT) || Auth.userType(Constants.Users.REVISER)) {
                  // if agent then log out
                  e.preventDefault();
                  Auth.logout();
                } else if (Auth.userType(Constants.Users.APPLICANT)) {
                  // if applicant then send ipapedi en linea's applicant home
                  e.preventDefault();
                  window.location.replace(Constants.IPAPEDI_URL + 'asociados');
                }
              }
            });

            function userHasPermission(userType, url) {
              switch (url) {
                case '/applicantHome':
                  // Anyone can access user home page
                  return userType == Constants.Users.APPLICANT;
                case '/agentHome':
                  // Check for agent rights
                  return userType == Constants.Users.AGENT;
                case '/managerHome':
                  // Check for manager rights
                  return userType == Constants.Users.MANAGER;
                case '/reviserHome':
                  // Check for reviser rights
                  return userType == Constants.Users.REVISER;
                case '/actions':
                  // check if user is not applicant
                  return userType !== Constants.Users.APPLICANT;
                case '/userInfo':
                  // every one can come here
                  return true;
                case '/perspective':
                  // check for agent or reviser rights
                  return userType == Constants.Users.AGENT || userType == Constants.Users.REVISER;
              }
              return true;
            }
          }
]);

// Slide up/down animation for ng-hide
sgdp.animation('.slide-toggle', ['$animateCss', function ($animateCss) {
  return {
    addClass: function (element, className, doneFn) {
      if (className == 'ng-hide') {
        var animator = $animateCss(element, {
          to: {height: '0px'}
        });
        if (animator) {
          return animator.start().finally(function () {
            element[0].style.height = '';
            doneFn();
          });
        }
      }
      doneFn();
    },
    removeClass: function (element, className, doneFn) {
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


sgdp.config(function($mdIconProvider) {
  //$mdIconProvider.icon('account-box', 'images/icons/ic_account_box_black_48px.svg', 24);
  $mdIconProvider.icon('assignment', 'images/icons/ic_assignment_black_48px.svg', 24);
  //$mdIconProvider.icon('assessment', 'images/icons/ic_assessment_black_48px.svg', 24);
  //$mdIconProvider.icon('error', 'images/icons/ic_error_black_48px.svg', 24);
  //$mdIconProvider.icon('verified-user', 'images/icons/ic_verified_user_black_48px.svg', 24);
  $mdIconProvider.icon('expired', 'images/icons/ic_access_time_black_48px.svg', 24);
  $mdIconProvider.icon('search', 'images/icons/ic_search_black_48px.svg', 24);
});

// Cache svg's
sgdp.run(function($http, $templateCache) {
  var urls = [
    //'images/icons/ic_account_box_black_48px.svg',
    'images/icons/ic_assignment_black_48px.svg',
    //'images/icons/ic_assessment_black_48px.svg',
    //'images/icons/ic_error_black_48px.svg',
    //'images/icons/ic_verified_user_black_48px.svg',
    'images/icons/ic_access_time_black_48px.svg',
    'images/icons/ic_search_black_48px.svg'
  ];
  angular.forEach(urls, function(url) {
    $http.get(url, {cache: $templateCache});
  });
});
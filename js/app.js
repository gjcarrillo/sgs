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
        "sgdp.directive-animate-change",
        "sgdp.directive-select-fix",
        "sgdp.directive-perspective",
        "sgdp.directive-helps",
        "sgdp.directive-overlay",
        "ui.router",
        "ngMaterial",
        "ngFileUpload",
        "ngMessages"
    ]);


sgdp.config(function ($stateProvider, $urlRouterProvider, $mdThemingProvider,
                      $mdDateLocaleProvider) {
    $urlRouterProvider.otherwise('login');
    $stateProvider
        .state('login', {
            url: '/login',
            views: {
                'content': {
                    templateUrl: 'index.php/LoginController',
                    controller: 'LoginController'
                },
                'footer': {
                    templateUrl: 'index.php/MainController/footer'
                }
            }
        })
        .state('applicantHome', {
            url: '/applicantHome',
            views: {
                'content': {
                    templateUrl: 'index.php/ApplicantHomeController',
                    controller: 'ApplicantHomeController'
                },
                'footer': {
                    templateUrl: 'index.php/MainController/footer'
                }
            }
        })
        .state('agentHome', {
            url: '/agentHome',
            views: {
                'content': {
                    templateUrl: 'index.php/AgentHomeController',
                    controller: 'AgentHomeController'
                },
                'footer': {
                    templateUrl: 'index.php/MainController/footer'
                }
            }
        })
        .state('managerHome', {
            url: '/managerHome',
            views: {
                'content': {
                    templateUrl: 'index.php/ManagerHomeController',
                    controller: 'ManagerHomeController'
                },
                'footer': {
                    templateUrl: 'index.php/MainController/footer'
                }
            }
        })
        .state('history', {
            url: '/history',
            views: {
                'content': {
                    templateUrl: 'index.php/HistoryController',
                    controller: 'HistoryController'
                }
            }
        })
        .state('perspective', {
            url: '/perspective',
            views: {
                'content': {
                    templateUrl: 'index.php/PerspectiveController',
                    controller: 'PerspectiveController'
                },
                'footer': {
                    templateUrl: 'index.php/MainController/footer'
                }
            }
        })
        .state('transition', {
            url: '/transition/:token',
            views: {
                'content': {
                    templateUrl: 'index.php/LoginController/transition',
                    controller: 'LoginController'
                }
            }
        })
        .state('userInfo', {
            url: '/userInfo',
            views: {
                'content': {
                    templateUrl: 'index.php/UserInfoController',
                    controller: 'UserInfoController'
                }
            }
        })
        .state('incompatibility', {
            url: '/incompatible',
            views: {
                'content': {
                    templateUrl: 'index.php/MainController/incompatible',
                    controller: 'IncompatibilityController'
                }
            }
        })
        .state('validate', {
            url: '/validate/:token',
            views: {
                'content': {
                    templateUrl: 'index.php/ValidationController',
                    controller: 'ValidationController'
                },
                'footer': {
                    templateUrl: 'index.php/MainController/footer'
                }
            }
        })
        .state('delete', {
            url: '/delete/:rid',
            views: {
                'content': {
                    templateUrl: 'index.php/RequestsController/deleteRequestView',
                    controller: 'DeleteController'
                },
                'footer': {
                    templateUrl: 'index.php/MainController/footer'
                }
            }
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
    $mdDateLocaleProvider.days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
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


sgdp.run(['$rootScope', '$location', '$state', 'Auth', '$cookies', '$http',
          function ($rootScope, $location, $state, Auth, $cookies, $http) {
              $rootScope.logout = function () {
                  $http.get('index.php/LoginController/logout');
                  Auth.logout();
              };
              $rootScope.$on("$locationChangeStart", function (e, toState, toParams, fromState, fromParams) {
                  var url = $location.url();
                  if (!Auth.isLoggedIn() &&
                      url != "/login" &&
                      !url.startsWith('/validate') &&
                      !url.startsWith('/delete') &&
                      !url.startsWith('/incompatible') &&
                      !url.startsWith('/transition')) {
                      // if user is not logged in and is trying to access
                      // private content, send to login.
                      e.preventDefault();
                      $state.go('login');
                  } else if (Auth.isLoggedIn() && url == "/login") {
                      // if user Is logged in and is trying to access login page
                      // send to home page
                      e.preventDefault();
                      Auth.sendHome();
                  } else if (Auth.isLoggedIn() && !userHasPermission(Auth.permission(), url)) {
                      // check if user actually has access permission to intended url

                      e.preventDefault();
                      // if user does not have the proper permission, send home
                      Auth.sendHome();
                  }
              });

              function userHasPermission(userType, url) {
                  switch (url) {
                      case '/applicantHome':
                          // Anyone can access user home page
                          return true;
                      case '/validate':
                          return userType == 3;
                      case '/delete':
                          return userType == 3;
                      case '/agentHome':
                          // Check for agent rights
                          return userType == 1;
                      case '/managerHome':
                          // Check for manager rights
                          return userType == 2;
                      case '/history':
                          // check for agent or manager rights
                          return userType <= 2;
                      case '/userInfo':
                          // check for agent or manager rights
                          return userType <= 2;
                      case '/perspective':
                          // check for agent or manager rights
                          return userType <= 2;
                  }
                  //  Going to login (.otherwise('login')), so keep going!
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
    $mdIconProvider.icon('account-box', 'images/icons/ic_account_box_black_48px.svg', 24);
    $mdIconProvider.icon('assignment', 'images/icons/ic_assignment_black_48px.svg', 24);
    $mdIconProvider.icon('assessment', 'images/icons/ic_assessment_black_48px.svg', 24);
    $mdIconProvider.icon('error', 'images/icons/ic_error_black_48px.svg', 24);
    $mdIconProvider.icon('verified-user', 'images/icons/ic_verified_user_black_48px.svg', 24);
});

// Cache svg's
sgdp.run(function($http, $templateCache) {
    var urls = [
        'images/icons/ic_account_box_black_48px.svg',
        'images/icons/ic_assignment_black_48px.svg',
        'images/icons/ic_assessment_black_48px.svg',
        'images/icons/ic_error_black_48px.svg',
        'images/icons/ic_verified_user_black_48px.svg'
    ];
    angular.forEach(urls, function(url) {
        $http.get(url, {cache: $templateCache});
    });
});
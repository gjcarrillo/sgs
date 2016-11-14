// factory controlling authentication
// uses cookies to save user session
var login = angular
    .module('sgdp.login', ['ngCookies', 'ngMaterial'])
    .factory('Auth', auth);

auth.$inject = ['$cookies', '$location', '$http', '$rootScope', '$timeout'];

function auth($cookies, $location, $http, $rootScope, $timeout) {
    var self = this;

    self.login = function (username, password) {
        $rootScope.loading = true;
        $http.get('index.php/LoginController/authenticate', {params: {id: username, password: password}})
            .then(function (response) {
                      console.log(response);
                      if (response.data.message === "success") {
                          var now = new Date();
                          // 1 year exp date
                          var timeToExpire = new Date(now.getFullYear() + 1, now.getMonth(), now.getDate());
                          // create the session cookie
                          $cookies.putObject('session', {
                              id: username,
                              type: response.data.type,
                              name: response.data.name,
                              lastName: response.data.lastName
                          }, {
                              expires: timeToExpire
                          });
                          // $cookies.putObject('session', {
                          //     id: username,
                          //     password: password,
                          //     type: response.data.type,
                          //     name: response.data.name,
                          //     lastName: response.data.lastName
                          // });
                          if (response.data.type == 3) {
                              // if applicant then redirect to home
                              $location.path("userHome");
                          } else {
                              // if agent or manager, allow perspective selection
                              $location.path("perspective");
                          }
                          $timeout(function () {
                              $rootScope.loading = false;
                          }, 1000);

                      } else {
                          $rootScope.loading = false;
                          $rootScope.model.loginError = response.data.message;
                      }
                  })
    };

    self.logout = function () {
        // remove cookie
        $cookies.remove('session');
        // redirect to login page
        $location.path("/login");
        // Remove possible data on broswer's session storage
        cleanBrowser();
        // Clear login form
        $rootScope.model = {};
    };

    self.permission = function () {
        return $cookies.getObject('session').type;
    };

    self.sendHome = function () {
        if ($cookies.getObject('session').type == 1) {
            $location.path("/agentHome");
        } else if ($cookies.getObject('session').type == 2) {
            $location.path("/managerHome");
        } else {
            $location.path("/userHome");
        }
    };

    self.isLoggedIn = function () {
        return typeof $cookies.get('session') !== "undefined";
    };

    // Clears possible data stored on browser
    function cleanBrowser() {
        sessionStorage.removeItem("requests");
        sessionStorage.removeItem("fetchId");
        sessionStorage.removeItem("selectedReq");
        sessionStorage.removeItem("showList");
        sessionStorage.removeItem("pendingRequests");
        sessionStorage.removeItem("selectedPendingReq");
        sessionStorage.removeItem("showReq");
        sessionStorage.removeItem("status");
        sessionStorage.removeItem("from");
        sessionStorage.removeItem("to");
        sessionStorage.removeItem("date");

    }

    return self;
}

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
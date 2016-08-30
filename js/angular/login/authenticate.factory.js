// factory controlling authentication
// uses cookies to save user session
angular.module('sgdp.login').factory("auth", function($cookies, $location,
    $http ,$rootScope, $timeout)
{
    return{
        login : function(username, password)
        {
            $rootScope.loading = true;
            $http.get('index.php/login/LoginController/authenticate', {params:{id: username , password:password}})
                .then(function(response) {
                    console.log(response);
                    if(response.data.message === "success") {
                        var now = new Date();
                        // 1 year exp date
                        var timeToExpire =  new Date(now.getFullYear()+1, now.getMonth(), now.getDate());
                        // create the session cookie
                        $cookies.putObject('session', {
                            id: username,
                            type: response.data.type,
                            name: response.data.name,
                            lastName: response.data.lastName
                        }, {
                            expires : timeToExpire
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
                        $timeout(function() {
                            $rootScope.loading = false;
                        }, 1000);

                    } else {
                        $rootScope.loading = false;
                        $rootScope.model.loginError =  response.data.message;
                   }
                })
        },
        logout : function()
        {
            // remove cookie
            $cookies.remove('session');
            // redirect to login page
            $location.path("/login");
            // Remove possible data on broswer's session storage
            cleanBrowser();
            // Clear login form
            $rootScope.model = {};
        },
        permission : function()
        {
            return $cookies.getObject('session').type;
        },
        sendHome : function() {
            if ($cookies.getObject('session').type == 1) {
                $location.path("/agentHome");
            } else if ($cookies.getObject('session').type == 2){
                $location.path("/managerHome");
            } else {
                $location.path("/userHome");
            }
        },
        isLoggedIn : function()
        {
            return typeof $cookies.get('session') !== "undefined" ;
        },
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
});

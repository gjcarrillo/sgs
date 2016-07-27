// factory controlling authentication
// uses cookies to save user session
angular.module('sgdp.login').factory("auth", function($cookies,$location, $http , $rootScope)
{
    return{
        login : function(username, password)
        {
            $http.get('index.php/login/LoginController/authenticate', {params:{id: username , password:password}})
                .then(function(response) {
                    console.log(response);
                    if(response.data.message === "success") {
                        var timeToExpire =  new Date();
                        timeToExpire.setDate(timeToExpire.getDate() + 7 );
                         // create the session cookie
                        $cookies.putObject('session', {
                            id: username,
                            password: password,
                            type: response.data.type,
                            name: response.data.name,
                            lastName: response.data.lastName
                        }, {
                            expires : timeToExpire
                        });
                        // redirect to home
                        if (response.data.type == 1) {
                            $location.path("adminHome");
                        } else {
                            $location.path("userHome");
                        }

                    } else {
                        $rootScope.model.loginError =  response.data.message;
                   }
                }, function (response){

            })

        },
        logout : function()
        {
            // remove cookie
            $cookies.remove('session');
            // redirect to login page
            $location.path("/login");
        },
        permission : function() {
            return $cookies.getObject('session').type;
        },
        sendHome : function() {
            if ($cookies.getObject('session').type == 1) {
                $location.path("/adminHome");
            } else {
                $location.path("/userHome");
            }
        },
        isLoggedIn : function()
        {
            return typeof $cookies.get('session') !== "undefined" ;
        },
    }
});

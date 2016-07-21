// factory controlling authentication
// uses cookies to save user session
angular.module('sgdp.login').factory("auth", function($cookies,$location, $http , $rootScope)
{
    return{
        login : function(username, password)
        {
            $http.get('index.php/login/LoginController/authenticate', {params:{username: username , password:password}})
                .then(function(response) {
                    console.log("response: " + response);
                    if(response.data.message === "success") {
                        $rootScope.loading = false;
                        var timeToExpire =  new Date();
                        timeToExpire.setDate(timeToExpire.getDate() + 7 );
                         // create the session cookie
                        $cookies.putObject('session', {username: username , password:password, id:response.data.id, profile:response.data.profile}, {
                            expires : timeToExpire
                        });
                        // redirect to home
                        $location.path("/");

                    }
                    var obj = $cookies.getObject("session");
                    console.log(obj);
                   $rootScope.model.errorLogin =  response.data.message;
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
        profile: function(){
          if($cookies.get('session') !== "undefined")
          {
            var obj = $cookies.getObject("session");
            return(obj.profile);
          }
        },
        isLoggedIn : function()
        {
            return typeof $cookies.get('session') !== "undefined" ;
        },
    }
});

app.config(['$routeProvider','$locationProvider',function($routeProvider){
    $routeProvider
        .when('/', {
            templateUrl: '/app/table.html',
            controller: 'TableCtrl'
        })
        .when('/app/:column/:parameter',{
            templateUrl: '/app/table.html',
            controller: 'TableCtrl'
        })
        .otherwise({
            templateUrl: '/app/table.html',
            controller: 'TableCtrl'
        });
}]);
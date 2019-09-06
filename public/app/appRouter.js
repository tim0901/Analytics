app.config(['$routeProvider','$locationProvider',function($routeProvider){
    $routeProvider
        .when('/', {
            templateUrl: '/app/table.html',
            controller: 'TableCtrl'
        })
        .when('/app/graph.html',{
            templateUrl: 'app/graph.html',
            controller: 'GraphCtrl'
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
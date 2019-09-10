app.directive('viz',function(){
    return {
        restrict: 'E',
        replace:true,
        template:'<svg></svg>',
        link: function(scope, element, attrs){
            scope.$watch('model',function(newData){
                updateViz(newData)
            },true);
        }
    };
});


app.controller('GraphCtrl',function($scope,$rootScope,$routeParams,$location,$http){

    //List of accessible columns
    $scope.columns = ["Event_ID","Date","Module","User","Accessed","Type","Action"];

    $scope.lists = [];
    $scope.lists.Accessed = [];

    $scope.model = null;

    $scope.plot = function plot(){
        $scope.model = [];
        $scope.model.push("/api/public/graph/&" + $scope.graphColumn0 + "=" + $scope.graphParameter0 + "&" + $scope.graphColumn1 + "=" + $scope.graphParameter1 + "&" + $scope.graphColumn2 + "=" + $scope.graphParameter2 + "&" + $scope.graphColumn3 + "=" + $scope.graphParameter3 + "&" + $scope.graphColumn4 + "=" + $scope.graphParameter4);
    };

    $scope.GetSelectedList = function(){
        

        $scope.strList = $scope.lists.Accessed;
    };

    function populateLists(){
            //Populate modules list
            $http.get("/api/public/accessed_table/").then(function(response){
                console.log(response);
                $scope.lists.Accessed  = []; //Clear first
                if(response.data == null){
                    $scope.lists.Accessed = "No modules found";
                }
                else{
                    for(var i = 0; i < response.data.length; i++){
                        $scope.lists.Accessed[i] = response.data[i].Accessed_Name;
                    }
                }
            });
        }


    populateLists();
});

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
    $scope.columns = ["Module","User","Accessed"];
    $scope.model = null;
    $scope.accessedData;
    $scope.usersData;
    $scope.modulesData;

    $scope.choices = [];
    $scope.choices[0] = [[0, '','']];
    $scope.index = 1;
    var found = null;

    //Add another line
    $scope.addNewChoice = function() {
        var newItemNo = $scope.choices.length+1;
        $scope.choices[0].push([$scope.index++,null, null]);
        console.log($scope.choices);
    };

    //Update a current line
    $scope.updateChoice = function(idx,col,opt){
        //Update the correct row in the array
        console.log("ID:" + idx + col + opt);
        found = $scope.choices[0].find(function(element){
            return element[0] == idx;
        });

        //These (thankfully) seem to work like pointers
        found[1]= col;
        found[2] = opt;
        console.log("found: ");
        console.log($scope.choices);
    };


    //Delete a line from the graph by removing its data
    $scope.deleteChoice = function(idx){
        console.log("Delete choice ID: " + idx);

        for(var i = 0; i < $scope.choices[0].length; i++){
            if($scope.choices[0][i][0] === idx){
                $scope.choices[0].splice(i,1);
                i--;
            }
            if($scope.choices[0][i][0] > idx){
                $scope.choices[0][i][0]--;
            }
        }
        $scope.index--;
        console.log($scope.choices);
        //delete $scope.choices[0][idx];
    };

    //Plot!
    $scope.plot = function plot(){
        console.log("Plot");
        $scope.model = [];
        console.log("choices: " + $scope.choices);
        $payload = $scope.choices;
        $scope.model.push("/api/public/graph/");
//      $scope.model.push("/api/public/graph/&" + $scope.graphColumn0 + "=" + $scope.graphParameter0 + "&" + $scope.graphColumn1 + "=" + $scope.graphParameter1 + "&" + $scope.graphColumn2 + "=" + $scope.graphParameter2 + "&" + $scope.graphColumn3 + "=" + $scope.graphParameter3 + "&" + $scope.graphColumn4 + "=" + $scope.graphParameter4);
        $scope.model.push($payload);
    };

    //Show correct dropdown list
    $scope.loadColumn = function (idx,col,opt) {
        if(col === "Accessed"){
            $scope.opts = $scope.accessedData;
        }
        if(col === "User"){
            $scope.opts = $scope.usersData;
        }
        if(col === "Module"){
            $scope.opts = $scope.modulesData;
        }
        $scope.updateChoice(idx,col,opt);
    };

    //Load the values for the drop down lists
    function loadValues(){
        $http.get("/api/public/users_table/").then(function(response){
            console.log(response.data);
            $scope.usersData = response.data;
        });

        $http.get("/api/public/accessed_table/").then(function(response){
            console.log(response.data);
            $scope.accessedData = response.data;
        });


        $http.get("/api/public/modules_table/").then(function(response){
            console.log(response.data);
            $scope.modulesData = response.data;
        });
    }
    loadValues()
});

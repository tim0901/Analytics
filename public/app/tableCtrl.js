app.controller('TableCtrl',function($scope,$rootScope,$routeParams,$location,$http){

    //Holds current contents of the search boxes, syncs contents with URL on page load.
    $scope.searchColumn = $routeParams.column;
    $scope.searchParameter = $routeParams.parameter;

    //Columns for drop down list
    $scope.columns = ["id","firstname","lastname","email"];

    //Load table data from server
    $scope.loadData = function loadData(column = null, parameter = null){
        $rootScope.records = null;

        //Checks that the column exists in the table, if it doesn't, column is set to null
        if(!$scope.columns.includes(column) && (column !== null)){
            console.log(column + " invalid column");
            $location.path("/app/");
        }

        //Sends GET request corresponding to given parameters
        if(column === "id" && parameter !== null){
            //Id lookup
            $http.get("/api/public/table/" + parameter).then(function(response){
                console.log(response);
                $rootScope.records = response.data;});
        }
        else if(column !== null && parameter !== null){
            //Other column lookup
            $http.get("/api/public/table/" + column + "=" + parameter).then(function(response){
                console.log(response);
                $rootScope.records = response.data;});
        }
        else{
            //Get the whole table
            $http.get("/api/public/table/").then(function(response){
                console.log(response);
                $rootScope.records = response.data;});
        }

        $scope.populateTable();
    };

    $scope.populateTable = function populateTable(){
        //Populate Table

        if($rootScope.records != null){
            for($i = 1; $i < $rootScope.records.length; $i++) {

                var $row = document.createElement('tr',label= "row" + $i);
                var $cell = document.createElement('td');
                $cell.innerHTML = $rootScope.records[$i].id;
                $row.appendChild($cell);
                $cell = document.createElement('td');
                $cell.innerHTML = $rootScope.records[$i].firstname;
                $row.appendChild($cell);
                $cell = document.createElement('td');
                $cell.innerHTML = $rootScope.records[$i].lastname;
                $row.appendChild($cell);
                $cell = document.createElement('td');
                $cell.innerHTML = $rootScope.records[$i].email;
                $row.appendChild($cell);
                $cell = document.createElement('td');
                $cell.innerHTML = "<button data-ng-click=edit(" + $rootScope.records[$i].id + ")>Edit</button>";
                $row.appendChild($cell);
                $cell = document.createElement('td');
                $cell.innerHTML = "<button data-ng-click=del(" + $rootScope.records[$i].id + ")>Remove</button>";
                $row.appendChild($cell);

                document.getElementById("Table").appendChild($row);
            }
        }
    };

    //Reload the table after making changes
    $rootScope.$on('reloadTable',function(event){
        $scope.clearTable();
        $scope.loadData($scope.searchColumn,$scope.searchParameter);
    });

    //Delete all entries in the table (locally)
    $scope.clearTable = function clearTable(){
        console.log("Clearing table");
        $rows = document.getElementsByClassName("row");
        for($i = $rows.length-1; $i >= 0; $i--){
            $rows[$i].parentNode.removeChild($rows[$i])
        }
    };

    ////Buttons

    document.getElementById("searchInput").addEventListener("keyup",function(event){
        if(event.key === "Enter"){
            event.preventDefault();
            document.getElementById("searchButton").click();
        }
    });

    //Search button
    $scope.search = function search(){
        $location.path("/app/" + $scope.searchColumn + "/" + $scope.searchParameter);
    };

    //Clear button
    $scope.clear = function clear(){
        $location.path("/app/");
    };

    //Open edit window
    $scope.edit =
        function edit(idx, fn, ln, em) {
            $rootScope.$emit('openEditModal',idx, fn, ln, em);
        };

    //Open new entry window
    $scope.crea =
        function crea() {
            $rootScope.$emit('openCreateModal');
        };

    //Delete button
    $scope.del =
        function del(idx) {
            console.log("Deleting id:" + (idx+1));
            $http.delete("/api/public/table/" + (idx+1)).then(function(response){
                console.log(response);
                $rootScope.$emit('reloadTable');
            })
        };


    //When finished loading, load the table based on the URL.
    $scope.loadData($routeParams.column,$routeParams.parameter);

});
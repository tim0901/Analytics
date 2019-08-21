app.controller('TableCtrl',function($scope,$rootScope,$routeParams,$location,$http){

    //Holds current contents of the search boxes, syncs contents with URL on page load.
    $scope.searchColumn = $routeParams.column;
    $scope.searchParameter = $routeParams.parameter;

    //Columns for drop down list
    $scope.columns = ["id","firstname","lastname","email"];

    function getData(column = null, parameter = null){
        $rootScope.records = null;

        //Checks that the column exists in the table, if it doesn't, column is set to null
        if(!$scope.columns.includes(column) && (column !== null)){
            console.log(column + " invalid column");
            $location.path("/app/");
        }

        //Sends GET request corresponding to given parameters
        if(column === "id" && parameter !== null){
            //Id lookup
            setTimeout(()=>{
                $http.get("/api/public/table/" + parameter).then(function(response){
                    console.log(response);
                    $rootScope.records = response.data;
                    populateTable();
                });
            },1000);
        }
        else if(column !== null && parameter !== null){
            //Other column lookup
            setTimeout(()=>{
                $http.get("/api/public/table/" + column + "=" + parameter).then(function(response){
                    console.log(response);
                    $rootScope.records = response.data;
                    populateTable();
                });
            },1000);
        }
        else{
            //Get the whole table
            setTimeout(()=>{
                $http.get("/api/public/table/").then(function(response){
                    console.log(response);
                    $rootScope.records = response.data;
                    populateTable();
                });
            },1000);
        }
    }

    //Load table data from server
    function loadData(column = null, parameter = null){
        return new Promise((resolve) => {
            resolve(getData(column,parameter));
        });
    }

    //Populate the Table
    function populateTable(){

        clearTable();

        let row,cell = null;

        //Filling the table is dealt with by table.html. This returns an error if no data is returned.
        if($rootScope.records == null) {
            row = document.createElement('tr');
            row.setAttribute("class","row");
            cell = document.createElement('td');
            cell.innerHTML = "No records found";
            row.appendChild(cell);
            document.getElementById("Table").appendChild(row);
        }
    }

    //Reload the table after making changes
    $rootScope.$on('reloadTable',function(event){
        loadData($routeParams.column,$routeParams.parameter);
    });

    //Delete all entries in the table (locally)
    function clearTable(){
        console.log("Clearing table");
        let rows = document.getElementsByClassName("row");
        for(let i = rows.length-1; i >= 0; i--){
            rows[i].parentNode.removeChild(rows[i]);
        }
    }



    ////Buttons

    //Enter to search
    document.getElementById("searchInput").addEventListener("keyup",function(event){
        if(event.key === "Enter"){
            event.preventDefault();
            document.getElementById("searchButton").click();
        }
    });

    //Search button
    $scope.search = function search(){
        console.log("search:" + $scope.searchColumn + " for " + $scope.searchParameter);
        if($scope.searchParameter === undefined){
            $location.path("/app/");
        }
        else{
            $location.path("/app/" + $scope.searchColumn + "/" + $scope.searchParameter);
        }
    };

    //Clear button
    $scope.clear = function clear(){
        $location.path("/app/");
    };

    //Open edit window
    $scope.edit =
        function edit(idx, fn, ln, em) {
            console.log("edit");
            $rootScope.$emit('openEditModal',idx, fn, ln, em);
        };

    //Open new entry window
    $scope.crea =
        function crea() {
            $rootScope.$emit('openCreateModal');
        };

    //Delete button
    $scope.del = function del(idx) {
        console.log("Deleting id:" + (idx));
        $http.delete("/api/public/table/" + (idx)).then(function(response){
            console.log(response);
            $rootScope.$emit('reloadTable');
        })
    };

    //When finished loading, load the table based on the URL.
    loadData($routeParams.column,$routeParams.parameter);

});
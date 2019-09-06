app.controller('TableCtrl',function($scope,$rootScope,$routeParams,$location,$http){

    //Holds current contents of the search boxes, syncs contents with URL on page load.
    $scope.searchColumn = $routeParams.column;
    $scope.searchParameter = $routeParams.parameter;
    $rootScope.currentTable = "event_table";

    //Columns for drop down list
    $scope.columns = ["Event_ID","Date","Module","User","Accessed","Type","Action"];
    $rootScope.modulesList = [];

    $rootScope.selectedFile = document.getElementById("uploadFile");

    function createDatabase(){
        $http.post("api/public/createDatabase/").then(function(response){
            console.log(response);
            loadData("event_table",$routeParams.column,$routeParams.parameter);
        })
    }

    //GET requests
    function getData(table = "event_table", column = null, parameter = null){
        $rootScope.records = null;

        //Checks that the desired column exists in the table, if it doesn't, column is set to null. Null is excluded to stop this being fired when asking for the whole table.
        if(!$scope.columns.includes(column) && (column !== null)){
            console.log(column + " invalid column");
            $location.path("/app/");
        }

        //Sends GET request corresponding to given parameters
        if(column === "event_id" && parameter !== null){
            //Id lookup
            setTimeout(()=>{
                $http.get("/api/public/event_table/" + parameter).then(function(response){
                    console.log(response);
                    $rootScope.records = response.data;
                    $rootScope.currentTable = table;
                    populateTable();
                });
            },1000);
        }
        else if(column !== null && parameter !== null){
            //Other column lookup
            setTimeout(()=>{
                $http.get("/api/public/event_table/" + column + "=" + parameter).then(function(response){
                    console.log(response);
                    $rootScope.records = response.data;
                    $rootScope.currentTable = table;
                    populateTable();
                });
            },1000);
        }
        else{
            //Get the whole table
            setTimeout(()=>{
                $http.get("/api/public/event_table/").then(function(response){
                    console.log(response);
                    $rootScope.records = response.data;
                    populateTable();
                });
            },1000);
        }
    }

    //Load table data from server
    function loadData(table = "event_table", column = null, parameter = null){
        //Populate modules list
        $http.get("/api/public/modules_table/").then(function(response){
            console.log(response);
            $rootScope.modulesList = []; //Clear first
            if(response.data == null){
                    $rootScope.modulesList[0] = "No modules found";
            }
            else{
                for(var i = 0; i < response.data.length; i++){
                    $rootScope.modulesList[i] = response.data[i].Module_Name;
                }
            }
        });

        getData(table,column,parameter);
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
            $rootScope.firstOpen = false;
            $location.path("/app/" + $scope.searchColumn + "/" + $scope.searchParameter);
        }
    };

    //Clear button
    $scope.clear = function clear(){
        $rootScope.firstOpen = false;
        $location.path("/app/");
    };

    //Open edit window
    $scope.edit = function edit(eid, da, md, us, acc, ty, act) {
        console.log("edit");
        $rootScope.$emit('openEditModal',eid, da, md, us, acc, ty, act);
    };

    //Open new entry window
    $scope.crea = function crea() {
        $rootScope.$emit('openCreateModal');
    };

    //Upload button
    $scope.uploadBtn = function uploadBtn(){
        $rootScope.$emit('openUploadModal');
    };

    //Delete module button
    $scope.deleteModuleBtn = function deleteModuleBtn(){
        $rootScope.$emit('openDeleteModuleModal');
    };

    //Delete entry button
    $scope.del = function del(eid) {
        console.log("Deleting id:" + (eid));
        $http.delete("/api/public/"+ $rootScope.currentTable + "/" + (eid)).then(function(response){
            console.log(response);
            $rootScope.$emit('reloadTable');
        })
    };

    //When finished loading, load the table based on the URL, if this is the first session.
    if(!$rootScope.firstOpen){
        $rootScope.firstOpen = true;
        createDatabase();
    }
    //createDatabase();
    //loadData("event_table",$routeParams.column,$routeParams.parameter);

});
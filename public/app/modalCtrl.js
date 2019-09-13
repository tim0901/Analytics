app.controller('modalController',function($scope,$rootScope,$routeParams,$http){

    $scope.openCreateModal = false;
    $scope.openEditModal = false;
    $scope.openUploadModal = false;
    $scope.openDeleteModuleModal = false;

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    //Open the modal when the edit button is pressed
    $rootScope.$on('openEditModal',function(event, eid, da, md, us, acc, ty, act){
        $scope.event_id = eid;
        $scope.date = da;
        $scope.module = md;
        $scope.user = us;
        $scope.accessed = acc;
        $scope.type = ty;
        $scope.action = act;
        //Aaand open
        $scope.openEditModal = true;

    });

    //Look for event in rootscope, then open modal
    $rootScope.$on('openCreateModal',function(event){

        $scope.newDate = null;
        $scope.newModule = null;
        $scope.newUser = null;
        $scope.newAccessed = null;
        $scope.newType = null;
        $scope.newAction = null;

        $scope.openCreateModal = true;
    });

    $rootScope.$on('openDeleteModuleModal',function(event){
        $scope.openDeleteModuleModal = true;
    });

    $rootScope.$on('openUploadModal',function(event){
        $scope.newModule = null;
        $scope.openUploadModal = true;
    });

    //Send put request to database
    $scope.submit = function submit(){
        console.log("/api/public/"+ $rootScope.currentTable + "/" + $scope.event_id + "&Date=" + $scope.date + "&Module=" + $scope.module + "&User=" + $scope.user + "&Accessed=" + $scope.accessed + "&Type=" + $scope.type + "&Action=" + $scope.action);
        $http.put("/api/public/"+ $rootScope.currentTable + "/" + $scope.event_id + "&Date=" + $scope.date + "&Module=" + $scope.module + "&User=" + $scope.user + "&Accessed=" + $scope.accessed + "&Type=" + $scope.type + "&Action=" + $scope.action)
            .then(function (response){
                console.log(response);
                $scope.openEditModal = false;
                $rootScope.$emit('reloadTable');
            });
    };

    //Send post request to database
    $scope.create = function create(){
        $http.post("/api/public/"+ $rootScope.currentTable + "/" + "date=" + $scope.newDate + "&module=" + $scope.newModule + "&user=" + $scope.newUser + "&accessed=" + $scope.newAccessed + "&type=" + $scope.newType + "&action=" + $scope.newAction)
            .then(function (response){
                console.log(response);
                $scope.openCreateModal = false;
                $rootScope.$emit('reloadTable');
            })
    };

    //Delete all entries for a given module in the database
    $scope.deleteModule = function deleteModule(moduleToDelete){
        $http.delete("/api/public/batchUpload/" + moduleToDelete + "/")
            .then(function (response){
                console.log(response);
                $rootScope.modulesList = $rootScope.modulesList.filter(mod => mod !== moduleToDelete); //Filter the module that has been removed from the array of modules
                $scope.deletingModule = false;
                $scope.openDeleteModuleModal = false;
                $rootScope.$emit('reloadTable');
            });
    };

    //Upload file
    $scope.upload = function upload(){
        var reader = new FileReader();

        reader.onload = (function(theFile){
            return function (e) {
                $scope.uploading = true;
                $rootScope.modulesList.push($scope.newModule);
                $http.post("/api/public/batchUpload/" + $scope.newModule + "/", e.target.result)
                    .then(function (response){
                        console.log(response);
                        $scope.uploading = false;
                        $scope.openUploadModal = false;
                        $rootScope.$emit('reloadTable');
                    });
            };
        })($rootScope.selectedFile.files[0]);

        reader.readAsText($rootScope.selectedFile.files[0]);
    };

    // Clicking on <span> (x) closes the modal
    span.onclick = function() {
        $scope.openEditModal = false;
        $scope.openCreateModal = false;
        $scope.openUploadModal = false;
        $scope.openDeleteModuleModal = false;
        $scope.$digest();
    };

    // Clicking anywhere outside of the modal closes it
    window.onclick = function(event) {
        if (event.target === document.getElementById("editModal")) {
            $scope.openEditModal = false;
            $scope.$digest();
        }
        else if(event.target=== document.getElementById("deleteModuleModal")){
            $scope.openDeleteModuleModal = false;
            $scope.$digest();
        }
        else if(event.target=== document.getElementById("createModal")){
            $scope.openCreateModal = false;
            $scope.$digest();
        }
        else if(event.target=== document.getElementById("uploadModal")){
            $scope.openUploadModal = false;
            $scope.$digest();
        }
    };

});
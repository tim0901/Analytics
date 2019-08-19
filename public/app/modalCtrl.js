app.controller('modalController',function($scope,$rootScope,$routeParams,$http){

    $scope.openCreateModal = false;
    $scope.openEditModal = false;

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    //Open the modal when the edit button is pressed
    $rootScope.$on('openEditModal',function(event, idx, fn, ln, em){
        $scope.id = idx;
        $scope.firstname = fn;
        $scope.lastname = ln;
        $scope.email = em;
        //Aaand open
        $scope.openEditModal = true;

    });

    //Look for event in rootscope, then open modal
    $rootScope.$on('openCreateModal',function(event){

        $scope.newfirstname = null;
        $scope.newlastname = null;
        $scope.newemail = null;
        $scope.openCreateModal = true;
    });

    //Send put request to database
    $scope.submit = function submit(){
        $http.put("/api/public/table/" + $scope.id + "&firstname=" + $scope.firstname + "&lastname=" + $scope.lastname + "&email=" + $scope.email)
            .then(function (response){
                console.log(response);
                $scope.openEditModal = false;
                $rootScope.$emit('reloadTable');
            });
    };

    //Send post request to database
    $scope.create = function create(){
        $http.post("/api/public/table/" + "firstname=" + $scope.newfirstname + "&lastname=" + $scope.newlastname + "&email=" + $scope.newemail)
            .then(function (response){
                console.log(response);
                $scope.openCreateModal = false;
                $rootScope.$emit('reloadTable');
            })
    };

    // Clicking on <span> (x) closes the modal
    span.onclick = function() {
        $scope.openEditModal = false;
        $scope.openCreateModal = false;
        $scope.$digest();
    };

    // Clicking anywhere outside of the modal closes it
    window.onclick = function(event) {
        if (event.target === document.getElementById("editModal")) {
            $scope.openEditModal = false;
            $scope.$digest();
        }
        if(event.target=== document.getElementById("createModal")){
            $scope.openCreateModal = false;
            $scope.$digest();
        }
    };

});
angular.module('hnApp', []).controller('listCtrl', function($scope, $http) {
	$http.get('src/data/data.json')
       .then(function(src){
          $scope.articles = src.data;
        });
});
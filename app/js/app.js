// app.js
(function() {
	angular.module('app', ['ngRoute', 'ngResource'])

    .factory('USER', ['$resource', function($resource){
      return $resource('/jobbook/user/role');
    }])

	.factory('API', ['$resource', function($resource) {
		return $resource('/jobbook/jobs/:job_number');
	}])
		
	.config(function($routeProvider) {
		$routeProvider
			.when('/', {
				controller:'JobListCtrl',
				templateUrl:'app/views/list.html'
			})
			.when('/edit/:id', {
				controller:'JobEditCtrl',
				templateUrl:'app/views/detail.html'
			})
			.when('/new', {
				controller:'JobCreateCtrl',
				templateUrl:'app/views/detail.html'
			})
			.otherwise({
				redirectTo:'/'
			});
	})
	
	.controller('JobListCtrl', ['$scope', 'API', function($scope, API) {
		$scope.orderBy = { field: 'job_number', asc: true };

		$scope.setOrderBy = function(field) {
			var asc = $scope.orderBy.field === field ? !$scope.orderBy.asc : true;
			$scope.orderBy = { field: field, asc: asc };
		};
		
		$scope.jobs = API.query();
	}])
	
	.controller('JobCreateCtrl', function($scope, $location, $timeout, API, USER) {
        $scope.resource_mode = "Add";
        $scope.user = USER.get({});

        $scope.save = function() {
			var newJob = new API({
                job_number: $scope.job.job_number,
				date: $scope.job.date,
				client_name: $scope.job.client_name,
				description: $scope.job.description,
				initials: $scope.job.initials,
				invoice_date: $scope.job.invoice_date,
				p_number: $scope.job.p_number
			});
			
			newJob.$save(function() { 
				$timeout(function() { $location.path('/'); });
			});
		};
	})
 
	.controller('JobEditCtrl', 
		function($scope, $location, $timeout, $routeParams, API, USER) {
            $scope.resource_mode = "Edit";
			$scope.job = API.get({job_number: $routeParams.id});
            $scope.user = USER.get({});

            $scope.destroy = function() {
              var d = confirm("Delete this entry?");

              if (d == true) {
                  $scope.job.$remove({job_number: $routeParams.id}, function(){
                        $timeout(function() { $location.path('/'); });
                  });
              }
			};
 
			$scope.save = function() {
				$scope.job.$save({job_number: $routeParams.id}, function(){
					$timeout(function() { $location.path('/'); });
			  });
			};
	});

	
})();
angular.module('app').controller('ArtistAboutController', function($rootScope, $scope, $http) {
    $scope.$watch('tabs.active', function(newTab, oldTab) {
        if (newTab === 'about' && ! $scope.initiated) {
            getBio();
        }
    });

    function getBio() {
        if ($scope.artist.bio && ! bioNeedsUpdating()) {
            var bio = JSON.parse($scope.artist.bio);
            $scope.bio = bio.bio;
            $scope.images = bio.images;
        } else {
            $scope.aboutLoading = true;

            $http.post('artist/'+$scope.artist.id+'/get-bio', {name: $scope.artist.name}).success(function(data) {
                $scope.bio = data.bio;
                $scope.images = data.images;
                $scope.initiated = true;
                $scope.aboutLoading = false;
            });
        }
    }

    function bioNeedsUpdating() {
        var q = new Date(), m = q.getMonth() + 1, d = q.getDate(), y = q.getFullYear();

        var now = new Date(y, m, d);

        var updatedAt = $scope.artist.updated_at.split(' ')[0].split('-');
        updatedAt = new Date(updatedAt[0], updatedAt[1], updatedAt[2]);

        var passed = now - updatedAt;

        if (passed > 0) {
            var days = Math.round((passed / (1000*60*60*24)));

            if (days >= 6) {
                return true;
            }
        }

        return false;
    }
});


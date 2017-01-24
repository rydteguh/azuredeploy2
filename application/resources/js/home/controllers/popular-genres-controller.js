angular.module('app').controller('PopularGenresController', function($rootScope, $scope, $http, utils) {
    utils.showLoader();

    var uri    = 'genres',
        names = utils.getSetting('homepageGenres');

    if (names) {
        uri = uri+'?names='+names;
    }
        
    $http.get(uri).success(function(data) { 
        $scope.genres = data;
        utils.hideLoader();

        setTimeout(function() {
            $rootScope.$emit('lazyImg:refresh');
        })
    }).error(function() {
        utils.toState('404');
        utils.hideLoader();
    });

    /**
     * Go to artist state and start playing all his tracks and albums.
     *
     * @param {object} artist
     */
    $scope.playArtist = function(artist) {
        $rootScope.autoplay = true;
        utils.toState('artist', {name: artist.name, id: artist.id});
    };
});



angular.module('app').controller('RadioController', function($rootScope, $scope, $http, $stateParams, $timeout, utils, player) {
    utils.showLoader(true);
    $scope.enableSteering = false;
    $scope.radioPageIsReady = false;
    player.ignoreNext = true;
    $scope.likedTracks = [];
    $scope.currentPlaylistIndex = 0;
    $scope.oldQueue = player.queue.slice();

    $timeout(function() {
        player.queue = [];
    });

    $http.post('radio/artist', { name: $stateParams.name, id: $stateParams.id }).success(function(data) {
        $scope.currentPlaylist = data;
        $scope.loadRadioItem($scope.currentPlaylist[0]);
    });

    $scope.loadRadioItem = function(item) {
        if ( ! item.name) {
            return alertify.delay(2000).error(utils.trans('radioNoMoreTracks'))
        }

        $http.post('get-artist', {name:item.artist.name}).success(function(data) {
            var track = findTrack(item, data);

            //if we could't find matching track in artists discography load the next track
            if ( ! track) {
                $scope.loadNextRadioItem();
            } else {
                player.addToQueue(track, true, true);
                utils.hideLoader(true);
                $scope.radioPageIsReady = true;
            }
        });
    };

    /**
     * Fetch and load next radio track.
     */
    $scope.loadNextRadioItem = function() {
        utils.showLoader(true);

        //pause player immediately and make sure elapsed time is set to 0
        $rootScope.$emit('player.trackLoadingStarted');
        player.pause();

        $scope.currentPlaylistIndex++;
        var track = $scope.currentPlaylist[$scope.currentPlaylistIndex];

        //load next track
        if (track) {
            $scope.loadRadioItem(track);
        } else {
            utils.hideLoader(true);
            return alertify.delay(2000).error(utils.trans('radioNoMoreTracks'))
        }
    };

    $scope.moreLikeThis = function(track) {
        $http.post('radio/artist/more-like-this', {session_id: $scope.sessionId, id: track.echo_nest_id});

        $scope.likedTracks.push(track.name);
        alertify.delay(2000).success(utils.trans('improvedStation'));
    };

    $scope.lessLikeThis = function(track) {
        utils.showLoader(true);
        var payload = {session_id: $scope.sessionId, id: track.echo_nest_id};
        $http.post('radio/artist/less-like-this', payload).success(function() {
            $scope.loadNextRadioItem();
            alertify.delay(2000).success(utils.trans('improvedStation'));
        })
    };

    $rootScope.$on('player.playNext', function(e) {
        $scope.loadNextRadioItem();
    });

    $scope.$on('$destroy', function() {
        player.ignoreNext = false;
        player.queue = $scope.oldQueue;
    });

    function findTrack(item, artist) {
        for (var i = 0; i < artist.albums.length; i++) {
            var album = artist.albums[i];

            for (var j = 0; j < album.tracks.length; j++) {
                if (album.tracks[j].name === item.name) {
                    var track = album.tracks[j];

                    track.image  = album.image;
                    track.image_large = artist.image_large;
                    track.artist = artist.name;
                    track.echo_nest_id = item.id;

                    return track;
                }
            }
        }
    }
});



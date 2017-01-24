angular.module('app').controller('PlaylistModifyController', function($scope, $http, modal, playlists, utils, users) {
    $scope.playlists = playlists;

    $scope.params = {};

    /**
     * Create a new playlist.
     */
    $scope.createNewPlaylist = function() {
        playlists.createNew($scope.params.name).success(function() {
            $scope.closeModal();
        }).error(function(data) {
            modal.showErrors(data);
        })
    };

    /**
     * Open create new playlist modal.
     */
    $scope.openNewPlaylistModal = function() {
        if ( ! users.current) return utils.toState('login');

        modal.show('new-playlist', $scope);
    };

    /**
     * Open playlist rename modal.
     */
    $scope.openRenamePlaylistModal = function() {
        $scope.params.name = $scope.playlist.name;
        $scope.params.description = $scope.playlist.description;
        modal.show('rename-playlist', $scope);
    };

    /**
     * Update currently open playlist.
     */
    $scope.updatePlaylist = function() {
        var payload = { name: $scope.params.name, description: $scope.params.description };

        $http.put('playlist/'+$scope.playlist.id, payload).success(function() {
            for (var i = 0; i < playlists.all.length; i++) {
                if (playlists.all[i].id == $scope.playlist.id) {
                    playlists.all[i].name = $scope.params.name;
                    playlists.all[i].description = $scope.params.description;
                    $scope.playlist.name  = $scope.params.name;
                    $scope.playlist.description  = $scope.params.description;
                }
            }

            $scope.closeModal();
        }).error(function(data) {
            $scope.error = utils.resolveErrors(data);
        })
    };

    /**
     * Open modal to confirm currently open playlists deletion.
     *
     * @param {object} playlist
     */
    $scope.confirmDeletePlaylist = function(playlist) {
        modal.confirm({
            title: 'deleteForever',
            content: 'confirmPlaylistDelete',
            subcontent: 'confirmPlaylistDelete2',
            ok: 'delete',
            onConfirm: $scope.deletePlaylist,
            onClose: $scope.closeModal,
            params: playlist
        });
    };

    /**
     * Delete currently open playlist
     *
     * @param {object} playlist
     */
    $scope.deletePlaylist = function(playlist) {
        $http.delete('playlist/'+playlist.id).success(function() {
            for (var i = 0; i < playlists.all.length; i++) {
                if (playlists.all[i].id == playlist.id) {
                    playlists.all.splice(i, 1);
                }
            }

            utils.toState('songs');
        })
    };

    /**
     * Close open modal and clear all the form values.
     */
    $scope.closeModal = function() {
        $scope.params = {};
        modal.hide();
    };
});



'use strict';

angular.module('app').controller('AdminSongsController', function($scope, $rootScope, $state, $http, modal, utils) {
    $scope.showUpdateItemModal = function(item) {
        $scope.selectedItem = angular.copy(item);
        modal.show('update-track', $scope);
    };

    $scope.uploadTrack = function(file, params, formFile) {
        $scope.trackFile = file;

        var objectUrl = URL.createObjectURL(formFile);

        var audio = document.createElement('audio');

        angular.element(audio).on("canplaythrough", function(e) {
            $scope.$apply(function() {
                $scope.selectedItem.duration = e.currentTarget.duration * 1000;
            });

            URL.revokeObjectURL(objectUrl);
        });

        audio.src = objectUrl;

        $scope.$apply(function() {
            $scope.selectedItem.url = formFile.name;
        })
    };

    $scope.updateOrCreateTrack = function() {
        if (utils.isDemo) {
            return alertify.delay(2000).error(utils.trans('noDemoPermissions'));
        }

        if ($scope.creatingTrack) {
            var promise = utils.uploadFileAndData('track', $scope.trackFile, $scope.selectedItem, 'post');
        } else {
            var promise = utils.uploadFileAndData('track/'+$scope.selectedItem.id, $scope.trackFile, $scope.selectedItem, 'put');
        }

        promise.success(function(data) {
            if ($scope.creatingTrack) {
                alertify.delay(2000).success(utils.trans('createdTrack'));
                $scope.album.tracks.push(data);
            } else {
                alertify.delay(2000).success(utils.trans('updatedTrack'));
            }

            $scope.trackFile = false;
            $scope.closeModal();
            $scope.paginate($scope.params);
        }).error(function(data) {
            $scope.setErrors(data);
        })
    };

    $scope.paginate = function(params) {
        if ($scope.ajaxInProgress || ! params) return;

        $scope.ajaxInProgress = true;
        utils.showLoader();

        $http.get('track', {params:params}).success(function(data) {
            $scope.items = data.data;
            $scope.totalItems = data.total;

            $scope.ajaxInProgress = false;
            utils.hideLoader();
        })
    };

    $scope.paginate($scope.params);
});

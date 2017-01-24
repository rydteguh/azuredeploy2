'use strict';
angular.module('app').controller('SettingsController', function($scope, $http, utils) {
    $scope.settings = [];

    $scope.logoUrl = utils.getLogoUrl();

    $scope.regularMailDrivers = ['smtp', 'mail', 'sendmail', 'log'];

    $http.get('settings').success(function(data) {
        $scope.settings = data.settings;
        $scope.info     = data.info;
    });

    $scope.clearCache = function() {
        utils.showLoader();

        $http.post('settings/clear-cache').success(function(data) {
            utils.notify(data);
        }).error(function(data) {
            utils.notify(data, 'error');
        }).finally(function() {
            utils.hideLoader();
        })
    };

    $scope.generateSitemap = function() {
        utils.showLoader();

        $http.get('generate-sitemap').success(function(data) {
            utils.notify(data);
        }).error(function(data) {
            utils.notify(data, 'error');
        }).finally(function() {
            utils.hideLoader();
        });
    };

    $scope.uploadLogo = function(file) {
        if ( ! file) {
            var promise = $http.post('settings/upload-logo');
        } else {
            var promise = $http.post('settings/upload-logo', file, {
                transformRequest: angular.identity,
                headers: {'Content-Type': undefined}
            });
        }

        promise.success(function (data) {
            $scope.logoUrl = data+'?'+Math.random();
        }).error(function(data) {
            alertify.delay(2000).error(data);
        });
    };

    $scope.updateSettings = function() {
        if (utils.isDemo) {
            alertify.delay(2000).error('Sorry, you can\'t do that on demo site.');
            return;
        }

        $http.post('update-settings', $scope.settings).success(function(data) {
            alertify.delay(2000).success(data);
        }).error(function(data) {
            alertify.delay(2000).error(data);
        });
    }
});

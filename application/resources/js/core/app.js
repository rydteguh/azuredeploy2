'use strict';

angular.module('app', ['ui.router', 'pascalprecht.translate', 'ngTagsInput', 'angularLazyImg', 'angularUtils.directives.dirPagination', '720kb.tooltips', 'duScroll'])

.value('duScrollOffset', 20)

.factory('addMethodOverrideHeader', function() {
    return {
        request: function (config) {
            if (['PATCH', 'PUT', 'DELETE'].indexOf(config.method) > -1) {
                config.headers['X-HTTP-Method-Override'] = config.method;
                config.method = 'POST';
            }

            return config;
        }
    };
})

.config(function($translateProvider, $compileProvider, $httpProvider) {
    $compileProvider.debugInfoEnabled(false);

    if (vars.selectedLocale) {
        $translateProvider.translations(vars.selectedLocale, vars.trans);
        $translateProvider.preferredLanguage(vars.selectedLocale);
    } else {
        $translateProvider.translations('en', vars.trans);
        $translateProvider.preferredLanguage('en');
    }

    $translateProvider.useUrlLoader('trans-messages');
    $translateProvider.useSanitizeValueStrategy('escaped');

    $httpProvider.interceptors.push('addMethodOverrideHeader');
})

.run(function($rootScope, $state, users, utils) {

    //set base url
    $rootScope.baseUrl = vars.baseUrl + '/';

    //see if we're running in a demo env
    utils.isDemo  = parseInt(vars.isDemo);
    utils.version = vars.version;

    //detect if user is current using a mobile
    utils.isMobile = parseInt(vars.isMobile);

    if (vars.user) {
        try {
            var user = JSON.parse(vars.user);
        } catch(e) {
            var user = false;
        }
    } else {
        var user = false;
    }

    //set current user
    users.assignCurrentUser(user);

    //show a message if any where passed from server
    if (vars.message) {
        utils.notify(vars.message, 'success', 5000);
        delete vars.message;
    }

    //load settings
    utils.setAllSettings(vars.settings);

    //remove vars script node and delete vars object from window.
    utils.removeNode('#vars'); delete window.vars;
});

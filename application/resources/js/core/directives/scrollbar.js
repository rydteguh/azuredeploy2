angular.module('app').directive('prettyScrollbar', function(utils) {
	return {
		restrict: 'A',
		link: function ($scope, el) {

            //no need for custom scrollbar on mobile
            if (utils.isMobile) return;

            Ps.initialize(el[0]);

            setTimeout(function() {
                if( ! el.scrollTop()){
                    el.scrollTop(el.scrollTop()+1);
                    el.scrollTop(el.scrollTop()-1);
                }
            }, 350)
		}
	};
});
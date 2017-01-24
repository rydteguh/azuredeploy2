angular.module('app').directive('adContainer', function(utils) {
    return {
        restrict: 'C',
        replace: true,
        priority: 0,
        link: function ($scope, el) {
            setTimeout(function() {
                var html = el.html(); el.html('');

                //add a random id to ad container
                var id = utils.randomString(5);
                el.attr('id', id);

                //find any ad code javascript that we needs to be executed
                var pattern = /<script\b[^>]*>([\s\S]*?)<\/script>/g, content;

                //strip out all script tags from ad code and leave only html
                var adHtml = html.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '').trim();

                if (adHtml) {
                    el.append(adHtml);
                }

                //execute ad code javascript and replace document.write if needed
                while (content = pattern.exec(html)) {
                    if (content[1]) {
                        var toEval = content[1].replace('document.write', 'angular.element(document.getElementById(\''+id+'\')).append');
                        eval(toEval);
                    }
                }

                //load ad code script
                var pattern2 = /<script.*?src="(.*?)"/g, match;

                while (match = pattern2.exec(html)) {
                    if (match[1]) {
                        utils.loadScript(match[1]);
                    }
                }

                //if (html && html.indexOf('google') > -1) {
                //    utils.loadScript('//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js');
                //} else {
                //    var pattern = /.+?src=.(.+?).>/g, match;
                //
                //    while (match = pattern.exec(html)) {
                //        utils.loadScript(match[1]);
                //    }
                //}
                //
                //setTimeout(function() {
                //    (adsbygoogle = window.adsbygoogle || []).push({});
                //});

                setTimeout(function() {
                    el.css('display', 'flex');
                }, 600)
            })
        }
    }
});
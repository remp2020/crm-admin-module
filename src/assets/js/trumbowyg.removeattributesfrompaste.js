(function ($) {
    'use strict';

    $.extend(true, $.trumbowyg, {
        plugins: {
            removeAttributesFromPaste: {
                init: function (trumbowyg) {
                    trumbowyg.pasteHandlers.push(function (pasteEvent) {
                        setTimeout(function () {
                            var html = trumbowyg.$ed.html();
                            html = html.replace(new RegExp('style="[^"]*"', 'gi'), '');
                            html = html.replace(new RegExp('class="[^"]*"', 'gi'), '');
                            trumbowyg.$ed.html(html);
                        }, 0);
                    });
                }
            }
        }
    });
})(jQuery);

/*
 * Handles login to ScoutnetConnect
 */
+function ($) { "use strict";
    var Base = $.oc.foundation.base,
        BaseProto = Base.prototype

    var ScoutnetConnect = function () {
        Base.call(this)
    }

    ScoutnetConnect.prototype = Object.create(BaseProto)
    ScoutnetConnect.prototype.constructor = ScoutnetConnect

    ScoutnetConnect.prototype.init = function(tabPane, formGetUrl) {
        var $button = $(tabPane).find('[data-connect-button]');

        $button.on('click', function(e) {
            e.preventDefault();

            var formId = 'u-'+Math.random().toString().replace('.', '');
                console.log($button);

            $(this).request($(this).data('js-request'), {
                url: formGetUrl,
                data: { formId: formId }
            }).then(function(data) {
                $('body').append(data.result);
                $('#'+formId).submit();
            });
        });
    }

    $(document).ready(function(){
        $.oc.ScoutnetConnect = new ScoutnetConnect()
    })

}(window.jQuery);

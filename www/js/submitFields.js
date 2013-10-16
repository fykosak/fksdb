(function($) {
    $.fn.submitFields = function(options) {
        var opts = $.extend({}, $.fn.submitFields.defaults, options);

        return this.each(function() {
            var el = $(this);
            var containerEl = el.parent();
            var rawValue = el.val();
            var data = $.parseJSON(rawValue);
            var ctId = el.data('contestant');
            
            el.hide();
            containerEl = opts.initContainer(containerEl, ctId);

            for (var tasknr in data) {
                var taskData = data[tasknr];

                opts.createElements(taskData, data, el, containerEl);
            }
        });

    };

    $.fn.submitFields.defaults = {
        /*
         * Creates element for single sumbit.         
         */
        createElements: function(taskData, data, el, containerEl) {
        },
        /*
         * Initialize container element for submits, returns the container element
         * parameter is the original cainter.
         */
        initContainer: function(containerEl, ctId) {
            return containerEl;
        }
    }

})(jQuery);

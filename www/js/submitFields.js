(function($) {
    $.fn.submitFields = function(options) {
        return this.each(function() {
            var el = $(this);
            var containerEl = el.parent();
            var rawValue = el.val();
            var data = $.parseJSON(rawValue);

            el.hide();

            for (var tasknr in data) {
                var taskData = data[tasknr];

                options.createElements(taskData, data, el, containerEl);
            }
        });

    };

})(jQuery);

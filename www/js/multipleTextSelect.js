(function($) {
    function split(val, delimiter) {
        return val.split(new RegExp(delimiter));
    }
    function extractLast(term, delimiter) {
        var result=split(term, delimiter).pop();
        return result;
    }

    $.fn.multipleTextSelect = function(options) {
        var opts = $.extend({}, $.fn.submitFields.defaults, options);
        return this.each(function() {
            var el = $(this);
            var delimiter = el.data('mt-delimiter');
            var items = el.data('mt-data');

            // don't navigate away from the field on tab when selecting an item
            el/*.bind("keydown", function(event) {
                if (event.keyCode === $.ui.keyCode.TAB &&
                        $(this).data("ui-autocomplete").menu.active) {
                    event.preventDefault();
                }
            })*/.autocomplete({
                minLength: 0,
                autoFocus: true,
                source: function(request, response) {
// delegate back to autocomplete, but extract the last term
                    var s = extractLast(request.term, delimiter);
                    response($.ui.autocomplete.filter(
                            items, s));
                },
                focus: function() {
// prevent value inserted on focus
                    return false;
                },
                select: function(event, ui) {
                    var terms = split(this.value, delimiter);
                    // remove the current input
                    terms.pop();
                    // add the selected item
                    terms.push(ui.item.value);
                    // add placeholder to get the comma-and-space at the end
                    terms.push("");
                    this.value = terms.join(", ");//TODO parametrize
                    return false;
                }
            });
        });
    };

    $(function() {
        $('.mtselect').multipleTextSelect();
    });

})(jQuery);

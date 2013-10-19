$(function() {
    $.widget("fks.autocompleteSelect", $.ui.autocomplete, {
// default options
        options: {
        },
        _create: function() {
            function split(val, delimiter) {
                return val.split(/,\s*/);
            }
            function extractLast(term) {
                var result = split(term).pop();
                return result;
            }

            var elVal = this.element;
            elVal.hide();

            var ajax = elVal.data('ac-ajax');
            var multiselect = elVal.data('ac-multiselect');
            var defaultText = elVal.data('ac-default-value');

            var el = $('<input type="text"/>')
            elVal.parent().append(el);
            elVal.data('autocomplete', el);
            if (defaultText) {
                if (typeof defaultText === 'string') {
                    el.val(defaultText);
                } else {
                    el.val(defaultText.join(', '));
                }
            }



            var select = null, focus = null, source = null;
            var cache = {}; //TODO move in better scope
            var labelCache = {};
            var termFunction = function(arg) {
                return arg;
            };
            if (multiselect) {
                termFunction = extractLast;
            }

            var options = {};

            if (ajax) {
                options.source = function(request, response) {
                    var term = termFunction(request.term);
                    if (term in cache) {
                        response(cache[ term ]);
                        return;
                    }
                    $.getJSON(elVal.data('ac-ajax-url'), {acQ: term}, function(data, status, xhr) {
                        cache[ term ] = data;
                        response(data);
                    });
                };
                options.minLength = 3;
            } else {
                var items = elVal.data('ac-items');
                options.source = function(request, response) {
                    var s = termFunction(request.term);
                    response($.ui.autocomplete.filter(
                            items, s));
                }
            }


            if (multiselect) {
                options.select = function(event, ui) {
                    labelCache[ui.item.value] = ui.item.label;
                    if (elVal.val()) {
                        elVal.val(elVal.val() + ',' + ui.item.value);
                    } else {
                        elVal.val(ui.item.value);
                    }
                    el.val(Array.concat($.map(elVal.val().split(','), function(arg) {
                        return labelCache[arg];
                    }), ['']).join(', '));
                    return false;
                };
                options.focus = function(e, ui) {
                    return false;
                };
            } else {
                options.select = function(e, ui) {
                    elVal.val(ui.item.value);
                    el.val(ui.item.label);
                    return false;
                };
                options.focus = function(e, ui) {
                    elVal.val(ui.item.value);
                    el.val(ui.item.label);

                    return false;
                };
            }
            el.autocomplete(options);


        }

    });
    $('input.autocompleteSelect').autocompleteSelect();
    ;
});
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
                if (defaultText.length) {
                    el.val(defaultText.join(', '));
                } else {
                    el.val(defaultText);
                }
            }



            var select = null, focus = null, source = null;
            var cache = {}; //TODO move in better scope
            var termFunction = function(arg) {
                return arg;
            };
            if (multiselect) {
                termFunction = extractLast;
            }

            if (ajax) {
                source = function(request, response) {
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
            } else {
                var items = elVal.data('ac-items');
                source = function(request, response) {
                    var s = termFunction(request.term);
                    response($.ui.autocomplete.filter(
                            items, s));
                }
            }


            if (multiselect) {
                select = function(event, ui) {
                    var terms = split(el.val());
                    // remove the current input
                    terms.pop();
                    // add the selected item
                    terms.push(ui.item.label);
                    // add placeholder to get the comma-and-space at the end
                    terms.push('');
                    el.val(terms.join(', '));
                    elVal.val(elVal.val() + ',' + ui.item.value); //TODO
                    return false;
                };
                focus = function(e, ui) {
                    return false;
                };
            } else {
                select = function(e, ui) {
                    elVal.val(ui.item.value);
                    el.val(ui.item.label);
                    return false;
                };
                focus = function(e, ui) {
                    elVal.val(ui.item.value);
                    el.val(ui.item.label);

                    return false;
                };
            }
            el.autocomplete({
                source: source,
                select: select,
                focus: focus
            });


        }

    });
    $('input.autocompleteSelect').autocompleteSelect();
    ;
});
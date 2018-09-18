$(function() {
    $.widget("fks.autocompleteSelect", $.ui.autocomplete, {
// default options
        options: {
            metaSuffix: '__meta'
        },
        _create: function() {
            function split(val) {
                return val.split(/,\s*/);
            }
            function extractLast(term) {
                var result = split(term).pop();
                return result;
            }

            var elVal = this.element;

            var ajax = elVal.data('ac-ajax');
            var multiselect = elVal.data('ac-multiselect');
	    var defaultValue = elVal.val();
            var defaultText = elVal.data('ac-default-value');
            var renderMethod = elVal.data('ac-render-method');

            var el = $('<input type="text"/>');
            el.attr('class', elVal.attr('class'));
            el.attr('disabled', elVal.attr('disabled'));
            elVal.replaceWith(el);
            elVal.hide();
            elVal.insertAfter(el);
            elVal.data('uiElement', el);

            // element to detect enabled JavaScript
            var metaEl = $('<input type="hidden" value="JS" />');
            // this should work both for array and scalar names
            var metaName = elVal.attr('name').replace(/(\[?)([^\[\]]+)(\]?)$/g, '$1$2' + this.options.metaSuffix + '$3');
            metaEl.attr('name', metaName);
            metaEl.insertAfter(el);

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
	    // ensures default value is always suggested (needed for AJAX)
	    var conservationFunction = function(data) {
               if (!defaultText) {
                   return data;
	       }
               var found = false;
               for (var i in data) {
                   if (data[i].value == defaultValue) {
                       found = true;
		       break;
		   }
	       } 
	       if (!found) {
                   data.push({
                       label: defaultText,
		       value: defaultValue
		   });
	       }
	       return data;
	    }
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
                        data = conservationFunction(data);
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
                };
                options.minLength = 3;
            }


            if (multiselect) {
                options.select = function(event, ui) {
                    labelCache[ui.item.value] = ui.item.label;
                    if (elVal.val()) {
                        elVal.val(elVal.val() + ',' + ui.item.value);
                    } else {
                        elVal.val(ui.item.value);
                    }
                    el.val([].concat($.map(elVal.val().split(','), function(arg) {
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
                    elVal.change();
                    return false;
                };
                options.focus = function(e, ui) {
                    elVal.val(ui.item.value);
                    el.val(ui.item.label);

                    return false;
                };
            }

            var acEl = el.autocomplete(options);

            if (renderMethod) {
                acEl.data('ui-autocomplete')._renderItem = function(ul, item) {
                    return eval(renderMethod);
                };
            }

        }
    });

    $('input[data-ac]').autocompleteSelect();

});
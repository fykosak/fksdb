$(function () {
    $.widget("fks.autocomplete-select", $.ui.autocomplete, {
// default options
        options: {
            metaSuffix: '__meta'
        },
        _create: function () {
            const split = (val) => {
                return val.split(/,\s*/);
            }

            const extractLast = (term) => {
                return split(term).pop();
            }

            var multiselect = this.element.data('ac-multiselect');
            var defaultValue = this.element.val();
            var defaultText = this.element.data('ac-default-value');

            var el = $('<input type="text"/>');
            el.attr('class', this.element.attr('class'));
            el.attr('disabled', this.element.attr('disabled'));
            this.element.replaceWith(el);
            this.element.hide();
            this.element.insertAfter(el);
            this.element.data('uiElement', el);

            // element to detect enabled JavaScript
            var metaEl = $('<input type="hidden" value="JS" />');
            // this should work both for array and scalar names
            var metaName = this.element.attr('name').replace(/(\[?)([^\[\]]+)(\]?)$/g, '$1$2' + this.options.metaSuffix + '$3');
            metaEl.attr('name', metaName);
            metaEl.insertAfter(el);

            this.element.data('autocomplete', el);
            if (defaultText) {
                if (typeof defaultText === 'string') {
                    el.val(defaultText);
                } else {
                    el.val(defaultText.join(', '));
                }
            }

            var cache = {}; //TODO move in better scope
            var labelCache = {};
            var termFunction = (arg) => {
                return arg;
            };
            // ensures default value is always suggested (needed for AJAX)
            const conservationFunction = (data) => {
                if (!defaultText) {
                    return data;
                }
                let found = false;
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

            if (this.element.data('ac-ajax')) {
                options.source = (request, response) => {
                    var term = termFunction(request.term);
                    if (term in cache) {
                        response(cache[term]);
                        return;
                    }
                    $.getJSON(this.element.data('ac-ajax-url'), {acQ: term}, (data, status, xhr) => {
                        data = conservationFunction(data);
                        cache[term] = data;
                        response(data);
                    });
                };
                options.minLength = 3;
            } else {
                var items = this.element.data('ac-items');
                options.source =  (request, response)=> {
                    var s = termFunction(request.term);
                    response($.ui.autocomplete.filter(
                        items, s));
                };
                options.minLength = 3;
            }


            if (multiselect) {
                options.select = (event, ui) => {
                    labelCache[ui.item.value] = ui.item.label;
                    if (this.element.val()) {
                        this.element.val(this.element.val() + ',' + ui.item.value);
                    } else {
                        this.element.val(ui.item.value);
                    }
                    el.val([].concat($.map(this.element.val().split(','), (arg) => {
                        return labelCache[arg];
                    }), ['']).join(', '));
                    return false;
                };
                options.focus = () => {
                    return false;
                };
            } else {
                options.select = (e, ui) => {
                    this.element.val(ui.item.value);
                    el.val(ui.item.label);
                    this.element.change();
                    return false;
                };
                options.focus = (e, ui) => {
                    this.element.val(ui.item.value);
                    el.val(ui.item.label);
                    return false;
                };
            }

            var acEl = el.autocomplete(options);

            const renderMethod = this.element.data('ac-render-method');
            if (renderMethod) {
                acEl.data('ui-autocomplete')._renderItem = (ul, item) => {
                    return eval(renderMethod);
                };
            }
        }
    });

    $('input[data-ac]')['autocomplete-select']();

});

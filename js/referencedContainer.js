$(function() {
    $.widget("fks.referencedContainer", {
// default options
        options: {
            refId: null,
            clearMask: '__clear',
            submitSearchMask: '__search',
            searchMask: '_c_search',
            colLeft: 'col-lg-3',
            colRight: 'col-lg-6',
            subColLeft: 'col-lg-3',
            subColRight: 'col-lg-9'
        },
        _create: function() {
            var elGroup = $(this.element);
            var elRefId = this.options.refId;
            var options = this.options;

            var createCompactField = function(label, value) {
                var compactGroup = $('<div class="control-group">\
<div class="form-group"><label class="control-label ' + options.colLeft + ' control-label"/>\
<div class="' + options.colRight + '"><input type="text" class="form-control"/></div></div></div>');

                var elLabel = compactGroup.find('label');
                elLabel.text(label);

                var elInput = compactGroup.find('input');
                elInput.val(value);
                return compactGroup;
            };

            var compacted = null;
            var decompactifyContainer = function() {
                if (compacted !== null) {
                    compacted.hide();
                }
                elGroup.show();
            };
            var compactifyContainer = function() {
                if (compacted === null) {
                    var label = elGroup.find('legend').text();
                    var value = elRefId.val(); // TODO load it from data()
                    compacted = createCompactField(label, value);
                    compacted.insertAfter(elGroup);
                    compacted.find('input').click(decompactifyContainer);
                    elGroup.find('legend').click(compactifyContainer);
                }
                compacted.show();
                elGroup.hide();
            };






            var elClear = elGroup.find("input[type='submit'][name*='" + this.options.clearMask + "']");
            var elSubmitSearch = elGroup.find("input[type='submit'][name*='" + this.options.submitSearchMask + "']");
            var elSearch = elGroup.find("input[name*='" + this.options.searchMask + "']");


            if (elSubmitSearch.length) {
                // append handler
                elSearch.change(function() {
                    alert("Changed to " + $(this).val());
                    elSubmitSearch.click();
                });

                // change form
                var searchGroup = elSearch.closest('.control-group');


                searchGroup.children('.form-group').children('.control-label').removeClass(this.options.subColLeft).addClass(this.options.colLeft);
                searchGroup.children('.form-group').children('div').removeClass(this.options.subColRight).addClass(this.options.colRight);

                searchGroup.children('.form-group').children('.control-label').text(elGroup.find('legend').text());

                elGroup.replaceWith(searchGroup);
                elGroup.hide();
                elGroup.appendTo(searchGroup);// we need the group to working form

            }
            if (elClear.length) {
                compactifyContainer();
            }


        }
    });

});
$(function() {

    $.widget("fks.referencedContainer", {
// default options
        options: {
            refId: null,
            clearMask: '__clear',
            submitSearchMask: '__search',
            searchMask: '_c_search',
            compactValueMask: '_c_compact',
            colLeft: 'col-lg-3',
            colRight: 'col-lg-6',
            subColLeft: 'col-lg-3',
            subColRight: 'col-lg-9'
        },
        _create: function() {
            var elContainer = $(this.element);
            this.options.refId = $(this.options.refId);

            this.transformContainer(elContainer);
        },
        transformContainer: function(elContainer) {
            var elSearch = elContainer.find("input[name*='" + this.options.searchMask + "']");
            var elCompactValue = elContainer.find("input[name*='" + this.options.compactValueMask + "']");
            var elSubmitSearch = elContainer.find("input[type='submit'][name*='" + this.options.submitSearchMask + "']");
            var elClear = elContainer.find("input[type='submit'][name*='" + this.options.clearMask + "']");
            var compacted = null;
            var options = this.options;

            function searchifyContainer() {
                // create search button
                var searchButton = $('<span class="input-group-btn"><button class="btn btn-default" type="button"><span class="glyphicon glyphicon-search"></span></button></span>');
                searchButton.click(function() {
                    elSubmitSearch.click();
                });

                var searchInputGroup = $('<div class="input-group"/>');
                elSearch.replaceWith(searchInputGroup);
                searchInputGroup.append(elSearch);
                searchInputGroup.append(searchButton);

                // append handler
                elSearch.change(function() {
                    elSubmitSearch.click();
                });

                elSearch.keydown(function(e) {
                    var code = e.keyCode || e.which;
                    if (code == '9') {
                        var controls = $('.form-control,input[type="submit"]');
                        var next = this;
                        //var next = controls.eq(controls.index(this) + 1);
                        do {
                            next = controls.eq(controls.index(next) + 1);
                            next.focus();
                        } while (!next.is(':focus'));

                        return false;
                    }
                });
                // promote search group in place of the container
                var searchGroup = elSearch.closest('.form-group');

                searchGroup.children('.control-label').removeClass(options.subColLeft).addClass(options.colLeft);
                searchGroup.children('div').removeClass(options.subColRight).addClass(options.colRight);

                searchGroup.children('.control-label').text(elContainer.find('legend').text());

                searchGroup.attr('id', elContainer.attr('id'));
                elContainer.attr('id', null);
                elContainer.replaceWith(searchGroup);
                elContainer.hide();
                elContainer.appendTo(searchGroup);// we need the group to working form
            }

            function decompactifyContainer() {
                if (compacted !== null) {
                    compacted.hide();
                }
                elContainer.show();
            }

            function createCompactField(label, value) {
                var compactGroup = $('<div class="form-group">\
        <label class="control-label ' + options.colLeft + ' control-label"/>\
<div class="' + options.colRight + ' value"><p class="form-control-static"/></div></div>');

                var elLabel = compactGroup.find('label');
                elLabel.text(label);

                var elValue = compactGroup.find('p.form-control-static');
                elValue.text(value);

                var buttonEdit = $('<button type="button" class="btn btn-sm btn-default" title="Upravit"><span class="glyphicon glyphicon-edit"></span></button>');
                buttonEdit.click(decompactifyContainer);

                var buttonDel = $('<button type="button" class="btn btn-sm btn-warning" title="Smazat"><span class="glyphicon glyphicon-trash"></span></button>');
                buttonDel.click(function() {
                    elClear.click();
                });

                var tlb = $('<div class="btn-toolbar pull-right"/>');
                buttonEdit.appendTo(tlb);
                buttonDel.appendTo(tlb);
                tlb.appendTo(elValue);

                return compactGroup;
            }



            function compactifyContainer() {
                if (compacted === null) {
                    var label = elContainer.find('legend').text();
                    var value = elCompactValue.val();
                    compacted = createCompactField(label, value); //TODO clear button
                    compacted.insertAfter(elContainer);
                    compacted.find('.value').click(decompactifyContainer);
                    //elContainer.find('legend').click(compactifyContainer);
                    decorateClearButton(); //in original container
                }
                compacted.show();
                elContainer.hide();
            }

            function decorateClearButton() {
                var well = elContainer.find('.well');
                //var button = $('<button type="button" class="close" title="Smazat"><span class="glyphicon glyphicon-trash"></span></button>');
                var buttonDel = $('<button type="button" class="btn btn-sm btn-warning pull-right" title="Smazat"><span class="glyphicon glyphicon-trash"></span></button>');
                buttonDel.click(function() {
                    elClear.click();
                });
                elClear.closest('.form-group').hide();
                buttonDel.prependTo(well);
            }

            var hasAnyFields = elContainer.find(":input[type!='hidden'][disabled!='disabled']").not(elClear).filter(function() {
                return $(this).val() == '';
            });


            if (elSearch.length) {
                searchifyContainer();
            } else if (elClear.length && !hasAnyFields.length) {
                compactifyContainer();
            } else if (elClear.length && hasAnyFields.length) {
                decorateClearButton();
            }
        }
    });

    $.nette.ext('referencedContainer', {
        success: function(payload, status, jqXHR, settings) {
            if (payload.referencedContainer) {
                var el = $('#' + payload.referencedContainer.id);
                el.val(payload.referencedContainer.value);

                for (var id in payload.snippets) {
                    $.fks.referencedContainer('tranformContainer', $('#' + id));
                }
            }
        }
    }, {});

});
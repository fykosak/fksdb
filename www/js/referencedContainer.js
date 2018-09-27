$(function() {

    $.widget("fks.referencedContainer", {
// default options
        options: {
            refId: null,
            valuePromise: '__promise',
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
            this.transformContainer(elContainer, $('#' + elContainer.data('referenced-id')));
        },
        transformContainer: function(elContainer, elRefId) {
            var elSearch = elContainer.find("input[name*='" + this.options.searchMask + "'][type!='hidden']");
            var elCompactValue = elContainer.find("input[name*='" + this.options.compactValueMask + "']");
            var elSubmitSearch = elContainer.find("input[type='submit'][name*='" + this.options.submitSearchMask + "']");
            var elClear = elContainer.find("input[type='submit'][name*='" + this.options.clearMask + "']");
            var compacted = null;
            var options = this.options;
            if (elRefId) {
                options.refId = elRefId;
            }

            function searchifyContainer() {
                // create search button
                var searchButton = $('<span class="input-group-btn"><button class="btn btn-default" type="button"><span class="fa fa-search glyphicon glyphicon-search"></span></button></span>');
                searchButton.click(function() {
                    elSubmitSearch.click();
                });

                var searchInputGroup = $('<div class="input-group"/>');
                var elToReplace = elSearch;
                if(elSearch.data('uiElement')) {
                    elToReplace = elSearch.data('uiElement');
                }

                // Workaround for broken replaceWith()
                //elToReplace.replaceWith(searchInputGroup);
                var par = elToReplace.parent();
                var prev = elToReplace.prev();
                if(prev.length) {
                    searchInputGroup.insertAfter(prev);
                } else {
                    searchInputGroup.prependTo(par);
                }

                searchInputGroup.append(elSearch);
                searchInputGroup.append(elToReplace);
                searchInputGroup.append(searchButton);

                // append handler
                elSearch.change(function() {
                    elSubmitSearch.click();
                });

                elSearch.keydown(function(e) {
                    var code = e.keyCode || e.which;
                    var hasShift = e.shiftKey;
                    if (code == '9') {
                        var controls = $('.form-control,input[type="submit"],input[type="checkbox"]');
                        var next = this;
                        var diff = hasShift ? -1 : 1;
                        //var next = controls.eq(controls.index(this) + 1);
                        do {
                            next = controls.eq(controls.index(next) + diff);
                            next.focus();
                        } while (!next.is(':focus'));

                        return false;
                    }
                });

                elSearch.keypress(function(e) {
                    if (e.which == 13) {
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

                // ensure proper filling of the referenced id
                var writableFields = elContainer.find(":input[type!='hidden'][disabled!='disabled']").not(elClear);
                writableFields.change(function() {
                    var filledFields = writableFields.filter(function() {
                        return $(this).val() != '';
                    });
                    if (filledFields.length > 0 && options.refId.val() == '') {
                        options.refId.val(options.valuePromise);
                    } else if (filledFields.length == 0 && options.refId.val() == options.valuePromise) {
                        options.refId.val('');
                    }
                });

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

                var buttonEdit = $('<button type="button" class="btn btn-sm btn-default" title="Upravit"><span class="glyphicon glyphicon-edit fa fa-pencil"></span></button>');
                buttonEdit.click(decompactifyContainer);

                var buttonDel = $('<button type="button" class="btn btn-sm btn-warning" title="Smazat"><span class="glyphicon glyphicon-remove fa fa-remove"></span></button>');
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
                    var label = elContainer.find('> fieldset > legend').text();
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
                var well = elContainer.children('.well');
                var buttonDel = $('<button type="button" class="btn btn-sm btn-warning clear-referenced" title="Smazat"><span class="glyphicon glyphicon-remove fa fa-remove"></span></button>');
                buttonDel.click(function() {
                    elClear.click();
                });
                elClear.closest('.form-group').hide();
                buttonDel.prependTo(well);
            }

            var hasAnyFields = elContainer.find(":input[type!='hidden'][disabled!='disabled']").not(elClear).filter(function() {
                return $(this).val() == '' && !$(this).attr('data-writeonly-overlay');
            });

            var hasErrors = elContainer.find(".has-error");


            if (elSearch.length) {
                searchifyContainer();
            } else if (elClear.length && !(hasAnyFields.length || hasErrors.length)) {
                compactifyContainer();
            } else if (elClear.length && (hasAnyFields.length || hasErrors.length)) {
                decorateClearButton();
            }
        }
    });
    if (!$.nette.ext('referencedContainer')) {
        $.nette.ext('referencedContainer', {
            success: function(payload, status, jqXHR, settings) {
                if (payload.referencedContainer) {
                    var elRefId = $('#' + payload.referencedContainer.id);
                    elRefId.val(payload.referencedContainer.value);

                    for (var id in payload.snippets) {
                        var snippet = $('#' + id);
                        $.fks.referencedContainer._proto.transformContainer(snippet, elRefId);
                        snippet.closest('form').each(function() {
                            window.Nette.initForm(this);
                            $(this).enterSubmitForm('update');
                        });
                    }
                }
            }
        }, {});
    }
    $("[data-referenced]").referencedContainer();

});

$(function () {

    $.widget("fks.referencedContainer", {
// default options
        options: {
            refId: null,
            valuePromise: '__promise',
            clearMask: '__clear',
            submitSearchMask: '__search',
            searchMask: '_c_search',
            compactValueMask: '_c_compact',
        },
        _create: function () {
            var elContainer = $(this.element);
            this.transformContainer(elContainer, $('#' + elContainer.data('referenced-id')));
        },
        transformContainer: function (elContainer, elRefId) {
            const $searchInput = elContainer.find("input[name*='" + this.options.searchMask + "'][type!='hidden']");
            const $compactValueInput = elContainer.find("input[name*='" + this.options.compactValueMask + "']");
            const $searchButton = elContainer.find("input[type='submit'][name*='" + this.options.submitSearchMask + "']");
            const $clearButton = elContainer.find("input[type='submit'][name*='" + this.options.clearMask + "']");
            var compacted = null;
            var options = this.options;
            if (elRefId) {
                options.refId = elRefId;
            }

            const searchifyContainer = function () {

                // create search button
                var searchButton = $('<button class="input-group-append btn btn-secondary" type="button"><span class="fa fa-search"></span></button>');
                searchButton.click(function () {
                    $searchButton.click();
                });

                var searchInputGroup = $('<div class="input-group"/>');
                var elToReplace = $searchInput;
                if ($searchInput.data('uiElement')) {
                    elToReplace = $searchInput.data('uiElement');
                }

                // Workaround for broken replaceWith()
                //elToReplace.replaceWith(searchInputGroup);
                var par = elToReplace.parent();
                var prev = elToReplace.prev();
                if (prev.length) {
                    searchInputGroup.insertAfter(prev);
                } else {
                    searchInputGroup.prependTo(par);
                }

                searchInputGroup.append($searchInput);
                searchInputGroup.append(elToReplace);
                searchInputGroup.append(searchButton);

                // append handler
                $searchInput.change(function () {
                    $searchButton.click();
                });
                // promote search group in place of the container
                var searchGroup = $searchInput.closest('.form-group');


                searchGroup.children('.control-label').text(elContainer.find('legend').text());

                searchGroup.attr('id', elContainer.attr('id'));
                elContainer.attr('id', null);
                elContainer.replaceWith(searchGroup);
                elContainer.hide();
                elContainer.appendTo(searchGroup);// we need the group to working form

                // ensure proper filling of the referenced id
                var writableFields = elContainer.find(":input[type!='hidden'][disabled!='disabled']").not($clearButton);
                writableFields.change(function () {
                    var filledFields = writableFields.filter(function () {
                        return $(this).val() != '';
                    });
                    if (filledFields.length > 0 && options.refId.val() == '') {
                        options.refId.val(options.valuePromise);
                    } else if (filledFields.length == 0 && options.refId.val() == options.valuePromise) {
                        options.refId.val('');
                    }
                });

            };

            function decompactifyContainer() {
                if (compacted !== null) {
                    compacted.hide();
                }
                elContainer.show();
            }

            function createCompactField(label, value) {
                const $compactGroup = $('<div class="form-group">\
        <label class="control-label"/>\
<div class="input-group"><p class="form-control-static form-control"/></div></div>');

                const elLabel = $compactGroup.find('label');
                elLabel.text(label);

                const elValue = $compactGroup.find('p.form-control-static');
                const $label = $('<span></span>');
                elValue.append('<span class="fa fa-user mr-3"></span>');
                $label.text(value);
                elValue.append($label);

                const $btnContainer = $('<div class="input-group-append"></div>');
                const $buttonEdit = $('<button type="button" class="btn btn-secondary" title="Upravit"><span class="fa fa-pencil"></span></button>');
                $buttonEdit.click(decompactifyContainer);

                const $buttonDel = $('<button type="button" class="btn btn-warning" title="Smazat"><span class="fa fa-remove"></span></button>');
                $buttonDel.click(function () {
                    $clearButton.click();
                });
                $btnContainer.append($buttonEdit);
                $btnContainer.append($buttonDel);

                elValue.parent('.input-group').append($btnContainer);

                return $compactGroup;
            }


            function compactifyContainer() {

                if (compacted === null) {
                    const label = elContainer.find('> fieldset > h4').text();
                    const value = $compactValueInput.val();
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
                var well = elContainer.children('.bd-callout');
                var buttonDel = $('<button type="button" class="btn btn-sm btn-warning clear-referenced" title="Smazat"><span class="fa fa-remove"></span></button>');
                buttonDel.click(function () {
                    $clearButton.click();
                });
                $clearButton.closest('.btn-group').hide();
                buttonDel.prependTo(well);
            }

            var hasAnyFields = elContainer.find(":input[type!='hidden'][disabled!='disabled']").not($clearButton).filter(function () {
                return $(this).val() == '' && !$(this).attr('data-writeonly-overlay');
            });

            var hasErrors = elContainer.find(".has-error");

            if ($searchInput.length) {
                searchifyContainer();
            } else if ($clearButton.length && !(hasAnyFields.length || hasErrors.length)) {
                compactifyContainer();
            } else if ($clearButton.length && (hasAnyFields.length || hasErrors.length)) {
                decorateClearButton();
            }
        }
    });
    if (!$.nette.ext('referencedContainer')) {
        $.nette.ext('referencedContainer', {
            success: function (payload) {
                if (payload.referencedContainer) {
                    var elRefId = $('#' + payload.referencedContainer.id);
                    elRefId.val(payload.referencedContainer.value);

                    for (var id in payload.snippets) {
                        var snippet = $('#' + id);
                        $.fks.referencedContainer._proto.transformContainer(snippet, elRefId);
                        snippet.closest('form').each(function () {
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

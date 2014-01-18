$(function() {

    $.widget("fks.writeonlyInput", {
// default options
        options: {
        },
        _create: function() {
            var actualInput = $(this.element);
            if (actualInput.data('writeonly-enabled')) {
                return;
            }
            var originalValue = actualInput.data('writeonly-value');
            var originalLabel = actualInput.data('writeonly-label');

            var button = $('<i class="glyphicon glyphicon-remove"/>');
            var actualGroup = $('<div class="right-inner-addon"/>');
            actualInput.replaceWith(actualGroup);
            actualGroup.append(actualInput);
            actualGroup.append(button);

            var overlayInput = actualInput.clone().attr('id', null).attr('name', null).val('').attr('placeholder', originalLabel);
            overlayInput.removeAttr('data-writeonly');
            overlayInput.data('michal', true);
            console.log('clone', overlayInput);
            overlayInput.insertAfter(actualGroup);


            function applyOverlay() {
                actualGroup.hide();
                actualInput.val(originalValue);
                overlayInput.show();
            }

            function removeOverlay() {
                if (actualInput.val() == originalValue) {
                    actualInput.val('');
                }
                overlayInput.hide();
                actualGroup.show();
            }

            overlayInput.focus(function() {
                removeOverlay();
                actualInput.focus();
            });

            button.click(function() {
                applyOverlay();
            });

            if (actualInput.val() == originalValue) {
                applyOverlay();
            } else {
                removeOverlay();
            }
            actualInput.data('writeonly-enabled', true);
        }
    });
    $("input[data-writeonly]").writeonlyInput();

});
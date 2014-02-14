$(document).ready(function() {
    $.widget("fks.enterSubmitForm", {
        _create: function() {
            this.update();
        },
        update: function() {
            var elForm = $(this.element);
            var elSubmit = elForm.find("input[data-submit-on='this']");
            elForm.find("input").not(":data(submit-on-handled)")
                    .data('submit-on-handled', true)
                    .keypress(function(e) {
                if (e.which == 13) {
                    elSubmit.click();
                    return false;
                }
            });
        }});

    // TODO is still needed spinner (with Nette ajax)?
    $.ajaxSetup({
        beforeSend: function() {
            $('#spinner').show();
        },
        complete: function() {
            $('#spinner').hide();
        }
    });
    $.nette.init();
    $("form[data-submit-on='enter']").enterSubmitForm();
    $('a.btn-danger').confirm({
        text: 'O RLY?',
    });
    // TODO form buttons aren't checked

});


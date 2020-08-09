$(document).ready(function () {
    $.widget("fks.enterSubmitForm", {
        _create: function () {
            this.update();
        },
        update: function () {
            const elForm = $(this.element);
            const elSubmit = elForm.find("input[data-submit-on='this']");
            elForm.find("input").not(":data(submit-on-handled)")
                .data('submit-on-handled', true)
                .keypress(function (e) {
                    if (e.which == 13) {
                        elSubmit.click();
                        return false;
                    }
                });
        }
    });

    // TODO is still needed spinner (with Nette ajax)?
    $.ajaxSetup({
        beforeSend: function () {
            $('#spinner').show();
        },
        complete: function () {
            $('#spinner').hide();
        }
    });
    $("form[data-submit-on='enter']").enterSubmitForm();
    document.querySelectorAll('.btn-danger').forEach((el) => {
        el.addEventListener('click', () => {
            if (window.confirm('O RLY?')) {
                el.trigger('click');
            }
        })
    });
    // TODO form buttons aren't checked

});
$(function () {
    $('[data-toggle="popover"]').popover({
        trigger: 'hover',
    })
});


$(document).ready(function() {
    $.ajaxSetup({
        beforeSend: function() {
            $('#spinner').show();
        },
        complete: function() {
            $('#spinner').hide();
        }
    });
    // TODO is still needed spinner above?
    $.nette.init();

});


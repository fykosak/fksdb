$(document).ready(function() {
    $.ajaxSetup({
        beforeSend: function() {
            $('#spinner').show();
        },
        complete: function() {
            $('#spinner').hide();
        }
    });
});


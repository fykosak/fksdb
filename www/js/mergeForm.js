jQuery(function() {
    $('div.mergeSource').each(function() {
        var el = $(this);
        var field = $('#' + el.data('field'));
        el.click(function() {
            field.val(el.find('.value').text());
        });
    });
});

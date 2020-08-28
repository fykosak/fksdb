jQuery(function () {
    document.querySelectorAll('div.mergeSource').forEach((el) => {
        const field = document.getElementById(el.getAttribute('data-field'));
        field.addEventListener('click', () => {
            field.value = el.querySelector('.value').innerText;
        });
    });
   /* $('div.mergeSource').each(function () {
        var el = $(this);
        var field = $('#' + el.data('field'));
        el.click(function () {
            field.val(el.find('.value').text());
        });
    });*/
});

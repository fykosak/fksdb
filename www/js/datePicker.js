/* Czech initialisation for the jQuery UI date picker plugin. */
/* Written by Tomas Muller (tomas@tomas-muller.net). */
jQuery(function($) {
    $.datepicker.regional['cs'] = {
        closeText: 'Zavřít',
        prevText: '&#x3c;Dříve',
        nextText: 'Později&#x3e;',
        currentText: 'Nyní',
        monthNames: ['leden', 'únor', 'březen', 'duben', 'květen', 'červen', 'červenec', 'srpen',
            'září', 'říjen', 'listopad', 'prosinec'],
        monthNamesShort: ['led', 'úno', 'bře', 'dub', 'kvě', 'čer', 'čvc', 'srp', 'zář', 'říj', 'lis', 'pro'],
        dayNames: ['neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota'],
        dayNamesShort: ['ne', 'po', 'út', 'st', 'čt', 'pá', 'so'],
        dayNamesMin: ['ne', 'po', 'út', 'st', 'čt', 'pá', 'so'],
        weekHeader: 'Týd',
        dateFormat: 'dd. mm. yy',
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: ''
    };
    $.datepicker.setDefaults($.datepicker.regional['cs']);
});

/* Enable in forms */
$(document).ready(function() {
    $("input.date").each(function() { // input[type=date] does not work in IE
        var el = $(this);

        var minDate = el.attr("min") || null;
        if (minDate)
            minDate = $.datepicker.parseDate($.datepicker.W3C, minDate);
        var maxDate = el.attr("max") || null;
        if (maxDate)
            maxDate = $.datepicker.parseDate($.datepicker.W3C, maxDate);

        el.datepicker({
            minDate: minDate,
            maxDate: maxDate,
            changeMonth: true,
            changeYear: true
        });

        var value = el.val();
        var hasOriginal = false;
        if (value == '__original') {
            hasOriginal = true;
            value = '';
        }

        var date = (value ? $.datepicker.parseDate($.datepicker.W3C, value) : null);

        if (!hasOriginal) {
            el.val($.datepicker.formatDate(el.datepicker("option", "dateFormat"), date));
        }
    });
});


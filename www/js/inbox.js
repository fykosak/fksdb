$(document).ready(function() {
    var linkFormat = '/public.submit/download/%d';
    function createDownloadLink(taskData, opts) {
        var el = $('<a>');
        el.attr('class', 'inboxField')

        el.attr('href', linkFormat.replace('%d', taskData.submit_id));
        el.text('Download');
        return el;
    }

    function createDatetime(taskData, dataEl, data) {
        var el = $('<input>');
        el.attr('type', 'text');
        el.attr('class', 'inboxField')

        var alt = $('<input>');
        alt.attr('type', 'hidden');
        if (taskData) {
            alt.val(taskData.submitted_on);
        }
        el.change(function() {
            if (!el.val()) {
                taskData.submitted_on = null;
            } else {
                taskData.submitted_on = alt.val();
            }

            dataEl.val(JSON.stringify(data));
        });


        var internalFormat = 'yy-mm-dd'; //TODO parametrize this time, now it works because it's only for post submits
        el.datepicker({
            altField: alt,
            altFormat: internalFormat
        });

        if (taskData.submitted_on) {
            var submitted_on = $.datepicker.parseDate(internalFormat, taskData.submitted_on);
            el.datepicker('setDate', submitted_on);
        }

        return [el, alt];
    }

    $("input.inbox").submitFields({
        createElements: function(taskData, allData, el, containerEl) {
            var substEl = null;
            if (taskData && taskData.source === 'upload') {
                // add download link
                substEl = createDownloadLink(taskData);
            } else {
                // create datetime element
                taskData.source = 'post';
                substEl = createDatetime(taskData, el, allData);
            }

            containerEl.append(substEl);
        }
    });

});


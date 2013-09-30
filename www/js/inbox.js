$(document).ready(function() {
    var linkFormat = '/public.submit/download/%d';
    function createDownloadLink(taskData) {
        var el = $('<a>');
        //el.attr('class', 'inboxField');
        el.attr('href', linkFormat.replace('%d', taskData.submit_id));
        el.text('Download ' + taskData.submit_id);
        return el;
    }

    function createDatetime(taskData, dataEl, data) {
        var el = $('<input>');
        el.attr('type', 'text');
        //el.attr('class', 'inboxField');
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
            var li = $('<li>');
            li.addClass('inbox-field');
            if (!taskData || !taskData.submit_id || taskData.source === 'upload') {
                li.addClass('swappable');
            }
            li.attr('id', 'submit-' + taskData.submit_id);
            li.append(substEl);
            containerEl.append(li);


        },
        initContainer: function(containerEl, ctId) {
            var list = $('<ul>');
            list.attr('class', 'inbox-swappable');
            list.data('contestant', ctId);
            containerEl.append(list);
            return list;
        }
    });
    $('ul.inbox-swappable').swappable({
        items: '.swappable',
        cursorAt: {top: -5},
        update: function(event, ui) {
            var item = ui.item;
            var container = item.parent();
            var ctId = container.data('contestant');

            var order = $(this).swappable('toArray');

            var data = {order: order, ctId: ctId};
            $.post('?do=swapSubmits', data, function() {
                console.log('DONE');
            });
        }
    });


});


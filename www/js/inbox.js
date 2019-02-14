$(document).ready(function () {
    var linkFormat = '/submit/download/%d';

    function createDownloadLink(taskData) {
        var el = $('<a>');
        el.attr('class', 'btn btn-sm btn-secondary');
        el.attr('href', linkFormat.replace('%d', taskData.submit_id));
        el.text('Download ' + taskData.task.label);
        return el;
    }

    function createDatetime(taskData, dataEl, data) {
        var el = $('<input type="text" class="form-control form-control-sm">');
        el.attr('placeholder', 'Úloha ' + taskData.task.label);

        //el.attr('class', 'inboxField');
        var alt = $('<input>');
        alt.attr('type', 'hidden');
        if (taskData) {
            alt.val(taskData.submitted_on);
        }
        el.change(function () {
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

    function createElement(taskData, dataEl, allData) {
        if (taskData && taskData.source === 'upload') {
            // add download link
            return createDownloadLink(taskData);
        } else {
            // create datetime element
            taskData.source = 'post';
            return createDatetime(taskData, dataEl, allData);
        }
    }

    $("input.inbox").submitFields({
        createElements: function (taskData, allData, dataEl, containerEl) {
            var substEl = createElement(taskData, dataEl, allData);

            var li = $('<li>');
            li.addClass('inbox-field col');
            if (!taskData || !taskData.submit_id || taskData.source === 'upload') {
                li.addClass('swappable');
            }
            li.attr('id', 'submit-' + taskData.submit_id);
            li.append(substEl);
            containerEl.append(li);


        },
        initContainer: function (containerEl, ctId) {
            var list = $('<ul>');
            list.attr('class', 'inbox-swappable row');
            list.data('contestant', ctId);
            containerEl.append(list);
            return list;
        }
    });
});

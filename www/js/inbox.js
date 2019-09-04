$(document).ready(function () {
    // const linkFormat = '/fykos32/public/submit/download/%d';
    const linkFormat = '/submit/download/%d'; //TODO
    /**
     *
     * @param {{submit_id:string task:{label:string}}} taskData
     * @returns {jQuery.fn.init|jQuery|HTMLElement}
     */
    function createDownloadLink(taskData) {
        const el = $('<a>');
        el.attr('class', 'btn btn-sm btn-secondary');
        el.attr('href', linkFormat.replace('%d', taskData.submit_id));
        el.text('Download ' + taskData.task.label);
        return el;
    }

    /**
     *
     * @param {{submit_id:string task:{label:string} submitted_on: string|null}} taskData
     * @param dataEl
     * @param data
     * @returns {[jQuery.fn.init|jQuery|HTMLElement, jQuery.fn.init|jQuery|HTMLElement]}
     */
    function createDatetime(taskData, dataEl, data) {
        const el = $('<input type="text" class="form-control form-control-sm">');
        el.attr('placeholder', 'Úloha ' + taskData.task.label);

        //el.attr('class', 'inboxField');
        const alt = $('<input>');
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
        const internalFormat = 'yy-mm-dd'; //TODO parametrize this time, now it works because it's only for post submits
        el.datepicker({
            altField: alt,
            altFormat: internalFormat
        });
        if (taskData.submitted_on) {
            const submitted_on = $.datepicker.parseDate(internalFormat, taskData.submitted_on);
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
            const substEl = createElement(taskData, dataEl, allData);

            const li = $('<li>');
            li.addClass('inbox-field col');
            if (!taskData || !taskData.submit_id || taskData.source === 'upload') {
                li.addClass('swappable');
            }
            li.attr('id', 'submit-' + taskData.submit_id);
            li.append(substEl);
            containerEl.append(li);


        },
        initContainer: function (containerEl, ctId) {
            const list = $('<ul>');
            list.attr('class', 'inbox-swappable row');
            list.data('contestant', ctId);
            containerEl.append(list);
            return list;
        }
    });
});

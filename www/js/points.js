$(document).ready(function() {

    function createElement(taskData, dataEl, allData) {
        if (!taskData || !taskData.submit_id) {
            var el = $('<span>');
            el.text('-'); //TODO
            return el;
        } else {
            var el = $('<input type="number" class="form-control">');
            el.attr('placeholder', 'Úloha ' + taskData.task.label);

            el.change(function() {
                if (!el.val()) {
                    taskData.raw_points = null;
                } else {
                    taskData.raw_points = el.val();
                }

                dataEl.val(JSON.stringify(allData));
            });

            el.val(taskData.raw_points);
            var wrap = $('<span>');
            wrap.append(el);
            return wrap;
        }
    }

    $("input.points").submitFields({
        createElements: function(taskData, allData, dataEl, containerEl) {
            var substEl = createElement(taskData, dataEl, allData);
            substEl.addClass('points-field col');
            containerEl.append(substEl);
        }
    });
});


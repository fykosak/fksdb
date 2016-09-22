
(function ($) {





    $('.fyziklaniResults').each(function () {
        var $outerDiv = $(this);
        var $table = $('<table></table>');

        $outerDiv.append($table);

        var $tHead = $('<thead></thead>');
        $table.append($tHead);

        var $tHeadTr = $('<tr>');
        $tHead.append($tHeadTr);

        var $tBody = $('<tbody>');
        $table.append($tBody);
        var $nav = $('.nav.nav-tabs');

        var $toEnd = $('<div></div>').addClass('to_end');
        var $toStart = $('<div></div>').addClass('to_start');

        var switchTRows = function () {
            var fces = [];
            fces.push(function () {
                  $nav.find('li').removeClass('active');
                    $nav.find('li[data-type="all"]').addClass('active');
                $tBody.find('tr').show();
            });
            var category = $outerDiv.data('category');
            if (category) {
                fces.push(function () {
                    $nav.find('li').removeClass('active');
                    $nav.find('li[data-category="' + category + '"]').addClass('active');
                    $tBody.find('tr').each(function () {
                        if ($(this).data('category') == category) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                });
            }
            var room = $outerDiv.data('room');
            if (room) {
                fces.push(function ( ) {
                    $nav.find('li').removeClass('active');
                    $nav.find('li[data-room="' + room + '"]').addClass('active');
                    $tBody.find('tr').each(function () {
                        if ($(this).data('room') == room) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                });
            }


            var i = 0;
            setInterval(function () {
                fces[i]();
                i = ++i % fces.length;
                console.debug(i);
            }, 15000);

        };


        var refreshData = function () {
            $.nette.ajax({
                data: {
                    type: 'refresh'
                },
                success: function (data) {
                    if (data.times.visible) {
                        $outerDiv.show();
                    } else {
                        $outerDiv.hide();

                    }

                    data.submits.forEach(function (submit) {
                        $table.find('tr[data-team_id="' + submit.team_id + '"]')
                                .find('td[data-task_id="' + submit.task_id + '"]')
                                .attr('data-points', submit.points)
                                .text(submit.points);
                    });
                    $tBody.find('tr').each(function () {
                        var sum = 0;

                        $(this).find('td[data-points]').each(function () {
                            sum += +$(this).data('points');
                        });

                        $(this).find('td.sum').text(sum);
                    });
                    $table.trigger("update");
                    $table.trigger("sorton", [[[1, 1]]]);

                    setTimeout(refreshData, 1000 * 30);
                    //window.setTimeout(refreshData(), 1000*30);
                }
            });
        };


        var createTable = function (data) {
            console.debug(arguments);
            $tHeadTr.append($('<th>').text('Názov týmu'));
            $tHeadTr.append($('<th>').text('Sum'));
            // $tHead.append('td').text('Názov týmu');
            data.tasks.forEach(function (d, i) {
                var $th = $('<th>').text(d.label).attr('data-task_id', d.task_id);
                $tHeadTr.append($th);

            });

            data.teams.forEach(function (d, i) {
                var $tr = $('<tr>').attr({'data-team_id': d.team_id, 'data-category': d.category, 'data-room': d.room});
                $tr.append($('<td>').text(d.name)).append($('<td>').addClass('sum'));
                data.tasks.forEach(function (d, i) {
                    var $td = $('<td>').attr('data-task_id', d.task_id);
                    $tr.append($td);

                });
                $tBody.append($tr);

            });
            $table.tablesorter();
            refreshData();

            switchTRows();



        };




        console.debug(window);


        $.nette.ajax({
            data: {
                type: 'init'
            },
            success: createTable
        });


    });








    return;
}(jQuery));



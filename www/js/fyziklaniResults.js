
(function ($) {


$('#resultsOptToogle').click(function(){
   $('#resultsOpt').slideToggle();
   $(this).toggleClass('active');
});


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

        var $beforeStart = $('.before_start');
        var $afterEnd = $('.after_end');
        var $form = $('form#resultsFrom');

        var toStart = false;
        var toEnd = false;


        var $clock = $('.clock');
        var $imageWP = $('#imgeWP img');
        //$outerDiv.append($clock);



        var switchTRows = function () {
            var fces = {};
            fces.disableFilter = function () {
                $nav.find('li').removeClass('active');
                $nav.find('li[data-type="all"]').addClass('active');
                $tBody.find('tr').show();
            };

            fces.aplyFilterByCategory = function (category) {
                $nav.find('li').removeClass('active');
                $nav.find('li[data-category="' + category + '"]').addClass('active');
                $tBody.find('tr').each(function () {
                    if ($(this).data('category') == category) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            };


            fces.aplyFilterByRoom = function (room) {
                $nav.find('li').removeClass('active');
                $nav.find('li[data-room="' + room + '"]').addClass('active');
                $tBody.find('tr').each(function () {
                    if ($(this).data('room') == room) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            };


            var i = 0;
            var applyNext = function (i) {
                console.debug('filter' + i);
                var t = 5000;
                if ($form.find('#autoSwitch').is(':checked')) {

                    switch (i) {
                        case 0:
                        {
                            fces.disableFilter();
                            break;
                        }
                        case 1:
                        {
                            var room = $form.find('#room').val();
                            console.debug(room);
                            if (room) {
                                fces.aplyFilterByRoom(room);
                            } else {
                                t = 1000;
                            }
                            break;
                        }
                        case 2:
                        {
                            var category = $form.find('#category').val();
                            console.debug(category);
                            if (category) {
                                fces.aplyFilterByCategory(category);
                            } else {
                                t = 1000;
                            }
                            break;
                        }
                    }
                }
                setTimeout(function () {
                    i++;
                    applyNext(i % 3);
                }, t);
            };
            applyNext(i);
//            setInterval(function () {
//                fces[i]();
//                i = ++i % 3;
//                console.debug(i);
//            }, 15000);

        };
        var resultsShow = function () {
            $outerDiv.show();
            $clock.removeClass('big');
            $imageWP.hide();
             $('h1').show();
        };
        var resultsHidde = function () {
            $clock.addClass('big');
            $outerDiv.hide();
            $imageWP.show();
            $('h1').hide();
        };


        var refreshData = function () {
            $.nette.ajax({
                data: {
                    type: 'refresh'
                },
                success: function (data) {
                    if (data.times.visible) {
                        resultsShow();

                    } else {
                        if ($form.find('#orgResults').is(':checked')) {
                            resultsShow();

                        } else {
                            resultsHidde();
                        }
                    }

                    toStart = +data.times.toStart;
                    toEnd = +data.times.toEnd;


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

                }
            });
        };


        var createTable = function (data) {
            console.debug(arguments);
            $tHeadTr.append($('<th>').text('Názov týmu'));
            $tHeadTr.append($('<th>').text('Sum'));
            $tHeadTr.append($('<th>').text('Cat.'));
            // $tHead.append('td').text('Názov týmu');
            data.tasks.forEach(function (d, i) {
                var $th = $('<th>').text(d.label).attr('data-task_id', d.task_id);
                $tHeadTr.append($th);

            });

            data.teams.forEach(function (d, i) {
                var $tr = $('<tr>').attr({'data-team_id': d.team_id, 'data-category': d.category, 'data-room': d.room});
                $tr.append($('<td>').text(d.name)).append($('<td>').addClass('sum')).append($('<td>').text(d.category));

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

        $.nette.ajax({
            data: {
                type: 'init'
            },
            success: createTable
        });
        var switchImage = function () {
            var imgSRC = '/images/fyziklani/';
            if (toStart > 300) {
                imgSRC += 'nezacalo.svg';
            } else if (toStart > 0) {
                imgSRC += 'brzo.svg';
            } else if (toStart > -120) {
                imgSRC += 'start.svg';
            } else if (toEnd > 0) {
                imgSRC += 'fyziklani.svg';

            } else if (toEnd > -240) {
                imgSRC += 'skoncilo.svg';
            } else {
                imgSRC += 'ceka.svg';

            }
            $imageWP.attr('src', imgSRC);
      
        };


        var clockTick = function () {
            var timeStamp = false;
            toStart--;
            toEnd--;
            if (toStart > 0) {
                timeStamp = toStart * 1000;
            } else if (toEnd > 0) {
                timeStamp = toEnd * 1000;
            } else {
                $clock.html('');
                return;
            }
            var d = new Date();
            d.setTime(timeStamp);
            var h = d.getUTCHours();
            var m = d.getUTCMinutes();
            var s = d.getUTCSeconds();
            h = h < 10 ? "0" + h : "" + h;
            m = m < 10 ? "0" + m : "" + m;
            s = s < 10 ? "0" + s : "" + s;
            $clock.html(h + ":" + m + ":" + s);
        };


        setInterval(function () {
            clockTick();
            switchImage();
        }, 1000);



    });









    return;
}(jQuery));



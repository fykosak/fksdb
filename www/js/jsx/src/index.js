"use strict";
var React = require('react');
var ReactDOM = require('react-dom');
var fyziklaniResults_1 = require('../fyziklaniResults');
var taskCode_1 = require('./taskCode');
$('.fyziklani-results').parent('.container').css({ width: 'inherit' });
ReactDOM.render(React.createElement(fyziklaniResults_1.default, null), document.getElementsByClassName('fyziklani-results')[0]);
jQuery('#taskcode').each(function (a, input) {
    var $ = jQuery;
    if (!input.value) {
        var c = document.createElement('div');
        var tasks = $(input).data('tasks');
        var teams = $(input).data('teams');
        $(input).parent().parent().append(c);
        $(input).parent().hide();
        $(c).addClass('col-lg-6');
        ReactDOM.render(React.createElement(taskCode_1.default, {node: input, tasks: tasks, teams: teams}), c);
    }
});

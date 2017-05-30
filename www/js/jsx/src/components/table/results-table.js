"use strict";
var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var React = require('react');
var ReactDOM = require('react-dom');
var team_row_1 = require('./team-row');
var ResultsTable = (function (_super) {
    __extends(ResultsTable, _super);
    function ResultsTable() {
        _super.call(this);
        this.refs = { table: undefined };
    }
    ResultsTable.prototype.componentDidUpdate = function () {
        var $table = $(ReactDOM.findDOMNode(this.refs.table));
        try {
            $table.trigger("update");
            $table.trigger("sorton", [[[1, 1], [3, 1]]]);
        }
        catch (error) {
            console.error(error);
        }
    };
    ResultsTable.prototype.componentDidMount = function () {
        var $table = $(ReactDOM.findDOMNode(this.refs.table));
        $table.tablesorter();
    };
    ResultsTable.prototype.render = function () {
        var _a = this.props, submits = _a.submits, teams = _a.teams, tasks = _a.tasks, displayCategory = _a.displayCategory, displayRoom = _a.displayRoom;
        var submitsForTeams = {};
        console.log(submits);
        for (var index in submits) {
            if (submits.hasOwnProperty(index)) {
                var submit = submits[index];
                var team_id = submit.team_id, tasks_id = submit.tasks_id;
                submitsForTeams[team_id] = submitsForTeams[team_id] || {};
                submitsForTeams[team_id][tasks_id] = submit;
            }
        }
        var rows = teams.map(function (team, teamIndex) {
            var display = ((!displayCategory || displayCategory == team.category) && (!displayRoom || displayRoom == team.room)) ? '' : 'none';
            return (React.createElement(team_row_1.default, {tasks: tasks, submits: submitsForTeams[team.team_id] || {}, team: team, key: teamIndex, styles: { display: display }}));
        });
        var headCools = [];
        tasks.forEach(function (task, taskIndex) {
            headCools.push(React.createElement("th", {key: taskIndex, "data-task_label": task.label}, task.label));
        });
        return (React.createElement("div", null, React.createElement("table", {ref: "table", className: "tablesorter"}, React.createElement("thead", null, React.createElement("tr", null, React.createElement("th", null), React.createElement("th", null, "∑"), React.createElement("th", null, "N"), React.createElement("th", null, "x̄"), headCools)), React.createElement("tbody", null, rows))));
    };
    return ResultsTable;
}(React.Component));
exports.default = ResultsTable;

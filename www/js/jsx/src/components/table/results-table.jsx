"use strict";
var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var __assign = (this && this.__assign) || Object.assign || function(t) {
    for (var s, i = 1, n = arguments.length; i < n; i++) {
        s = arguments[i];
        for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
            t[p] = s[p];
    }
    return t;
};
var React = require("react");
var ReactDOM = require("react-dom");
var react_redux_1 = require("react-redux");
var team_row_1 = require("./team-row");
var ResultsTable = (function (_super) {
    __extends(ResultsTable, _super);
    function ResultsTable() {
        var _this = _super.call(this) || this;
        _this.table = null;
        return _this;
    }
    ResultsTable.prototype.componentDidUpdate = function () {
        var $table = $(ReactDOM.findDOMNode(this.table));
        try {
            $table.trigger("update");
            $table.trigger("sorton", [[[1, 1], [3, 1]]]);
        }
        catch (error) {
            console.error(error);
        }
    };
    ResultsTable.prototype.componentDidMount = function () {
        var $table = $(ReactDOM.findDOMNode(this.table));
        //   $table.tablesorter()
    };
    ResultsTable.prototype.render = function () {
        var _this = this;
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
            return (<team_row_1.default tasks={tasks} submits={submitsForTeams[team.team_id] || {}} team={team} key={teamIndex}/>);
        });
        //  styles={{display: display}}
        var headCools = [];
        tasks.forEach(function (task, taskIndex) {
            headCools.push(<th key={taskIndex} data-task_label={task.label}>{task.label}</th>);
        });
        return (<div>
                <table ref={function (table) { _this.table = table; }} className="tablesorter">
                    <thead>
                    <tr>
                        <th />
                        <th>∑</th>
                        <th>N</th>
                        <th>x̄</th>
                        {headCools}
                    </tr>
                    </thead>
                    <tbody>
                    {rows}
                    </tbody>
                </table>
            </div>);
    };
    return ResultsTable;
}(React.Component));
var mapStateToProps = function (state, ownProps) {
    return __assign({}, ownProps, { teams: state.results.teams, tasks: state.results.tasks, submits: state.results.submits });
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.default = react_redux_1.connect(mapStateToProps, null)(ResultsTable);

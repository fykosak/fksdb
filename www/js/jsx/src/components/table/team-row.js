"use strict";
var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var React = require('react');
var TeamRow = (function (_super) {
    __extends(TeamRow, _super);
    function TeamRow() {
        _super.apply(this, arguments);
    }
    TeamRow.prototype.render = function () {
        var _a = this.props, submits = _a.submits, team = _a.team, tasks = _a.tasks;
        var cools = [];
        var count = 0;
        var sum = 0;
        tasks.forEach(function (task, taskIndex) {
            var task_id = task.task_id;
            var submit = submits[task_id] || null;
            var points = submit ? submit.points : null;
            if (points !== null) {
                count++;
                sum += +points;
            }
            cools.push(React.createElement("td", {"data-points": points, key: taskIndex}, points));
        });
        var average = count > 0 ? Math.round(sum / count * 100) / 100 : '-';
        return (React.createElement("tr", null, React.createElement("td", null, team.name), React.createElement("td", {className: "sum"}, sum), React.createElement("td", null, count), React.createElement("td", null, average), cools));
    };
    ;
    return TeamRow;
}(React.Component));
exports.default = TeamRow;

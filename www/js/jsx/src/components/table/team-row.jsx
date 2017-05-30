"use strict";
var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var React = require("react");
var TeamRow = (function (_super) {
    __extends(TeamRow, _super);
    function TeamRow() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    TeamRow.prototype.render = function () {
        var _a = this.props, submits = _a.submits, team = _a.team, tasks = _a.tasks;
        var cools = [];
        var count = 0;
        var sum = 0;
        tasks.forEach(function (task, taskIndex) {
            // find submit
            var task_id = task.task_id;
            var submit = submits[task_id] || null;
            var points = submit ? submit.points : null;
            if (points !== null) {
                count++;
                sum += +points;
            }
            cools.push(<td data-points={points} key={taskIndex}>{points}</td>);
        });
        var average = count > 0 ? Math.round(sum / count * 100) / 100 : '-';
        return (<tr>
                <td>{team.name}</td>
                <td className="sum">{sum}</td>
                <td>{count}</td>
                <td>{average}</td>
                {cools}
            </tr>);
    };
    ;
    return TeamRow;
}(React.Component));
Object.defineProperty(exports, "__esModule", { value: true });
exports.default = TeamRow;

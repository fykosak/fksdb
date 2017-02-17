var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var TaskCode = (function (_super) {
    __extends(TaskCode, _super);
    function TaskCode() {
        _super.call(this);
        this.state = {};
    }
    TaskCode.prototype.componentDidMount = function () {
        jQuery(ReactDOM.findDOMNode(this.refs.team)).focus();
    };
    TaskCode.prototype.render = function () {
        var _this = this;
        var onInputTask = function (event) {
            var value = event.target.value.toLocaleUpperCase();
            var oldValue = _this.state.task;
            if (value == oldValue) {
                return;
            }
            _this.isValid(_this.getFullCode(null, value));
            if (_this.isValidTask(value)) {
                jQuery(ReactDOM.findDOMNode(_this.refs.control)).focus();
            }
            _this.setState({
                task: value,
            });
        };
        var onInputTeam = function (event) {
            var value = +event.target.value;
            var oldValue = _this.state.team;
            if (value == oldValue) {
                return;
            }
            _this.isValid(_this.getFullCode(value));
            if (_this.isValidTeam(value)) {
                jQuery(ReactDOM.findDOMNode(_this.refs.task)).focus();
            }
            _this.setState({
                team: value,
            });
        };
        var onInputControl = function (event) {
            var value = +event.target.value;
            var oldValue = _this.state.control;
            if (value == oldValue) {
                return;
            }
            _this.isValid(_this.getFullCode(null, null, value));
            _this.setState({
                control: value,
            });
        };
        return (React.createElement("div", {className: 'task-code-container'}, React.createElement("div", {className: 'form-control has-feedback '}, React.createElement("input", {maxLength: "6", ref: "team", className: 'team ' + (this.state.validTeam === false ? 'invalid' : (this.state.validTeam === true ? 'valid' : '')), onKeyUp: onInputTeam, placeholder: "XXXXXX"}), React.createElement("input", {maxLength: "2", className: 'task ' + (this.state.validTask === false ? 'invalid' : (this.state.validTask === true ? 'valid' : '')), ref: "task", placeholder: "XX", onKeyUp: onInputTask}), React.createElement("input", {maxLength: "1", ref: "control", className: 'control ' + (this.state.valid ? 'valid' : 'invalid'), placeholder: "X", onKeyUp: onInputControl}), React.createElement("span", {className: 'glyphicon ' + (this.state.valid ? 'glyphicon-ok' : '') + ' form-control-feedback', "aria-hidden": "true"}))));
    };
    ;
    TaskCode.prototype.getFullCode = function (team, task, control) {
        if (team === void 0) { team = null; }
        if (task === void 0) { task = null; }
        if (control === void 0) { control = null; }
        team = team || (+this.state.team < 1000) ? '0' + +this.state.team : +this.state.team;
        task = task || this.state.task || '';
         control = (control !== null) ? control : (typeof this.state.control == "undefined"?  '':this.state.control);
        return '00' + team + task + control;
    };
    TaskCode.prototype.isValid = function (code) {
        var _a = this.state, validTeam = _a.validTeam, validTask = _a.validTask;
        if (!validTask) {
            this.state.valid = false;
            return;
        }
        if (!validTeam) {
            this.state.valid = false;
            return;
        }
        var subCode = code.split('').map(function (char) {
            return char.toLocaleUpperCase()
                .replace('A', 1)
                .replace('B', 2)
                .replace('C', 3)
                .replace('D', 4)
                .replace('E', 5)
                .replace('F', 6)
                .replace('G', 7)
                .replace('H', 8);
        });
        var c = 3 * (+subCode[0] + +subCode[3] + +subCode[6]) +
            7 * (+subCode[1] + +subCode[4] + +subCode[7]) +
            (+subCode[2] + +subCode[5] + +subCode[8]);
        this.state.valid = c % 10 == 0;
    };
    TaskCode.prototype.isValidTask = function (task) {
        var tasks = this.props.tasks;
        return this.state.validTask = tasks.map(function (task) { return task.label; }).indexOf(task) !== -1;
    };
    TaskCode.prototype.isValidTeam = function (team) {
        var teams = this.props.teams;
        return this.state.validTeam = teams.map(function (team) { return team.team_id; }).indexOf(+team) !== -1;
    };
    TaskCode.prototype.componentDidUpdate = function () {
        this.props.node.value = '';
        if (this.state.valid) {
            this.props.node.value = this.getFullCode();
        }
    };
    return TaskCode;
}(React.Component));
jQuery('#taskcode').each(function (a, input) {
    var $ = jQuery;
    if (!input.value) {
        var c = document.createElement('div');
        var tasks = $(input).data('tasks');
        var teams = $(input).data('teams');
        $(input).parent().parent().append(c);
        $(input).parent().hide();
        $(c).addClass('col-lg-6');
        ReactDOM.render(React.createElement(TaskCode, {node: input, tasks: tasks, teams: teams}), c);
    }
});

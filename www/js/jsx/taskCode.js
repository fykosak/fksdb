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
        var node = this.props.node;
        if (node.value) {
            var value = node.value;
            var team = +value.slice(0, 6);
            var task = value.slice(6, 8);
            var control = value.slice(8, 9);
            this.setState({ defaultValues: { team: team, task: task, control: control } });
        }
    };
    TaskCode.prototype.render = function () {
        var _this = this;
        var teamInputStyles = {
            border: 'none',
            width: '4em',
            'outline-offset': 0,
            'outline-color': 'transparent',
        };
        var taskInputStyles = {
            border: 'none',
            width: '2em',
            'outline-offset': 0,
            'outline-color': 'transparent',
        };
        var controlInputStyles = {
            border: 'none',
            width: '1em',
            'outline-offset': 0,
            'outline-color': 'transparent',
        };
        var containerStyles = {
            'font-size': '120%',
            border: '1px solid #ccc',
            'border-color': '#66afe9',
        };
        var onInputTask = function (event) {
            var value = event.target.value.toLocaleUpperCase();
            _this.setState({
                task: value,
                validTask: _this.isValidTask(value),
                valid: _this.isValid(_this.getFullCode(null, value))
            });
            if (_this.isValidTask(value)) {
                jQuery(ReactDOM.findDOMNode(_this.refs.control)).focus();
            }
        };
        var onInputTeam = function (event) {
            var value = +event.target.value;
            _this.setState({
                team: value,
                validTeam: _this.isValidTeam(value),
                valid: _this.isValid(_this.getFullCode(value))
            });
            if (_this.isValidTeam(event.target.value)) {
                jQuery(ReactDOM.findDOMNode(_this.refs.task)).focus();
            }
        };
        var onInputControl = function (event) {
            var value = +event.target.value;
            _this.setState({
                control: value,
                valid: _this.isValid(_this.getFullCode(null, null, value))
            });
        };
        return (React.createElement("div", {className: 'row col-lg-6 task-code-container'}, React.createElement("div", {className: 'form-control form-group has-feedback ', style: containerStyles}, React.createElement("small", null, "00"), React.createElement("input", {maxLength: "4", className: this.state.validTeam === false ? 'invalid' : (this.state.validTeam === true ? 'valid' : ''), onInput: onInputTeam, style: teamInputStyles, placeholder: "XXXX"}), React.createElement("input", {maxLength: "2", className: this.state.validTask === false ? 'invalid' : (this.state.validTask === true ? 'valid' : ''), ref: "task", style: taskInputStyles, placeholder: "XX", onInput: onInputTask}), React.createElement("input", {maxLength: "1", ref: "control", className: this.state.valid ? 'valid' : 'invalid', style: controlInputStyles, placeholder: "X", onInput: onInputControl}), React.createElement("span", {className: 'glyphicon ' + (this.state.valid ? 'glyphicon-ok' : '') + ' form-control-feedback', "aria-hidden": "true"}))));
    };
    ;
    TaskCode.prototype.getFullCode = function (team, task, control) {
        if (team === void 0) { team = null; }
        if (task === void 0) { task = null; }
        if (control === void 0) { control = null; }
        team = team || (+this.state.team < 1000) ? '0' + +this.state.team : +this.state.team;
        task = task || this.state.task || '';
        control = control || this.state.control || '';
        return '00' + team + task + control;
    };
    TaskCode.prototype.isValid = function (code) {
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
        return c % 10 == 0;
    };
    TaskCode.prototype.isValidTask = function (task) {
        var tasks = this.props.tasks;
        return tasks.map(function (task) { return task.label; }).indexOf(task) !== -1;
    };
    TaskCode.prototype.isValidTeam = function (team) {
        var teams = this.props.teams;
        return teams.map(function (team) { return team.team_id; }).indexOf(+team) !== -1;
    };
    TaskCode.prototype.componentDidUpdate = function () {
        var code = this.getFullCode();
        this.props.node.value = this.getFullCode();
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
        ReactDOM.render(React.createElement(TaskCode, {node: input, tasks: tasks, teams: teams}), c);
    }
});

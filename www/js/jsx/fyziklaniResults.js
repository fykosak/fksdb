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
if (!Object.values) {
    Object.values = function (obj) {
        var vals = [];
        for (var key in obj) {
            if (obj.hasOwnProperty(key)) {
                vals.push(obj[key]);
            }
        }
        return vals;
    };
}
var basePath = $(document.getElementsByClassName('fyziklani-results')[0]).data('basepath');
var filters = [
    { room: null, category: null, name: "ALL" },
    { room: null, category: 'A', name: "A" },
    { room: null, category: 'B', name: "B" },
    { room: null, category: 'C', name: "C" },
    { room: 'M1', category: null, name: "M1" },
    { room: 'M2', category: null, name: "M2" },
    { room: 'M3', category: null, name: "M3" },
    { room: 'M4', category: null, name: "M4" },
    { room: 'M6', category: null, name: "M6" },
    { room: 'F1', category: null, name: "F1" },
    { room: 'F2', category: null, name: "F2" },
    { room: 'S1', category: null, name: "S1" },
    { room: 'S2', category: null, name: "S2" },
    { room: 'S6', category: null, name: "S6" },
];
var store = {};
var Results = (function (_super) {
    __extends(Results, _super);
    function Results() {
        _super.call(this);
        this.state = {
            autoDisplayCategory: null,
            autoDisplayRoom: null,
            autoSwitch: false,
            hardVisible: false,
            displayCategory: null,
            displayRoom: null,
            image: null,
            submits: {},
            times: {},
            tasks: [],
            teams: [],
            visible: false,
            isOrg: false,
            isReady: false,
            configDisplay: false,
            msg: '',
            isRefreshing: false,
        };
    }
    Results.prototype.componentDidMount = function () {
        console.log('mount');
        this.initResults();
        this.applyNextAutoFilter(0);
    };
    Results.prototype.addResults = function (data) {
        var times = data.times, submits = data.submits, isOrg = data.isOrg, lastUpdated = data.lastUpdated, refreshDelay = data.refreshDelay;
        this.setState({
            times: times,
            isOrg: isOrg,
            submits: Object.assign({}, this.state.submits, submits),
            lastUpdated: lastUpdated,
            isRefreshing: (refreshDelay !== null)
        });
    };
    Results.prototype.initResults = function () {
        var _this = this;
        $.nette.ajax({
            success: function (data) {
                var tasks = data.tasks, teams = data.teams;
                _this.addResults(data);
                _this.setState({ tasks: tasks, teams: teams, isReady: true });
                var refreshDelay = data.refreshDelay;
                _this.downloadResults(refreshDelay);
            },
            error: function (e) {
                _this.setState({ msg: e.toString() });
            }
        });
    };
    Results.prototype.downloadResults = function (refreshDelay) {
        var _this = this;
        if (refreshDelay == null) {
            return;
        }
        setTimeout(function () {
            $.nette.ajax({
                data: {
                    lastUpdated: _this.state.lastUpdated,
                },
                success: function (data) {
                    _this.addResults(data);
                    var refreshDelay = data.refreshDelay;
                    _this.downloadResults(refreshDelay);
                },
                error: function (e) {
                    _this.setState({ msg: e.toString() });
                }
            });
        }, refreshDelay);
    };
    Results.prototype.applyNextAutoFilter = function (i) {
        var _this = this;
        $("html, body").scrollTop();
        var t = 20000;
        var _a = this.state, autoSwitch = _a.autoSwitch, autoDisplayCategory = _a.autoDisplayCategory, autoDisplayRoom = _a.autoDisplayRoom;
        if (autoSwitch) {
            switch (i) {
                case 0: {
                    t = 40000;
                    this.setState({ displayCategory: null, displayRoom: null });
                    break;
                }
                case 1: {
                    if (autoDisplayRoom) {
                        this.setState({ displayCategory: autoDisplayCategory });
                    }
                    else {
                        t = 0;
                    }
                    break;
                }
                case 2: {
                    if (autoDisplayCategory) {
                        this.setState({ displayRoom: autoDisplayRoom });
                    }
                    else {
                        t = 0;
                    }
                    break;
                }
            }
            if (t > 1000) {
                $("html, body").delay(t / 3).animate({ scrollTop: $(document).height() }, 2*t / 3);
            }
        }
        setTimeout(function () {
            i++;
            i = i % 3;
            _this.applyNextAutoFilter(i);
        }, t);
    };
    ;
    Results.prototype.render = function () {
        var _this = this;
        var _a = this.state, visible = _a.times.visible, hardVisible = _a.hardVisible, lastUpdated = _a.lastUpdated, isRefreshing = _a.isRefreshing;
        this.state.visible = (visible || hardVisible);
        var filtersButtons = filters.map(function (filter, index) {
            return (React.createElement("li", {key: index, role: "presentation", className: (filter.room == _this.state.displayRoom && filter.category == _this.state.displayCategory) ? 'active' : ''}, React.createElement("a", {onClick: function () {
                _this.setState({ displayCategory: filter.category,
                    displayRoom: filter.room });
            }}, filter.name)));
        });
        var msg = [];
        if (hardVisible && !visible) {
            msg.push(React.createElement("div", {key: msg.length, className: "alert alert-warning"}, "Výsledková listina je určená pouze pro organizátory!!!"));
        }
        if (!this.state.isOrg) {
            msg.push(React.createElement("div", {key: msg.length, className: "alert alert-info"}, "Na výsledkovou listinu se díváte jako \"Public\""));
        }
        var button = (React.createElement("button", {className: 'btn btn-default ' + (this.state.configDisplay ? 'active' : ''), onClick: function () { return _this.setState({ configDisplay: !_this.state.configDisplay }); }}, React.createElement("span", {className: "glyphicon glyphicon-cog", type: "button"}), "Nastavení"));
        if (!this.state.isReady) {
            return (React.createElement("div", {className: "load", style: { textAlign: 'center', }}, React.createElement("img", {src: basePath + '/images/gears.svg', style: { width: '50%' }})));
        }
        return (React.createElement("div", null, React.createElement(BackLink, null), React.createElement("div", {className: "last-update-info"}, "Naposledny updatováno:", React.createElement("span", {className: isRefreshing ? 'text-success' : 'text-muted'}, lastUpdated)), msg, React.createElement("ul", {className: "nav nav-tabs", style: { display: (this.state.visible) ? '' : 'none' }}, filtersButtons), React.createElement(Images, __assign({}, this.state, this.props)), React.createElement(ResultsTable, __assign({}, this.state, this.props)), React.createElement(Timer, __assign({}, this.state, this.props)), button, React.createElement("div", {style: { display: this.state.configDisplay ? 'block' : 'none' }}, React.createElement("div", {className: "form-group"}, React.createElement("label", {className: "sr-only"}, React.createElement("span", null, "Místnost")), React.createElement("select", {className: "form-control", onChange: function (event) {
            _this.setState({ autoDisplayRoom: event.target.value });
        }}, React.createElement("option", null, "--vyberte místnost--"), filters
            .filter(function (filter) { return filter.room != null; })
            .map(function (filter, index) {
            return (React.createElement("option", {key: index, value: filter.room}, filter.name));
        }))), React.createElement("div", {className: "form-group"}, React.createElement("label", {className: "sr-only"}, React.createElement("span", null, "Kategorie")), React.createElement("select", {className: "form-control", onChange: function (event) {
            _this.setState({ autoDisplayCategory: event.target.value });
        }}, React.createElement("option", null, "--vyberte kategorii--"), React.createElement("option", {value: "A"}, "A"), React.createElement("option", {value: "B"}, "B"), React.createElement("option", {value: "C"}, "C"))), React.createElement("div", {className: "form-group"}, React.createElement("div", {className: "checkbox"}, React.createElement("label", null, React.createElement("input", {type: "checkbox", value: "1", onChange: function (event) {
            _this.setState({ autoSwitch: event.target.checked });
        }}), React.createElement("span", null, "Automatické přepínání místností a kategorií")))), React.createElement("div", {className: "form-group has-error"}, React.createElement("div", {className: "checkbox"}, React.createElement("label", null, React.createElement("input", {type: "checkbox", disabled: !this.state.isOrg, value: "1", onChange: function (event) {
            _this.setState({ hardVisible: event.target.checked });
        }}), "Neveřejné výsledkovky, ", React.createElement("span", {className: "text-danger"}, "tuto funkci nezapínejte pokud jsou výsledkovky promítané!!!")))))));
    };
    return Results;
}(React.Component));
var BackLink = (function (_super) {
    __extends(BackLink, _super);
    function BackLink() {
        _super.apply(this, arguments);
    }
    BackLink.prototype.render = function () {
        return (React.createElement("button", {className: "btn btn-default", onClick: function () { return window.history.back(); }}, React.createElement("i", {className: "glyphicon glyphicon-chevron-left"})));
    };
    return BackLink;
}(React.Component));
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
        var rows = [];
        var _a = this.props, submits = _a.submits, teams = _a.teams, tasks = _a.tasks, displayCategory = _a.displayCategory, displayRoom = _a.displayRoom;
        teams.forEach(function (team, teamIndex) {
            var cools = [];
            tasks.forEach(function (task, taskIndex) {
                var submit = Object.values(submits).filter(function (submit) {
                    return submit.task_id == task.task_id && submit.team_id == team.team_id;
                })[0];
                var points = submit ? submit.points : '';
                cools.push(React.createElement("td", {"data-points": points, key: taskIndex}, (points !== null) ? points : ''));
            });
            var styles = {
                display: ((!displayCategory || displayCategory == team.category) && (!displayRoom || displayRoom == team.room)) ? '' : 'none',
            };
            var count = 0;
            var sum = Object.values(submits).filter(function (submit) {
                return submit.team_id == team.team_id;
            }).reduce(function (val, submit) {
                var points = submit.points;
                if (points !== null) {
                    count++;
                    return val + +points;
                }
                return val;
            }, 0);
            var average = count > 0 ? Math.round(sum / count * 100) / 100 : '-';
            rows.push(React.createElement("tr", {key: teamIndex, style: styles}, React.createElement("td", null, team.name), React.createElement("td", {className: "sum"}, sum), React.createElement("td", null, count), React.createElement("td", null, average), cools));
        });
        var headCools = [];
        tasks.forEach(function (task, taskIndex) {
            headCools.push(React.createElement("th", {key: taskIndex, "data-task_label": task.label}, task.label));
        });
        return (React.createElement("div", {style: { display: (this.props.visible ? 'block' : 'none') }}, React.createElement("table", {ref: "table", className: "tablesorter"}, React.createElement("thead", null, React.createElement("tr", null, React.createElement("th", null), React.createElement("th", null, "∑"), React.createElement("th", null, "N"), React.createElement("th", null, "x̄"), headCools)), React.createElement("tbody", null, rows))));
    };
    return ResultsTable;
}(React.Component));
var Timer = (function (_super) {
    __extends(Timer, _super);
    function Timer() {
        _super.call(this);
        this.state = { toStart: 0, toEnd: 0 };
    }
    Timer.prototype.componentDidMount = function () {
        var _this = this;
        setInterval(function () {
            _this.state.toStart = _this.state.toStart - 1;
            _this.state.toEnd = _this.state.toEnd - 1;
            _this.forceUpdate();
        }, 1000);
    };
    Timer.prototype.componentWillReceiveProps = function () {
        var _a = this.props.times, toStart = _a.toStart, toEnd = _a.toEnd;
        this.state.toStart = toStart;
        this.state.toEnd = toEnd;
    };
    Timer.prototype.render = function () {
        var _a = this.state, toStart = _a.toStart, toEnd = _a.toEnd;
        var timeStamp = 0;
        if (toStart > 0) {
            timeStamp = toStart * 1000;
        }
        else if (toEnd > 0) {
            timeStamp = toEnd * 1000;
        }
        else {
            return (React.createElement("div", null));
        }
        var date = new Date(timeStamp);
        var h = date.getUTCHours();
        var m = date.getUTCMinutes();
        var s = date.getUTCSeconds();
        return (React.createElement("div", {className: 'clock ' + (this.props.visible ? '' : 'big')}, (h < 10 ? "0" + h : "" + h)
            + ":" +
            (m < 10 ? "0" + m : "" + m)
            + ":" +
            (s < 10 ? "0" + s : "" + s)));
    };
    return Timer;
}(React.Component));
var Images = (function (_super) {
    __extends(Images, _super);
    function Images() {
        _super.call(this);
        this.state = { toStart: 0, toEnd: 0 };
    }
    Images.prototype.componentWillReceiveProps = function () {
        var _a = this.props.times, toStart = _a.toStart, toEnd = _a.toEnd;
        this.state.toStart = toStart;
        this.state.toEnd = toEnd;
    };
    Images.prototype.render = function () {
        var _a = this.state, toStart = _a.toStart, toEnd = _a.toEnd;
        if (toStart == 0 || toEnd == 0) {
            return (React.createElement("div", null));
        }
        var imgSRC = basePath + '/images/fyziklani/';
        if (toStart > 300) {
            imgSRC += 'nezacalo.svg';
        }
        else if (toStart > 0) {
            imgSRC += 'brzo.svg';
        }
        else if (toStart > -120) {
            imgSRC += 'start.svg';
        }
        else if (toEnd > 0) {
            imgSRC += 'fyziklani.svg';
        }
        else if (toEnd > -240) {
            imgSRC += 'skoncilo.svg';
        }
        else {
            imgSRC += 'ceka.svg';
        }
        return (React.createElement("div", {style: { display: this.props.visible ? 'none' : '' }, id: 'imageWP', "data-basepath": basePath}, React.createElement("img", {src: imgSRC, alt: ""})));
    };
    return Images;
}(React.Component));
$('.fyziklani-results').parent('.container').css({ width: 'inherit' });
ReactDOM.render(React.createElement(Results, null), document.getElementsByClassName('fyziklani-results')[0]);

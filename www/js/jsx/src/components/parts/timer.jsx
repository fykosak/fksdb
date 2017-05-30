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
var react_redux_1 = require("react-redux");
var Timer = (function (_super) {
    __extends(Timer, _super);
    function Timer() {
        var _this = _super.call(this) || this;
        _this.state = { lastUpdate: new Date() };
        return _this;
    }
    Timer.prototype.componentDidMount = function () {
        var _this = this;
        this.setState({ lastUpdate: new Date() });
        window.setInterval(function () {
            _this.forceUpdate();
        }, 1000);
    };
    Timer.prototype.componentWillReceiveProps = function () {
        this.setState({ lastUpdate: new Date() });
    };
    Timer.prototype.render = function () {
        var lastUpdate = this.state.lastUpdate;
        var _a = this.props, toStart = _a.toStart, toEnd = _a.toEnd, visible = _a.visible;
        var delta = (new Date).getTime() - lastUpdate.getTime();
        toStart = (toStart * 1000) - delta;
        toEnd -= (toEnd * 1000) - delta;
        var timeStamp = 0;
        if (toStart > 0) {
            timeStamp = toStart;
        }
        else if (toEnd > 0) {
            timeStamp = toEnd;
        }
        else {
            return (<div />);
        }
        var date = new Date(timeStamp);
        var h = date.getUTCHours();
        var m = date.getUTCMinutes();
        var s = date.getUTCSeconds();
        return (<div className={'clock ' + (visible ? '' : 'big')}>
                {(h < 10 ? "0" + h : "" + h)
            + ":" +
            (m < 10 ? "0" + m : "" + m)
            + ":" +
            (s < 10 ? "0" + s : "" + s)}
            </div>);
    };
    return Timer;
}(React.Component));
var mapStateToProps = function (state, ownProps) {
    return __assign({}, ownProps, state.timer);
};
var mapDispatchToProps = function (dispatch, ownProps) {
    return __assign({}, ownProps);
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.default = react_redux_1.connect(mapStateToProps, mapDispatchToProps)(Timer);

"use strict";
var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var React = require('react');
var Timer = (function (_super) {
    __extends(Timer, _super);
    function Timer() {
        _super.call(this);
        this.state = { lastUpdate: new Date() };
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
            return (React.createElement("div", null));
        }
        var date = new Date(timeStamp);
        var h = date.getUTCHours();
        var m = date.getUTCMinutes();
        var s = date.getUTCSeconds();
        return (React.createElement("div", {className: 'clock ' + (visible ? '' : 'big')}, (h < 10 ? "0" + h : "" + h)
            + ":" +
            (m < 10 ? "0" + m : "" + m)
            + ":" +
            (s < 10 ? "0" + s : "" + s)));
    };
    return Timer;
}(React.Component));
exports.default = Timer;

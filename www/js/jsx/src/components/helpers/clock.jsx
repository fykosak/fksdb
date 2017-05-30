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
var tick_1 = require("../../actions/tick");
var Clock = (function (_super) {
    __extends(Clock, _super);
    function Clock() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    Clock.prototype.componentDidMount = function () {
        var onTick = this.props.onTick;
        setInterval(onTick, 1000);
    };
    Clock.prototype.render = function () {
        return (<div />);
    };
    ;
    return Clock;
}(React.Component));
var mapDispatchToProps = function (dispatch, ownProps) {
    return __assign({}, ownProps, { onTick: function () { return dispatch(tick_1.tick()); } });
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.default = react_redux_1.connect(null, mapDispatchToProps)(Clock);

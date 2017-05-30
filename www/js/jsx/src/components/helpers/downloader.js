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
var FyziklaniDashboard = (function (_super) {
    __extends(FyziklaniDashboard, _super);
    function FyziklaniDashboard() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    FyziklaniDashboard.prototype.render = function () {
        var page = this.props.page;
        switch (page) {
            default:
                return />);;
        }
    };
    ;
    return FyziklaniDashboard;
}(React.Component));
var mapStateToProps = function (state, ownProps) {
    return __assign({}, ownProps, { page: state.options.page });
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.default = react_redux_1.connect(mapStateToProps, null)(FyziklaniDashboard);

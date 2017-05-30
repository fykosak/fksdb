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
var fetch_1 = require("../../helpers/fetch");
var Downloader = (function (_super) {
    __extends(Downloader, _super);
    function Downloader() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    Downloader.prototype.componentDidMount = function () {
        var onFetch = this.props.onFetch;
        onFetch(null);
    };
    Downloader.prototype.componentWillReceiveProps = function (nextProps) {
        if (this.props.lastUpdated !== nextProps.lastUpdated) {
            var onWaitForFetch = nextProps.onWaitForFetch, refreshDelay = nextProps.refreshDelay, lastUpdated = nextProps.lastUpdated;
            onWaitForFetch(lastUpdated, refreshDelay);
        }
    };
    Downloader.prototype.render = function () {
        var lastUpdated = this.props.lastUpdated;
        var isRefreshing = true;
        return className = "last-update-info" > Naposledny;
        updatováno: className;
        {
            isRefreshing ? 'text-success' : 'text-muted';
        }
         >
            { lastUpdated: lastUpdated }
            < /span>
            < /div>;
        ;
    };
    ;
    return Downloader;
}(React.Component));
var mapStateToProps = function (state, ownProps) {
    return __assign({}, ownProps, { lastUpdated: state.downloader.lastUpdated, refreshDelay: state.downloader.refreshDelay });
};
var mapDispatchToProps = function (dispatch, ownProps) {
    return __assign({}, ownProps, { onFetch: function () { return fetch_1.fetchResults(dispatch, null); }, onWaitForFetch: function (lastUpdated, delay) { return fetch_1.waitForFetch(dispatch, delay, lastUpdated); } });
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.default = react_redux_1.connect(mapStateToProps, mapDispatchToProps)(Downloader);

"use strict";
var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var React = require("react");
var BackLink = (function (_super) {
    __extends(BackLink, _super);
    function BackLink() {
        return _super !== null && _super.apply(this, arguments) || this;
    }
    BackLink.prototype.render = function () {
        return (<button className="btn btn-default" onClick={function () { return window.history.back(); }}>
                <i className="glyphicon glyphicon-chevron-left"/>
            </button>);
    };
    return BackLink;
}(React.Component));
Object.defineProperty(exports, "__esModule", { value: true });
exports.default = BackLink;

"use strict";
var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var React = require('react');
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
exports.default = BackLink;

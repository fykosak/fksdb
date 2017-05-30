"use strict";
var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var React = require('react');
var filters_1 = require('./filters');
var Options = (function (_super) {
    __extends(Options, _super);
    function Options() {
        this.state = { isDisplayed: false };
    }
    Options.prototype.render = function () {
        var _this = this;
        var _a = this.props, onCategoryChange = _a.onCategoryChange, onRoomChange = _a.onRoomChange, onAutoSwitchChange = _a.onAutoSwitchChange, onHardDisplayChange = _a.onHardDisplayChange, isOrg = _a.isOrg;
        var isDisplayed = this.state.isDisplayed;
        return (React.createElement("div", null, React.createElement("button", {className: 'btn btn-default ' + (isDisplayed ? 'active' : ''), onClick: function () { return _this.setState({ isDisplayed: !isDisplayed }); }}, React.createElement("span", {className: "glyphicon glyphicon-cog", type: "button"}), "Nastavení"), React.createElement("div", {style: { display: isDisplayed ? 'block' : 'none' }}, React.createElement("div", {className: "form-group"}, React.createElement("label", {className: "sr-only"}, React.createElement("span", null, "Místnost")), React.createElement("select", {className: "form-control", onChange: onRoomChange}, React.createElement("option", null, "--vyberte místnost--"), filters_1.filters
            .filter(function (filter) { return filter.room != null; })
            .map(function (filter, index) {
            return (React.createElement("option", {key: index, value: filter.room}, filter.name));
        }))), React.createElement("div", {className: "form-group"}, React.createElement("label", {className: "sr-only"}, React.createElement("span", null, "Kategorie")), React.createElement("select", {className: "form-control", onChange: onCategoryChange}, React.createElement("option", null, "--vyberte kategorii--"), React.createElement("option", {value: "A"}, "A"), React.createElement("option", {value: "B"}, "B"), React.createElement("option", {value: "C"}, "C"))), React.createElement("div", {className: "form-group"}, React.createElement("div", {className: "checkbox"}, React.createElement("label", null, React.createElement("input", {type: "checkbox", value: "1", onChange: onAutoSwitchChange}), React.createElement("span", null, "Automatické přepínání místností a kategorií")))), React.createElement("div", {className: "form-group has-error"}, React.createElement("div", {className: "checkbox"}, React.createElement("label", null, React.createElement("input", {type: "checkbox", disabled: !isOrg, value: "1", onChange: onHardDisplayChange}), "Neveřejné výsledkovky, ", React.createElement("span", {className: "text-danger"}, "tuto funkci nezapínejte pokud jsou výsledkovky promítané!!!")))))));
    };
    return Options;
}(React.Component));
exports.default = Options;

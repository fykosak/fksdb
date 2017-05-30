"use strict";
var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var React = require("react");
var Images = (function (_super) {
    __extends(Images, _super);
    function Images() {
        var _this = _super.call(this) || this;
        _this.state = { toStart: 0, toEnd: 0 };
        return _this;
    }
    Images.prototype.componentWillReceiveProps = function () {
        var _a = this.props.times, toStart = _a.toStart, toEnd = _a.toEnd;
        this.state.toStart = toStart;
        this.state.toEnd = toEnd;
    };
    Images.prototype.render = function () {
        var _a = this.state, toStart = _a.toStart, toEnd = _a.toEnd;
        var basePath = this.props.basePath;
        if (toStart == 0 || toEnd == 0) {
            return (<div />);
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
        return (<div id='imageWP' data-basepath={basePath}>
                <img src={imgSRC} alt=""/>
            </div>);
    };
    return Images;
}(React.Component));
Object.defineProperty(exports, "__esModule", { value: true });
exports.default = Images;

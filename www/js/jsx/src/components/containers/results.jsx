"use strict";
var __extends = (this && this.__extends) || function (d, b) {
    for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p];
    function __() { this.constructor = d; }
    d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
};
var React = require("react");
var react_redux_1 = require("react-redux");
var back_link_1 = require("../back-link");
var images_1 = require("../images");
var results_table_1 = require("../results-table");
var timer_1 = require("../timer");
var Results = (function (_super) {
    __extends(Results, _super);
    function Results() {
        var _this = _super.call(this) || this;
        _this.state = {
            autoDisplayCategory: null,
            autoDisplayRoom: null,
            autoSwitch: false,
            hardVisible: false,
            displayCategory: null,
            displayRoom: null,
            image: null,
            times: {},
            visible: false,
            isOrg: false,
            isReady: false,
            configDisplay: false,
            msg: '',
            isRefreshing: false,
        };
        return _this;
    }
    Results.prototype.componentDidMount = function () {
        this.applyNextAutoFilter(0);
    };
    Results.prototype.applyNextAutoFilter = function (i) {
        var _this = this;
        $("html, body").scrollTop();
        var t = 15000;
        var _a = this.state, autoSwitch = _a.autoSwitch, autoDisplayCategory = _a.autoDisplayCategory, autoDisplayRoom = _a.autoDisplayRoom;
        if (autoSwitch) {
            switch (i) {
                case 0: {
                    t = 30000;
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
                $("html, body").delay(t / 3).animate({ scrollTop: $(document).height() }, t / 3);
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
        var _a = this.state, visible = _a.times.visible, hardVisible = _a.hardVisible;
        this.state.visible = (visible || hardVisible);
        var filtersButtons = [];
        /*filters.map((filter, index) => {
         return (
         <li key={index} role="presentation"
         className={(filter.room==this.state.displayRoom&&filter.category==this.state.displayCategory)?'active':''}>
         <a onClick={()=>{
         this.setState({displayCategory:filter.category,
         displayRoom:filter.room});
         }}>
         {filter.name}
         </a>
         </li>
         )
         });*/
        var basePath = '';
        var msg = [];
        if (hardVisible && !visible) {
            msg.push(<div key={msg.length} className="alert alert-warning">
                Výsledková listina je určená pouze pro organizátory!!!</div>);
        }
        if (!this.state.isOrg) {
            msg.push(<div key={msg.length} className="alert alert-info">
                    Na výsledkovou listinu se díváte jako "Public"</div>);
        }
        if (!this.state.isReady) {
            return (<div className="load" style={{ textAlign: 'center', }}>
                    <img src={basePath + '/images/gears.svg'} style={{ width: '50%' }}/>
                </div>);
        }
        return (<div>
                <back_link_1.default />

                {msg}

                <ul className="nav nav-tabs" style={{ display: (this.state.visible) ? '' : 'none' }}>
                    {filtersButtons}
                </ul>

                <images_1.default {...this.state} {...this.props}/>
                <results_table_1.default {...this.state} {...this.props}/>
                <timer_1.default />
            </div>);
    };
    return Results;
}(React.Component));
Object.defineProperty(exports, "__esModule", { value: true });
exports.default = react_redux_1.connect(null, null)(Results);

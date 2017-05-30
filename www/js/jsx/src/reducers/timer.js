"use strict";
var __assign = (this && this.__assign) || Object.assign || function(t) {
    for (var s, i = 1, n = arguments.length; i < n; i++) {
        s = arguments[i];
        for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
            t[p] = s[p];
    }
    return t;
};
var times_1 = require("../actions/times");
var updateTimes = function (state, action) {
    var times = action.times;
    return __assign({}, state, times);
};
exports.timer = function (state, action) {
    if (state === void 0) { state = {}; }
    switch (action.type) {
        case times_1.UPDATE_TIMES:
            return updateTimes(state, action);
        default:
            return state;
    }
};

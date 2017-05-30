"use strict";
var __assign = (this && this.__assign) || Object.assign || function(t) {
    for (var s, i = 1, n = arguments.length; i < n; i++) {
        s = arguments[i];
        for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
            t[p] = s[p];
    }
    return t;
};
var downloader_1 = require("../actions/downloader");
var updateOptions = function (state, action) {
    var lastUpdated = action.lastUpdated, refreshDelay = action.refreshDelay;
    return __assign({}, state, { lastUpdated: lastUpdated,
        refreshDelay: refreshDelay });
};
exports.downloader = function (state, action) {
    if (state === void 0) { state = {}; }
    switch (action.type) {
        case downloader_1.UPDATE_DOWNLOADER_OPTIONS:
            return updateOptions(state, action);
        default:
            return state;
    }
};

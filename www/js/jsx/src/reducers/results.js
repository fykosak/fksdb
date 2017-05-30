"use strict";
var __assign = (this && this.__assign) || Object.assign || function(t) {
    for (var s, i = 1, n = arguments.length; i < n; i++) {
        s = arguments[i];
        for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
            t[p] = s[p];
    }
    return t;
};
var addSubmits = function (state, action) {
    var submits = action.submits;
    return __assign({}, state, { submits: __assign({}, state.submits, submits) });
};
exports.results = function (state, action) {
    switch () {
    }
    return {};
};

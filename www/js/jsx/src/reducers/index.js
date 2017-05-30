"use strict";
var timer_1 = require("./timer");
var results_1 = require("./results");
var options_1 = require("./options");
var downloader_1 = require("./downloader");
var redux_1 = require("redux");
exports.app = redux_1.combineReducers({
    timer: timer_1.timer,
    results: results_1.results,
    options: options_1.options,
    downloader: downloader_1.downloader,
});

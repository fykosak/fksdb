"use strict";
//import * as $ from 'jquery';
var times_1 = require("../actions/times");
var downloader_1 = require("../actions/downloader");
var results_1 = require("../actions/results");
exports.fetchResults = function (dispatch, lastUpdated) {
    if (lastUpdated === void 0) { lastUpdated = null; }
    var promise = new Promise(function (resolve, reject) {
        var data = {};
        if (lastUpdated) {
            data.lastUpdated = lastUpdated;
        }
        $.nette.ajax({
            data: data,
            success: function (data) {
                resolve(data);
            },
            error: function (e) {
                throw e;
            }
        });
    });
    promise.then(function (data) {
        var times = data.times, submits = data.submits, isOrg = data.isOrg, lastUpdated = data.lastUpdated, refreshDelay = data.refreshDelay, tasks = data.tasks, teams = data.teams;
        dispatch(times_1.updateTimes(times));
        dispatch(downloader_1.updateDownloaderOptions(lastUpdated, refreshDelay));
        dispatch(results_1.addSubmits(submits));
        //dispatch(updateOptions(isOrg));
        if (tasks) {
            dispatch(results_1.setTasks(tasks));
        }
        if (teams) {
            dispatch(results_1.setTeams(teams));
        }
    });
};
exports.waitForFetch = function (dispatch, delay, lastUpdated) {
    if (lastUpdated === void 0) { lastUpdated = null; }
    return setTimeout(function () {
        exports.fetchResults(dispatch, lastUpdated);
    }, delay);
};

"use strict";
exports.ADD_SUBMITS = 'ADD_SUBMITS';
exports.SET_TEAMS = 'SET_TEAMS';
exports.SET_TASKS = 'SET_TASKS';
exports.addSubmits = function (submits) {
    return {
        type: exports.ADD_SUBMITS,
        submits: submits,
    };
};
exports.setTeams = function (teams) {
    return {
        type: exports.SET_TEAMS,
        teams: teams,
    };
};
exports.setTasks = function (tasks) {
    return {
        type: exports.SET_TASKS,
        tasks: tasks,
    };
};

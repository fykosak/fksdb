"use strict";
exports.UPDATE_TIMES = 'UPDATE_TIMES';
exports.updateTimes = function (times) {
    return {
        type: exports.UPDATE_TIMES,
        times: times,
    };
};

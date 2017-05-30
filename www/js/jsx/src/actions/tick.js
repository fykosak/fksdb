"use strict";
exports.TICK = 'TICK';
exports.tick = function () {
    return {
        type: exports.TICK,
    };
};

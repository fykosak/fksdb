"use strict";
exports.UPDATE_DOWNLOADER_OPTIONS = 'UPDATE_DOWNLOADER_OPTIONS';
exports.updateDownloaderOptions = function (lastUpdated, refreshDelay) {
    return {
        type: exports.UPDATE_DOWNLOADER_OPTIONS,
        lastUpdated: lastUpdated,
        refreshDelay: refreshDelay,
    };
};

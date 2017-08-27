export const UPDATE_DOWNLOADER_OPTIONS = 'UPDATE_DOWNLOADER_OPTIONS';

export const updateDownloaderOptions = (lastUpdated: string, refreshDelay: number) => {
    return {
        lastUpdated,
        type: UPDATE_DOWNLOADER_OPTIONS,
        refreshDelay,
    };
};

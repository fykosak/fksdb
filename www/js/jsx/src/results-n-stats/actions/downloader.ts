export const UPDATE_DOWNLOADER_OPTIONS = 'UPDATE_DOWNLOADER_OPTIONS';

export const updateDownloaderOptions = (lastUpdated: string, refreshDelay: number) => {
    return {
        lastUpdated,
        refreshDelay,
        type: UPDATE_DOWNLOADER_OPTIONS,
    };
};

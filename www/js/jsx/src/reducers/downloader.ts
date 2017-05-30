import {UPDATE_DOWNLOADER_OPTIONS} from '../actions/downloader';
const updateOptions = (state, action) => {
    const {lastUpdated, refreshDelay} = action;
    return {
        ...state,
        lastUpdated,
        refreshDelay,
    }

};

export const downloader = (state = {}, action) => {
    switch (action.type) {
        case UPDATE_DOWNLOADER_OPTIONS:
            return updateOptions(state, action);
        default:
            return state;
    }
};

import { UPDATE_DOWNLOADER_OPTIONS } from '../actions/downloader';

export interface IState {
    lastUpdated?: string;
    refreshDelay?: number;
}

const updateOptions = (state: IState, action) => {
    const {lastUpdated, refreshDelay} = action;
    return {
        ...state,
        lastUpdated,
        refreshDelay,
    };
};

export const downloader = (state: IState = {}, action) => {
    switch (action.type) {
        case UPDATE_DOWNLOADER_OPTIONS:
            return updateOptions(state, action);
        default:
            return state;
    }
};

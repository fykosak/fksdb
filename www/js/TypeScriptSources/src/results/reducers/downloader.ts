import {
    FETCH_FAIL,
    NEW_DATA_ARRIVED,
} from '../actions/downloader';

export interface IState {
    lastUpdated?: string;
    refreshDelay?: number;
    isRefreshing?: boolean;
    error?: any;
}

const updateOptions = (state: IState, action): IState => {
    const {lastUpdated, refreshDelay} = action;
    return {
        ...state,
        isRefreshing: true,
        lastUpdated,
        refreshDelay,
    };
};
const fetchFail = (state: IState, action): IState => {
    return {
        ...state,
        error: action.error,
        isRefreshing: false,
    };
};

export const downloader = (state: IState = {lastUpdated: null}, action) => {
    switch (action.type) {
        case NEW_DATA_ARRIVED:
            return updateOptions(state, action);
        case FETCH_FAIL:
            return fetchFail(state, action);
        default:
            return state;
    }
};

import {
    ACTION_SUBMIT_FAIL,
    ACTION_SUBMIT_SUCCESS,
} from '@fetchApi/actions/submit';
import { ActionSubmitSuccess } from '@fetchApi/middleware/interfaces';
import { ResponseData } from '../actions/';

export interface State {
    lastUpdated?: string;
    refreshDelay?: number;
    isRefreshing?: boolean;
}

const updateOptions = (state: State, action: ActionSubmitSuccess<ResponseData>): State => {
    const {lastUpdated, refreshDelay} = action.data.responseData;
    return {
        ...state,
        isRefreshing: true,
        lastUpdated,
        refreshDelay,
    };
};
const fetchFail = (state: State): State => {
    return {
        ...state,
        isRefreshing: false,
    };
};

export const fyziklaniDownloader = (state: State = {lastUpdated: null}, action) => {
    switch (action.type) {
        case ACTION_SUBMIT_SUCCESS:
            return updateOptions(state, action);
        case ACTION_SUBMIT_FAIL:
            return fetchFail(state);
        default:
            return state;
    }
};

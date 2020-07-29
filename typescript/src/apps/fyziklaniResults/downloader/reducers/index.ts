import {
    ACTION_FETCH_FAIL,
    ACTION_FETCH_SUCCESS,
    ActionFetchSuccess,
} from '@fetchApi/actions/fetch';
import { Response2 } from '@fetchApi/middleware/interfaces';
import { ResponseData } from '../interfaces';

export interface State {
    lastUpdated?: string;
    refreshDelay?: number;
    isRefreshing?: boolean;
}

const fetchSuccess = (state: State, action: ActionFetchSuccess<Response2<ResponseData>>): State => {
    const {lastUpdated, refreshDelay} = action.data.data;
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

export const fyziklaniDownloader = (state: State = {lastUpdated: null}, action): State => {
    switch (action.type) {
        case ACTION_FETCH_SUCCESS:
            return fetchSuccess(state, action);
        case ACTION_FETCH_FAIL:
            return fetchFail(state);
        default:
            return state;
    }
};

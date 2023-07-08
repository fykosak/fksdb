import { ResponseData } from 'FKSDB/Components/Game/ResultsAndStatistics/Helpers/Downloader/downloader';
import {
    ACTION_FETCH_FAIL,
    ACTION_FETCH_SUCCESS,
    ActionFetchSuccess,
} from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/actions';
import { DataResponse } from 'vendor/fykosak/nette-frontend-component/src/Responses/response';

export interface State {
    lastUpdated?: string;
    refreshDelay?: number;
    isRefreshing?: boolean;
}

const fetchSuccess = (state: State, action: ActionFetchSuccess<DataResponse<ResponseData>>): State => {
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

export const downloader = (state: State = {lastUpdated: null}, action): State => {
    switch (action.type) {
        case ACTION_FETCH_SUCCESS:
            return fetchSuccess(state, action);
        case ACTION_FETCH_FAIL:
            return fetchFail(state);
        default:
            return state;
    }
};

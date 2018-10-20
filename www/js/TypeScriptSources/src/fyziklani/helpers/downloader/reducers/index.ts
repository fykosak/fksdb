import {
    ACTION_SUBMIT_FAIL,
    ACTION_SUBMIT_SUCCESS,
} from '../../../../fetch-api/actions/submit';
import { IActionSubmitSuccess } from '../../../../fetch-api/middleware/interfaces';
import { IResponseData } from '../actions/';

export interface IFyziklaniDownloaderState {
    lastUpdated?: string;
    refreshDelay?: number;
    isRefreshing?: boolean;
}

const updateOptions = (state: IFyziklaniDownloaderState, action: IActionSubmitSuccess<IResponseData>): IFyziklaniDownloaderState => {
    const {lastUpdated, refreshDelay} = action.data.responseData;
    return {
        ...state,
        isRefreshing: true,
        lastUpdated,
        refreshDelay,
    };
};
const fetchFail = (state: IFyziklaniDownloaderState): IFyziklaniDownloaderState => {
    return {
        ...state,
        isRefreshing: false,
    };
};

export const fyziklaniDownloader = (state: IFyziklaniDownloaderState = {lastUpdated: null}, action) => {
    switch (action.type) {
        case ACTION_SUBMIT_SUCCESS:
            return updateOptions(state, action);
        case ACTION_SUBMIT_FAIL:
            return fetchFail(state);
        default:
            return state;
    }
};

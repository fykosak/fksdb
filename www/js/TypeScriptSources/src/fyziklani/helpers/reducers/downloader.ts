import {
    ACTION_SUBMIT_FAIL,
    ACTION_SUBMIT_SUCCESS,
} from '../../../fetch-api/actions/submit';
import { ISubmitSuccessAction } from '../../../fetch-api/middleware/interfaces';
import { IFyziklaniResponse } from '../components/downloader/fetch';

export interface IFyziklaniDownloaderState {
    lastUpdated?: string;
    refreshDelay?: number;
    isRefreshing?: boolean;
}

const updateOptions = (state: IFyziklaniDownloaderState, action: ISubmitSuccessAction<IFyziklaniResponse>): IFyziklaniDownloaderState => {
    const {lastUpdated, refreshDelay} = action.data.data;
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

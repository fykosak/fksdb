import { ResponseData } from 'FKSDB/Components/Game/ResultsAndStatistics/Helpers/Downloader/downloader';
import {
    ACTION_FETCH_SUCCESS,
    ActionFetchSuccess,
} from 'vendor/fykosak/nette-frontend-component/src/fetch/redux/actions';
import { DataResponse } from 'vendor/fykosak/nette-frontend-component/src/Responses/response';
import { Action } from 'redux';

export interface State {
    gameEnd?: Date;
    gameStart?: Date;
    inserted?: Date;
    toEnd?: number;
    toStart?: number;
    visible?: boolean;
}

const fetchSuccess = (state: State, action: ActionFetchSuccess<DataResponse<ResponseData>>): State => {
    const {times, gameEnd, gameStart, times: {toEnd, toStart}} = action.data.data;
    return {
        ...state,
        ...times,
        gameEnd: new Date(gameEnd),
        gameStart: new Date(gameStart),
        inserted: new Date(),
        toEnd: toEnd * 1000,
        toStart: toStart * 1000,
    };
};

export const timer = (state: State = {}, action: Action<string>): State => {
    switch (action.type) {
        case ACTION_FETCH_SUCCESS:
            return fetchSuccess(state, <ActionFetchSuccess<DataResponse<ResponseData>>>action);
        default:
            return state;
    }
};

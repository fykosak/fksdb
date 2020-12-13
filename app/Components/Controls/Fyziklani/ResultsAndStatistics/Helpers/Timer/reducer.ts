import { ResponseData } from '@FKSDB/Components/Controls/Fyziklani/ResultsAndStatistics/Helpers/Downloader/Downloader';
import {
    ACTION_FETCH_SUCCESS,
    ActionFetchSuccess,
} from '@FKSDB/Model/FrontEnd/Fetch/actions';
import { Response2 } from '@FKSDB/Model/FrontEnd/Fetch/interfaces';

export interface State {
    gameEnd?: Date;
    gameStart?: Date;
    inserted?: Date;
    toEnd?: number;
    toStart?: number;
    visible?: boolean;
}

const fetchSuccess = (state: State, action: ActionFetchSuccess<Response2<ResponseData>>): State => {
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

export const fyziklaniTimer = (state: State = {}, action): State => {
    switch (action.type) {
        case ACTION_FETCH_SUCCESS:
            return fetchSuccess(state, action);
        default:
            return state;
    }
};

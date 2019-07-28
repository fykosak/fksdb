import { ACTION_SUBMIT_SUCCESS } from '@fetchApi/actions/submit';
import { ActionSubmitSuccess } from '@fetchApi/middleware/interfaces';
import { ResponseData } from '../downloader/actions/';

export interface State {
    gameEnd?: Date;
    gameStart?: Date;
    inserted?: Date;
    toEnd?: number;
    toStart?: number;
    visible?: boolean;
}

const updateTimes = (state: State, action: ActionSubmitSuccess<ResponseData>): State => {
    const {times, gameEnd, gameStart, times: {toEnd, toStart}} = action.data.responseData;
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
        case ACTION_SUBMIT_SUCCESS:
            return updateTimes(state, action);
        default:
            return state;
    }
};

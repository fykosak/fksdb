import { ACTION_SUBMIT_SUCCESS } from '../../../fetch-api/actions/submit';
import { IActionSubmitSuccess } from '../../../fetch-api/middleware/interfaces';
import { IResponseData } from '../downloader/actions/';

export interface IFyziklaniTimerState {
    gameEnd?: Date;
    gameStart?: Date;
    inserted?: Date;
    toEnd?: number;
    toStart?: number;
    visible?: boolean;
}

const updateTimes = (state: IFyziklaniTimerState, action: IActionSubmitSuccess<IResponseData>): IFyziklaniTimerState => {
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

export const fyziklaniTimer = (state: IFyziklaniTimerState = {}, action): IFyziklaniTimerState => {
    switch (action.type) {
        case ACTION_SUBMIT_SUCCESS:
            return updateTimes(state, action);
        default:
            return state;
    }
};

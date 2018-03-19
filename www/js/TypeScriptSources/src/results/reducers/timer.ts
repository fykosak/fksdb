import {
    NEW_DATA_ARRIVED,
    SET_INITIAL_PARAMS,
} from '../actions/downloader';

export interface IState {
    gameEnd?: Date;
    gameStart?: Date;
    inserted?: Date;
    toEnd?: number;
    toStart?: number;
    visible?: boolean;
}

const updateTimes = (state: IState, action: any): IState => {
    const {times} = action;
    const inserted = new Date();
    return {
        ...state,
        ...times,
        inserted,
    };
};
const setInitialParams = (state: IState, action: any): IState => {
    const {gameStart, gameEnd} = action;
    return {
        gameEnd: new Date(gameEnd),
        gameStart: new Date(gameStart),
        ...state,
    };
};

export const timer = (state: IState = {}, action): IState => {
    switch (action.type) {
        case NEW_DATA_ARRIVED:
            return updateTimes(state, action);
        case SET_INITIAL_PARAMS:
            return setInitialParams(state, action);
        default:
            return state;
    }
};

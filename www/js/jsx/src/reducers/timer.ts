import { UPDATE_TIMES } from '../actions/times';

export interface IState {
    gameEnd?: Date;
    gameStart?: Date;
    inserted?: Date;
    toEnd?: number;
    toStart?: number;
    visible?: boolean;
}

const updateTimes = (state: IState, action: any): IState => {
    const { times } = action;
    const inserted = new Date();
    return {
        ...state,
        ...times,
        inserted,
        ...initState, // fuck this shit!!!
    };
};

const initState: IState = {
    gameEnd: new Date('2017-02-17T14:30:00'),
    gameStart: new Date('2017-02-17T11:30:00'),
};

export const timer = (state: IState = initState, action): IState => {
    switch (action.type) {
        case UPDATE_TIMES:
            return updateTimes(state, action);
        default:
            return state;
    }
};

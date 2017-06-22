import {UPDATE_TIMES} from '../actions/times';

const updateTimes = (state, action: any) => {
    const {times} = action;
    const inserted = new Date();
    return {
        ...state,
        ...times,
        inserted,
        ...initState, // fuck this shit!!!
    };
};

const initState = {
    gameStart: new Date('2017-02-17T11:30:00'),
    gameEnd: new Date('2017-02-17T14:30:00'),
};

export const timer = (state = initState, action) => {
    switch (action.type) {
        case UPDATE_TIMES:
            return updateTimes(state, action);
        default:
            return state;
    }
};

import {UPDATE_TIMES} from '../actions/times';
import {TICK} from '../actions/tick';

const updateTimes = (state, action: any) => {
    const {times} = action;
    const inserted = new Date();
    return {
        ...state,
        ...times,
        inserted,
    }
};

export const timer = (state = {}, action) => {
    switch (action.type) {
        case UPDATE_TIMES:
            return updateTimes(state, action);
        default:
            return state;
    }
};

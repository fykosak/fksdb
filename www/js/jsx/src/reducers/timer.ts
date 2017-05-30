import {UPDATE_TIMES} from '../actions/times';
import {TICK} from '../actions/tick';

const updateTimes = (state, action: any) => {
    const {times} = action;
    return {
        ...state,
        ...times,
    }
};
const tick = (state, action) => {
    let {toEnd, toStart} = state;
    --toEnd;
    --toStart;
    return {
        ...state,
        toStart,
        toEnd,
    };
};

export const timer = (state = {}, action) => {
    switch (action.type) {
        case UPDATE_TIMES:
            return updateTimes(state, action);
        case TICK:
            return tick(state, action);
        default:
            return state;
    }
};

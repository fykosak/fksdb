import {SET_READY_STATUS} from '../actions/options';
const setReadyStatus = (state, action) => {
    return {
        ...state,
        isReady: action.status,
    };
};

export const options = (state = {}, action) => {
    switch (action.type) {
        case SET_READY_STATUS:
            return setReadyStatus(state, action);
        default:
            return state;
    }
};
